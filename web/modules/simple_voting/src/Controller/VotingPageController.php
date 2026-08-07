<?php

namespace Drupal\simple_voting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\simple_voting\Entity\VotingQuestion;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VotingPageController extends ControllerBase {

  /**
   * Displays the voting form or results for a question.
   */
  public function viewQuestion(VotingQuestion $voting_question) {
    $user = \Drupal::currentUser();
    if ($user->isAnonymous()) {
      throw new AccessDeniedHttpException('You must be logged in to vote.');
    }

    // Check global voting setting
    $config = \Drupal::config('simple_voting.settings');
    if ($config->get('voting_disabled')) {
      return [
        '#markup' => $this->t('Voting is currently disabled.'),
      ];
    }

    $service = \Drupal::service('simple_voting.voting_service');

    // Check if user already voted
    $existing_votes = \Drupal::entityTypeManager()
      ->getStorage('voting_vote')
      ->loadByProperties([
        'user_id' => $user->id(),
        'question_id' => $voting_question->id(),
      ]);

    if (!empty($existing_votes)) {
      // User already voted – show results if allowed
      if ($voting_question->get('show_results')->value) {
        $results = $service->getResults($voting_question);
        return [
          '#theme' => 'item_list',
          '#title' => $this->t('Results for @question', ['@question' => $voting_question->label()]),
          '#items' => array_map(function($row) {
            return $this->t('@title: @votes votes', ['@title' => $row['title'], '@votes' => $row['votes']]);
          }, $results),
        ];
      }
      else {
        return [
          '#markup' => $this->t('Thank you for voting!'),
        ];
      }
    }

    // Build voting form
    $options = [];
    foreach ($voting_question->get('field_options')->referencedEntities() as $option) {
      $options[$option->id()] = $option->label();
    }

    if (empty($options)) {
      return [
        '#markup' => $this->t('No options available for this question.'),
      ];
    }

    $form = \Drupal::formBuilder()->getForm(
      'Drupal\simple_voting\Form\VoteForm',
      $voting_question,
      $options
    );

    return $form;
  }
}