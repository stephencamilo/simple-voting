<?php

declare(strict_types=1);

namespace Drupal\simple_voting;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a voting option entity type.
 */
interface VotingOptionInterface extends ContentEntityInterface, EntityChangedInterface {

}
