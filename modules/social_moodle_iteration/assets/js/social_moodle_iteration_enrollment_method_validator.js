/**
 * @file
 */

(function ($, Drupal, drupalSettings) {

    /**
     * Behaviors.
     */
    Drupal.behaviors.socialMoodleIterationEnrollmentMethodValidator = {
        attach: function (context, settings) {

          console.log(settings.socialMoodleIterationEnrollmentMethodValidator.exclusive_elements);

          $('#edit-field-iteration-enrollment input').click(function(e) {
            var name = $(this).attr("name");
            

            if (name = 'field_iteration_enrollment[open_to_enroll]') {
              //console.log('test');
              //$('#edit-field-iteration-enrollment-request-to-enroll').attr('disabled',true);
    
            }
            


          });

          

        }
  };

})(jQuery, Drupal, drupalSettings);
