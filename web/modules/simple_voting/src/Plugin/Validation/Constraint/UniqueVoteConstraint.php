<?php

namespace Drupal\simple_voting\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Constraint(
 *   id = "UniqueVote",
 *   label = @Translation("Unique vote per user and question", context = "Validation")
 * )
 */
class UniqueVoteConstraint extends Constraint {
    public $message = 'You have already voted on this question.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string {
        return '\Drupal\simple_voting\Plugin\Validation\Constraint\UniqueVoteValidator';
    }
}
