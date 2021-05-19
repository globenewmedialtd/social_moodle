<?php
/**
 * @file
 * Contains \Drupal\social_moodle_iteration_enrollment_import\Form\IterationEnrollmentImportSettingsForm.
 */

namespace Drupal\social_moodle_iteration_enrollment_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\social_moodle_iteration_enrollment_import\IterationEnrollmentImportFields;

/**
 * Provides the settings form.
 */
class IterationEnrollmentImportSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'iteration_enrollment_import_settings_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('social_moodle_iteration_enrollment_import.settings');

    

    $iteration_enrollment_import_fields = new IterationEnrollmentImportFields();
    $fields = $iteration_enrollment_import_fields->getAllAvailableFieldsPerEntity();  

    $default_values = $config->get('active_fields');

    

    $default_values_allowed_roles = $config->get('allowed_user_roles');

    $form['label'] = $this->t('Check all fields you do not want to import.');

    foreach ($fields as $key => $field) {

      $options = array_combine($field, $field);

      $form[$key] = array(
        '#type' => 'details',
        '#title' => $key,
        '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
      );

      $form[$key]['check'] = array(
        '#type' => 'checkboxes',
        '#title' => $key,
        '#options' => $options,
        '#default_value' => $default_values
      );

    }


    $allowed_roles = \Drupal::entityQuery('user_role')->execute();
    foreach($allowed_roles as $key => $role) {
      if ($key === 'administrator' || $key === 'authenticated' || $key === 'anonymous') {
        unset($allowed_roles[$key]);
      }
    }



    $form['allowed_roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Allowed Roles'),
      '#options' => $allowed_roles,
      '#default_value' => $default_values_allowed_roles
    );

    $form['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Save'),
    ];

    $form['#tree'] = TRUE;

 

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('social_moodle_iteration_enrollment_import.settings');
    

    $values = $form_state->cleanValues()->getValues();
    $allowed_roles = $form_state->cleanValues()->getValue('allowed_roles'); 

    // Delete all current roles, before adding new ones
    $config->set('allowed_user_roles',[])->save();
    
    foreach ($allowed_roles as $key => $value) {
      if ($key === $value) {
        $save_roles[$key] = $key;
        $config->set('allowed_user_roles',$save_roles)->save();
      }
    }

    foreach ($values as $key => $value) {
      if (is_array($value) & $key != 'allowed_roles') {
        foreach ($value['check'] as $index => $checked) {
          if ($index === $checked) {
            $save_fields[] = $index;
            //\Drupal::state()->set('group_member_import_active_fields',$save_fields);
            $config->set('active_fields', $save_fields)->save();
          }
        }
      }
    }


  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, GroupInterface $group = NULL) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.

    $user = User::load($account->id());

    //kint($group);

    if ($group) {

      $member = $group->getMember($account);

      if ($member) {
        if($member->hasPermission('edit group', $account)) {
          return AccessResult::allowed();
        }
      }
      elseif ($user->hasRole('administrator')) {
        return AccessResult::allowed();
      }

    }
    else {
      if ($user->hasRole('administrator')) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();

  }


}