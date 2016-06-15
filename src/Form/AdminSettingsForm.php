<?php

namespace Drupal\livefyre_enterprise;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return 'livefyre_enterprise.settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'livefyre_enterprise_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('livefyre_enterprise.settings');
    $form['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Livefyre Site ID'),
      '#default_value' => $config->get('site_id'),
      '#description' => $this->t('The site ID supplied by Livefyre.'),
      '#required' => TRUE,
    ];

    $form['site_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Livefyre Site Key'),
      '#default_value' => $config->get('site_key'),
      '#description' => $this->t('The Site Key supplied by Livefyre.'),
      '#required' => TRUE,
    ];

    // The Enterprise Hook is used to build the plugin to either include
    // enterprise level functionality. Without this comment, things will not
    // build nicely and sed will most likely break.
    // Enterprise Hook

    $form['enterprise_network'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Livefyre Network'),
      '#default_value' => $config->get('enterprise_network'),
      '#description' => $this->t('Livefyre Network. Do not change unless on a custom network.'),
      '#required' => TRUE,
    ];

    $form['network_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Livefyre Network Key'),
      '#default_value' => $config->get('network_key'),
      '#description' => $this->t('Livefyre Network Key. Do not change unless on a custom network.'),
      '#required' => TRUE,
    ];

    $form['auth_delegate'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Livefyre Authentication Delegate'),
      '#description' => $this->t('Set the Authentication Delegate for Livefyre. This will override \'delegate\' in the App configuration. This function must return an auth delegate variable built according to the following: <a href=\'https://github.com/Livefyre/livefyre-docs/wiki/Comments-3-Integration-Guide#wiki-single-sign-on\'> Livefyre Single Sign On</a>'),
      '#default_value' => $config->get('auth_delegate'),
      '#resizable' => TRUE,
      '#rows' => 5,
    ];

    $form['conv_load_callback'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Livefyre Conversation Load Callback'),
      '#description' => $this->t('Set the function to call back to when the widget loads. This will override \'onload\' in the App configuration.'),
      '#default_value' => $config->get('conv_load_callback'),
      '#resizable' => TRUE,
      '#rows' => 5,
    ];

    $form['custom_css'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Livefyre Custom CSS'),
      '#description' => $this->t('Add custom CSS to your Livefyre Comment Widget. This CSS will appear inline on the page. Please use only Livefyre specific adjustments or rules will be applied to non-Livefyre tags.'),
      '#default_value' => $config->get('custom_css'),
      '#resizable' => TRUE,
      '#rows' => 5,
    ];

    $form['environment'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Livefyre Production Envrionment'),
      '#default_value' => $config->get('environment'),
      '#description' => $this->t('Livefyre Environment. Select this checkbox if you are now using your Livefyre Production Credentials.'),
    ];

    $form['jr_capture'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Janrain Capture'),
      '#default_value' => $config->get('jr_capture'),
      '#description' => $this->t('Livefyre Integration with Janrain Capture. Select this checkbox if you are integrating with Janrain Capture Profiling System.'),
    );

    $form['sync_activity_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of synchronized activity messages'),
      '#default_value' => $config->get('sync_activity_number'),
      '#description' => $this->t('Activity messages are synchronized at every cron run. Please define how many activity messages should be processed at a cron run.'),
      '#required' => TRUE,
      '#size' => 10,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('livefyre.settings');
    $config->set('site_id', $form_state->getValue('site_id'));
    $config->set('site_key', $form_state->getValue('site_key'));
    $config->set('sync_activity_number', $form_state->getValue('sync_activity_number'));
    $config->save();
  }

}
