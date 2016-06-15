<?php

namespace Drupal\livefyre;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\livefyre_enterprise\Entity\LifefyreEnterpriseComment;
use GuzzleHttp\Client;

class CommentSync {

  /** @var  \Drupal\Core\State\StateInterface */
  protected $state;

  /** @var  \GuzzleHttp\Client */
  protected $httpClient;

  /** @var  \Drupal\Core\Config\ConfigFactoryInterface */
  protected $configFactory;

  /**
   * Creates a new CommentSync instance.
   *
   * @param \Drupal\Core\State\StateInterface $state
   * @param \GuzzleHttp\Client $httpClient
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(StateInterface $state, Client $httpClient, ConfigFactoryInterface $configFactory) {
    $this->state = $state;
    $this->httpClient = $httpClient;
    $this->configFactory = $configFactory;
  }

  protected function config() {
    return $this->configFactory->get('livefyre_enterprise.settings');
  }

  public function sync() {
    $config = $this->config();
    $lf_network = $config->get('network');
    $lf_site_id = $config->get('site_id');
    $lf_site_key = $config->get('site_key');
    $since_id = $this->state->get('livefyre_enterprise_since_id');
    $query = [
      'page_size' => $config->get('sync_activity_number'),
      'sig_created' => REQUEST_TIME,
    ];
    $sig = getHmacsha1Signature(base64_decode($lf_site_key), UrlHelper::buildQuery($query));
    $query['sig'] = $sig;

    $response = $this->httpClient->get("http://bootstrap.{$lf_network}/api/v1.1/private/feed/site/{$lf_site_id}/sync/{$since_id}?" . UrlHelper::buildQuery($query), [
      'http_errors' => FALSE,
    ]);

    if ($response->getStatusCode() === 200) {
      $data = json_decode((string) $response->getBody());

      // Store the message last status (state) and the latest body text to store.
      $messages = [];
      foreach ($data as $message) {
        // If we limit the number of the results the last object will not have id.
        if (!isset($message->lf_comment_id)) {
          continue;
        }

        if (!isset($messages[$message->lf_comment_id])) {
          $messages[$message->lf_comment_id] = [
            'original' => $message,
            'status' => $message->state,
            'body' => $message->body_text,
          ];
        }
        else {
          $messages[$message->lf_comment_id]['status'] = $message->state;
          $messages[$message->lf_comment_id]['body'] = $message->body_text;
        }
        // Store since ID for the next sync event.
        $this->state->set('livefyre_enterprise_since_id', $message->activity_id);
      }

      foreach ($messages as $lf_comment_id => $message) {
        $original_message = $message['original'];
        // Update the existing entity.
        if ($comment_id = livefyre_enterprise_get_lfcid_from_lf_comment_id($lf_comment_id)) {
          $comment = LifefyreEnterpriseComment::load($comment_id);
        }
        else {
          $comment = LifefyreEnterpriseComment::create([]);
        }
        $comment->lf_comment_id->value = $lf_comment_id;
        // Delete the entity if it removed from livefyre system.
        if ($message['status'] == 'deleted' && !$comment->isNew()) {
          $comment->delete();
          continue;
        }
        // Do not do anything if the comment doesn't exist in Drupal and
        // marked as deleted in livefyre system.
        if ($message['status'] == 'deleted' && $comment->isNew()) {
          continue;
        }

        // Store parent entity ID.
        if (isset($original_message->lf_parent_comment_id) && $pid = livefyre_enterprise_get_lfcid_from_lf_comment_id($original_message->lf_parent_comment_id)) {
          $comment->pid->target_id = $pid;
        }

        // These properties are not allowed to update so store only if this is a
        // new comment.
        if ($comment->isNew()) {
          // Check if the user is an existing Drupal user.
          $users = user_load_multiple([], ['mail' => $original_message->author_email]);
          if ($users) {
            $account = reset($users);
            $comment->uid->target_id = $account->uid;
          }
          else {
            $comment->uid->target_id = 0;
          }
          $comment->name->value = $original_message->display_name;
          $comment->name->value = $original_message->author_email;

          // Other property informations.
          $comment->created->value = $original_message->created;
          $comment->hostname->value = $original_message->ip_address;
          if (isset($original_message->author_url)) {
            $comment->homepage->value = $original_message->author_url;
          }
          // @fixme
          $comment->path->value = $original_message->article_identifier;
        }

        $comment->status->value = $message['status'] == 'active' ? 1 : 0;
        $comment->livefyre->value = $message['status'];
        $comment->body->value = $message['body'];
        // Store the entity.
        $comment->save();
      }
    }
  }

}
