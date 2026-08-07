<?php

namespace Drupal\simple_voting\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends ControllerBase {

  public function getQuestions() {
    $service = \Drupal::service('simple_voting.voting_service');
    $questions = $service->getQuestions();
    $data = [];
    foreach ($questions as $question) {
      $data[] = [
        'id' => (int) $question->id(),
        'title' => $question->label(),
      ];
    }
    return new JsonResponse($data);
  }

  public function getQuestion($id) {
    $service = \Drupal::service('simple_voting.voting_service');
    $question = $service->getQuestion($id);
    if (!$question) {
      return new JsonResponse(['error' => 'Question not found.'], 404);
    }

    $options = [];
    foreach ($question->get('field_options')->referencedEntities() as $option) {
      $image = $option->get('image')->entity;
      $options[] = [
        'id' => (int) $option->id(),
        'title' => $option->label(),
        'description' => $option->get('description')->value,
        'image_url' => $image ? \Drupal::service('file_url_generator')->generateAbsoluteString($image->getFileUri()) : NULL,
      ];
    }

    return new JsonResponse([
      'id' => (int) $question->id(),
      'title' => $question->label(),
      'description' => $question->get('description')->value,
      'options' => $options,
    ]);
  }

  public function castVote(Request $request, $id) {
    $user = \Drupal::currentUser();
    if ($user->isAnonymous()) {
      return new JsonResponse(['error' => 'Authentication required.'], 401);
    }

    $body = json_decode($request->getContent(), TRUE);
    if (empty($body['option_id'])) {
      return new JsonResponse(['error' => 'option_id is required.'], 400);
    }

    $service = \Drupal::service('simple_voting.voting_service');
    $question = $service->getQuestion($id);
    if (!$question) {
      return new JsonResponse(['error' => 'Question not found.'], 404);
    }

    try {
      $userEntity = \Drupal::entityTypeManager()->getStorage('user')->load($user->id());
      $service->castVote($question, $body['option_id'], $userEntity);
      return new JsonResponse(['message' => 'Vote cast successfully.'], 201);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()], 400);
    }
  }

  public function getResults($id) {
    $service = \Drupal::service('simple_voting.voting_service');
    $question = $service->getQuestion($id);
    if (!$question) {
      return new JsonResponse(['error' => 'Question not found.'], 404);
    }

    if (!$question->get('show_results')->value) {
      return new JsonResponse(['error' => 'Results are not available for this question.'], 403);
    }

    $results = $service->getResults($question);
    return new JsonResponse([
      'question' => $question->label(),
      'total_votes' => array_sum(array_column($results, 'votes')),
      'options' => $results,
    ]);
  }
}