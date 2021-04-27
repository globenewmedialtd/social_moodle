/**
 * @file social_moodle_enrollment_request_modal.js
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.iterationModalRequest = {
    attach: function (context, settings) {

      // Trigger the modal window.
      $('body', context).once('iterationModalRequest').each(function () {
        $('a#modal-trigger').click();

        // When the dialog closes, reload without the location.search parameter.
        $('body').on('dialogclose', '.ui-dialog', function() {
          location.assign(location.origin + location.pathname);
        });
      });

      // When submitting the request, close the page.
      var closeDialog = settings.iterationModalRequest.closeDialog;

      $('body').once('iterationSubmitRequest').on('dialogclose', '.ui-dialog', function() {
        if (closeDialog === true) {
          location.assign(location.origin + location.pathname);
        }
      });
    }
  }

})(jQuery, Drupal, drupalSettings);
