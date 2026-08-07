<?php

namespace Drupal\simple_voting\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 *
 */
class UniqueVoteValidator extends ConstraintValidator {

  /**
   *
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

    $user_id = $entity->get('user_id')->target_id;
    $question_id = $entity->get('question_id')->target_id;

    if (empty($user_id) || empty($question_id)) {
      return;
    }

    $storage = \Drupal::entityTypeManager()->getStorage('voting_vote');
    $existing = $storage->loadByProperties([
      'user_id' => $user_id,
      'question_id' => $question_id,
    ]);

    // Exclude the current entity if it's an update (not needed for new votes).
    if (!empty($existing) && !($entity->id() && count($existing) === 1 && isset($existing[$entity->id()]))) {
      $this->context->addViolation($constraint->message);
    }
  }

}
