<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class VotingSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['simple_voting.settings'];
  }

  public function getFormId() {
    return 'voting_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('simple_voting.settings');
    $form['voting_disabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable voting'),
      '#default_value' => $config->get('voting_disabled'),
      '#description' => $this->t('When checked, voting is blocked everywhere.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('simple_voting.settings')
      ->set('voting_disabled', $form_state->getValue('voting_disabled'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
