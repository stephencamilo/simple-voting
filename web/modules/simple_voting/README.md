# Simple Voting Module

Custom Drupal 11 module that provides a voting system with:

- Custom entities: `voting_question`, `voting_option`, `voting_vote` (no nodes).
- Admin interface with inline options (Inline Entity Form).
- Global disable voting switch.
- Front‑end voting page for authenticated users.
- Manual REST API for external applications.

## Requirements

- Lando with Drupal 11 recipe (PHP 8.3, MariaDB 10.11).
- Drush 12+
- Composer dependencies: `drupal/inline_entity_form`, `drush/drush`.

## Installation

1. Start Lando:
   ```bash
   lando start