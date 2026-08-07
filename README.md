# Simple Voting Project

Demonstration of Drupal 11 custom entity nesting – a fully functional voting system built without nodes.  
It showcases three custom content entities (`voting_question`, `voting_option`, `voting_vote`) with an admin interface that uses **Inline Entity Form** to manage related options directly inside the question form.

The voting logic, REST API, and concurrency handling serve as a practical example of how to structure a module with clean separation of concerns.

## Features

- Custom entities – no nodes used.
- Admin interface with inline options (title, description, image).
- Global switch to disable voting everywhere.
- Front‑end voting page for authenticated users.
- Manual REST API (no JSON:API) with Basic Auth.
- Unique vote per user per question with lock protection.

## Requirements

- Lando with Drupal 11 recipe (PHP 8.3, MariaDB 10.11)
- Drush 12+
- Composer dependencies: `drupal/inline_entity_form`, `drush/drush`
- Drupal core modules: `image`, `basic_auth`

## Installation after cloning the repository

These steps assume you have cloned the repository and are in the project directory.

### 1. Start the Lando environment
```bash
lando start
```

### 2. Install Composer dependencies
(All required packages, including `inline_entity_form`, are already listed in `composer.json`.)
```bash
lando composer install
```

### 3. Install Drupal with the Standard profile
> **Note:** `admin:admin` is for testing purposes only.
```bash
lando drush site-install standard \
  --db-url=mysql://drupal11:drupal11@database/drupal11 \
  --account-name=admin \
  --account-pass=admin \
  -y
```

### 4. Enable required modules
```bash
lando drush en image basic_auth inline_entity_form simple_voting -y
```

### 5. Grant voting permission to all authenticated users
```bash
lando drush role-add-perm authenticated "vote on questions"
```

### 6. Clear cache
```bash
lando drush cr
```

### 7. Verify the API
```bash
curl -k -u admin:admin https://simple-voting.lndo.site/api/voting/questions
```
You should see an empty JSON array `[]`.

> **Note:** The `-k` flag is needed because Lando uses self‑signed SSL certificates.  
> If you prefer HTTP, use `http://simple-voting.lndo.site:8000` (if the port is functional).

### Alternative: Use the provided database dump
If you want to start with pre‑loaded questions and votes, import the dump instead of performing the fresh install (step 3):
```bash
lando drush sql-drop -y
lando ssh -s database -c "mysql -u drupal11 -pdrupal11 --ssl=0 drupal11" < drupal_dump.sql
lando drush cr
```

## Module dependencies

The module's `simple_voting.info.yml` already includes:
```yaml
dependencies:
  - drupal:user
  - drupal:rest
  - drupal:image
  - inline_entity_form
```

## Login

The default administrator account created during installation:

- **URL**: `https://simple-voting.lndo.site/user/login`
- **Username**: `admin`
- **Password**: `admin`

> ⚠️ These credentials are for testing purposes only. Change them immediately if the site is exposed publicly.

## Configuration

- **Global voting toggle**: `/admin/config/content/simple_voting` – disable/enable all voting.
- **Manage questions**: `/admin/content/voting-question` – add, edit, or delete questions.
- **Permissions**: Grant the `vote on questions` permission to any role that should be able to vote via CMS or API.

## UI Usage

1. Log in at `https://simple-voting.lndo.site/user/login` (admin / admin).
2. Go to **Administration** → **Content** (`https://simple-voting.lndo.site/admin/content`).
3. Click the **Add voting question** button at the top.
4. Fill in the question **Label**, optionally a **Description**, and keep **Show results after voting** checked if you want results visible after voting.
5. In the **Options** section, click **Add new option** for each answer:
   - Enter the option **Label** (e.g., “Red”).
   - Optionally add a **Description** and an **Image**.
   - Click **Create voting option**.
6. Add more options as needed.
7. Click **Save** to create the question.

The voting page (e.g., `https://simple-voting.lndo.site/voting/1`) is now available for authenticated users.

## API Endpoints

All endpoints require **Basic Auth** and the `vote on questions` permission.

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/voting/questions` | List all published questions |
| GET | `/api/voting/question/{id}` | Get question details and options |
| POST | `/api/voting/question/{id}/vote` | Cast a vote (body: `{"option_id": 1}`) |
| GET | `/api/voting/question/{id}/results` | View results (if `show_results` is enabled) |

Example with curl:

```bash
# Get questions
curl -k -u admin:admin https://simple-voting.lndo.site/api/voting/questions

# Cast a vote
curl -k -u admin:admin -X POST https://simple-voting.lndo.site/api/voting/question/1/vote \
  -H "Content-Type: application/json" \
  -d '{"option_id": 1}'
```

## Postman Collection

Import `Simple_Voting_API.postman_collection.json` into Postman. Set the `base_url` variable (default: `https://simple-voting.lndo.site`) and configure Basic Auth with a valid user. Disable SSL certificate verification in Postman settings.

## Database Dump

A database dump is provided in `drupal_dump.sql` (exported using `lando drush sql-dump`). To import it:

```bash
lando drush sql-drop -y
lando ssh -s database -c "mysql -u drupal11 -pdrupal11 --ssl=0 drupal11" < drupal_dump.sql
lando drush cr
```

## Environment

- Built with Lando – see `.lando.yml`.
- Drupal 11, PHP 8.3, MariaDB 10.11.

---

Thanks for reading this far! If you have any questions or suggestions, feel free to open an issue or reach out.