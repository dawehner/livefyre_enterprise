/**
 * @file
 *  Livefyre javascript behaviours.
 */

(function($) {

  Drupal.behaviors.livefyreAuthentication = {
    attach: function (context, settings) {
      // After the fyre.conv has created.
      fyre.conv.ready(function() {
        // Check that the livefyre.Usertoken is not null.
        if (typeof settings.livefyre != 'undefined' && typeof settings.livefyre.userToken != 'undefined') {
          // Authenticate the user with the given token.
          fyre.conv.login(settings.livefyre.userToken);
        }

        /**
         * Custom login handler.
         * Redirect the user to the Drupal's login page, because enterprise
         * users will login automatically to Livefyre system.
         * @param handlers
         */
        authDelegate.login = function (handlers){
          var param = {
            q: 'user/login',
            destination: settings.livefyre.destination
          };
          window.location = settings.basePath + '?' + $.param(param);
        };

        /**
         * Custom logout handler.
         * Log out the user from Liveyre system and also log out from the Drupal
         * system.
         * @param handlers
         */
        authDelegate.logout = function (handlers){
          handlers.success();
          var param = {
            q: 'user/logout',
            destination: settings.livefyre.destination
          };
          window.location = settings.basePath + '?' + $.param(param);
        };

        /**
         * Custom edit profile handler.
         * Redirect the user to the own Drupal user edit form.
         * @param handlers
         * @param author
         */
        authDelegate.editProfile = function(handlers, author) {
          var param = {
            q: 'user/' + settings.livefyre.uid + '/edit',
            destination: settings.livefyre.destination
          };
          window.location = settings.basePath + '?' + $.param(param);
        };
      });
    }
  };

})(jQuery);
