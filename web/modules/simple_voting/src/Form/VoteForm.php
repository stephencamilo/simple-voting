<?php

namespace Drupal\simple_voting\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simple_voting\Entity\VotingQuestion;

class VoteForm extends FormBase {

  public function getFormId() {
    return 'simple_voting_vote_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, VotingQuestion $voting_question = NULL, array $options = []) {
    if (!$voting_question) {
      return ['#markup' => $this->t('Question not found.')];
    }

    $form_state->set('voting_question', $voting_question);

    $form['option'] = [
      '#type' => 'radios',
      '#title' => $voting_question->label(),
      '#description' => $voting_question->get('description')->value,
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Vote'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $voting_question = $form_state->get('voting_question');
    $option_id = $form_state->getValue('option');
    $user = \Drupal::currentUser();

    $service = \Drupal::service('simple_voting.voting_service');
    try {
      $userEntity = \Drupal::entityTypeManager()->getStorage('user')->load($user->id());
      $service->castVote($voting_question, $option_id, $userEntity);
      $this->messenger()->addMessage($this->t('Your vote has been recorded.'));
    } catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
    }

    // Redirect back to the question page to show results/thanks
    $form_state->setRedirect('entity.voting_question.canonical', ['voting_question' => $voting_question->id()]);
  }

}