<?php

namespace Drupal\simple_voting;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\simple_voting\Entity\VotingQuestion;
use Drupal\simple_voting\Entity\VotingVote;
use Drupal\user\UserInterface;

/**
 *
 */
class VotingService {
  protected $entityTypeManager;
  protected $lock;
  protected $logger;
  protected $config;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    LockBackendInterface $lock,
    LoggerChannelFactoryInterface $loggerFactory,
    ConfigFactoryInterface $configFactory,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->lock = $lock;
    $this->logger = $loggerFactory->get('simple_voting');
    $this->config = $configFactory->get('simple_voting.settings');
  }

  /**
   * Returns all published questions.
   */
  public function getQuestions() {
    return $this->entityTypeManager->getStorage('voting_question')
      ->loadByProperties(['status' => 1]);
  }

  /**
   * Load a single question.
   */
  public function getQuestion($id) {
    return VotingQuestion::load($id);
  }

  /**
   *
   */
  public function castVote(VotingQuestion $question, $option_id, UserInterface $user) {
    // Global disable check.
    if ($this->config->get('voting_disabled')) {
      throw new \Exception('Voting is temporarily disabled.');
    }

    // Validate the option.
    $option = $this->entityTypeManager->getStorage('voting_option')->load($option_id);
    if (!$option) {
      throw new \Exception('Option not found.');
    }

    $valid_option_ids = array_map(
          fn($opt) => $opt->id(),
          $question->get('field_options')->referencedEntities()
      );
    if (!in_array($option->id(), $valid_option_ids)) {
      throw new \Exception('Invalid option.');
    }

    // Acquire lock to prevent race conditions.
    $lock_name = 'simple_voting_vote_' . $question->id() . '_' . $user->id();
    if (!$this->lock->acquire($lock_name, 10)) {
      throw new \Exception('Could not acquire lock; please try again.');
    }

    try {
      // ** Explicit duplicate check **
      $existing = $this->entityTypeManager->getStorage('voting_vote')->loadByProperties([
        'user_id'     => $user->id(),
        'question_id' => $question->id(),
      ]);
      if (!empty($existing)) {
        throw new \Exception('You have already voted on this question.');
      }

      // Create and save the vote.
      $vote = VotingVote::create([
        'user_id'     => $user->id(),
        'question_id' => $question->id(),
        'option_id'   => $option->id(),
      ]);
      $vote->save();

      $this->logger->info('Vote cast: user %uid, question %qid, option %oid', [
        '%uid' => $user->id(),
        '%qid' => $question->id(),
        '%oid' => $option->id(),
      ]);
    }
    finally {
      $this->lock->release($lock_name);
    }
  }

  /**
   * Get voting results for a question.
   */
  public function getResults(VotingQuestion $question) {
    $query = \Drupal::database()->select('voting_vote', 'v')
      ->fields('v', ['option_id'])
      ->condition('v.question_id', $question->id())
      ->groupBy('v.option_id');
    $query->addExpression('COUNT(v.option_id)', 'votes');
    $results = $query->execute()->fetchAllAssoc('option_id');

    $options = $question->get('field_options')->referencedEntities();
    $data = [];
    foreach ($options as $option) {
      $data[] = [
        'option_id' => (int) $option->id(),
        'title'     => $option->label(),
        'votes'     => isset($results[$option->id()]) ? (int) $results[$option->id()]->votes : 0,
      ];
    }
    return $data;
  }

}
