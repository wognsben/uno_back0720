# Spam Prevention Implementation

This document explains the lightweight spam-prevention guard added for the Cafe24/Gnuboard renewal bridge.

## Purpose

The existing spam moderation page handles posts after they are saved. The new guard blocks suspicious signup and community-write attempts before they reach the database.

## New Guard File

Upload this file:

```text
deploy/cafe24-www/api/_spam_guard.php
→ /www/api/_spam_guard.php
```

The guard provides:

- keyword blocking for gambling, adult spam, scam, loan, Telegram/link spam
- excessive URL blocking
- rapid repeated posting limit
- new-member link posting limit
- repeated signup limit by IP
- blocked-attempt logging into `uno_spam_block_log`

## Legacy Write Handler Hook

Target legacy file:

```text
/www/bbs/bbs/write_update.php
```

Add this block after `$wr_subject` and `$wr_content` are validated, before the real insert/update runs. A safe position is after the existing fast-posting delay check and before the final insert branch.

```php
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/api/_spam_guard.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/_spam_guard.php';
    uno_spam_guard_assert_write(
        isset($bo_table) ? $bo_table : '',
        isset($wr_subject) ? $wr_subject : '',
        isset($wr_content) ? $wr_content : '',
        isset($member['mb_id']) ? $member['mb_id'] : '',
        !empty($is_admin)
    );
}
```

Recommended boards to protect first:

- `cusTour`
- `qna`
- `write`

The guard already ignores other boards.

## Legacy Signup Handler Hook

Target legacy file:

```text
/www/bbs/bbs/register_form_update.php
```

Add this block after `$mb_id`, `$mb_name`, and `$mb_hp` are normalized and before the member insert query.

```php
if ($w === '' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/api/_spam_guard.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/_spam_guard.php';
    uno_spam_guard_assert_signup(
        isset($mb_id) ? $mb_id : '',
        isset($mb_name) ? $mb_name : '',
        isset($mb_hp) ? $mb_hp : ''
    );
}
```

## Current Policy

The first-pass policy is intentionally conservative:

- admin users are not blocked by write guard
- non-community boards are ignored
- members created less than 30 minutes ago cannot submit links in protected boards
- 3 or more links in one post are blocked
- 2 or more recent posts in 5 minutes blocks the next attempt
- 3 or more signups from one IP in 60 minutes blocks the next signup attempt

## Next Phase

After this is stable, add a renewal admin settings page for:

- keyword list editing
- blocked URL/domain patterns
- per-board rate limits
- Cloudflare Turnstile keys and enable flags
- spam block log review
