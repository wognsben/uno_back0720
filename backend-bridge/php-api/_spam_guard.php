<?php
/*
 * _spam_guard.php
 * Shared spam-prevention guard for legacy Gnuboard write and signup flows.
 * It checks suspicious keywords, repeated links, rapid posting/signup attempts, and new-member posting risk before data is saved.
 * Include this from legacy submit handlers; it does not render UI and stays separate from renewal admin moderation pages.
 */

if (!defined('UNO_SPAM_GUARD_LOADED')) {
    define('UNO_SPAM_GUARD_LOADED', true);
}

function uno_spam_guard_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_spam_guard_table($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_spam_guard_client_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

function uno_spam_guard_normalize($value)
{
    $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    return trim($text);
}

function uno_spam_guard_default_keywords()
{
    return array(
        '카지노', '도박', '바카라', '토토', '먹튀', '슬롯', '바카라사이트',
        '성매매', '성인', '섹스', '조건만남', '출장마사지', '오피',
        '대출', '작업대출', '불법', '스캠', 'telegram', '텔레그램',
        'bit.ly', 'tinyurl', 't.me/', 'http://', 'https://'
    );
}

function uno_spam_guard_contains_keyword($value)
{
    $text = function_exists('mb_strtolower')
        ? mb_strtolower(uno_spam_guard_normalize($value), 'UTF-8')
        : strtolower(uno_spam_guard_normalize($value));

    foreach (uno_spam_guard_default_keywords() as $keyword) {
        $needle = function_exists('mb_strtolower') ? mb_strtolower($keyword, 'UTF-8') : strtolower($keyword);
        if ($needle !== '' && strpos($text, $needle) !== false) {
            return $keyword;
        }
    }

    return '';
}

function uno_spam_guard_url_count($value)
{
    preg_match_all('/https?:\/\/|www\.|t\.me\/|bit\.ly|tinyurl/i', (string) $value, $matches);
    return isset($matches[0]) ? count($matches[0]) : 0;
}

function uno_spam_guard_member_age_seconds($memberId)
{
    $memberId = trim((string) $memberId);
    if ($memberId === '' || !function_exists('sql_fetch')) {
        return null;
    }

    $memberTable = uno_spam_guard_table('member_table', 'g5_member');
    $safeMember = uno_spam_guard_escape($memberId);
    $row = sql_fetch("select mb_datetime from {$memberTable} where mb_id = '{$safeMember}' limit 1");
    if (!$row || empty($row['mb_datetime'])) {
        return null;
    }

    $createdAt = strtotime($row['mb_datetime']);
    return $createdAt ? max(0, time() - $createdAt) : null;
}

function uno_spam_guard_recent_post_count($board, $memberId, $ip, $minutes)
{
    if (!function_exists('sql_fetch')) {
        return 0;
    }

    $allowed = array('cusTour' => true, 'qna' => true, 'write' => true);
    if (!isset($allowed[$board])) {
        return 0;
    }

    $table = 'g5_write_' . $board;
    $since = date('Y-m-d H:i:s', time() - max(1, (int) $minutes) * 60);
    $where = array("wr_is_comment = '0'", "wr_datetime >= '" . uno_spam_guard_escape($since) . "'");

    if (trim((string) $memberId) !== '') {
        $where[] = "mb_id = '" . uno_spam_guard_escape($memberId) . "'";
    } elseif (trim((string) $ip) !== '') {
        $where[] = "wr_ip = '" . uno_spam_guard_escape($ip) . "'";
    } else {
        return 0;
    }

    $row = sql_fetch("select count(*) as cnt from {$table} where " . implode(' and ', $where));
    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

function uno_spam_guard_recent_signup_count($ip, $minutes)
{
    if (!function_exists('sql_fetch') || trim((string) $ip) === '') {
        return 0;
    }

    $memberTable = uno_spam_guard_table('member_table', 'g5_member');
    $since = date('Y-m-d H:i:s', time() - max(1, (int) $minutes) * 60);
    $safeIp = uno_spam_guard_escape($ip);
    $safeSince = uno_spam_guard_escape($since);
    $row = sql_fetch("select count(*) as cnt from {$memberTable} where mb_ip = '{$safeIp}' and mb_datetime >= '{$safeSince}'");
    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

function uno_spam_guard_block($message)
{
    if (defined('UNO_API_BOOTSTRAPPED') && function_exists('uno_api_error')) {
        uno_api_error('SPAM_BLOCKED', $message, 429);
    }

    if (function_exists('alert')) {
        alert($message);
    }

    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
    }
    echo $message;
    exit;
}

function uno_spam_guard_log($type, $reason, $context)
{
    if (!function_exists('sql_query')) {
        return;
    }

    $table = 'uno_spam_block_log';
    sql_query(
        "create table if not exists {$table} (
            id int unsigned not null auto_increment,
            block_type varchar(30) not null default '',
            reason varchar(190) not null default '',
            bo_table varchar(40) not null default '',
            mb_id varchar(80) not null default '',
            ip varchar(45) not null default '',
            subject varchar(255) not null default '',
            created_at datetime not null default '0000-00-00 00:00:00',
            primary key (id),
            key idx_created_at (created_at),
            key idx_mb_id (mb_id),
            key idx_ip (ip)
        ) default charset=utf8"
    );

    $safeType = uno_spam_guard_escape($type);
    $safeReason = uno_spam_guard_escape($reason);
    $safeBoard = uno_spam_guard_escape(isset($context['board']) ? $context['board'] : '');
    $safeMember = uno_spam_guard_escape(isset($context['memberId']) ? $context['memberId'] : '');
    $safeIp = uno_spam_guard_escape(isset($context['ip']) ? $context['ip'] : '');
    $safeSubject = uno_spam_guard_escape(isset($context['subject']) ? substr((string) $context['subject'], 0, 255) : '');
    $safeNow = uno_spam_guard_escape(date('Y-m-d H:i:s'));

    sql_query(
        "insert into {$table}
            set block_type = '{$safeType}',
                reason = '{$safeReason}',
                bo_table = '{$safeBoard}',
                mb_id = '{$safeMember}',
                ip = '{$safeIp}',
                subject = '{$safeSubject}',
                created_at = '{$safeNow}'"
    );
}

function uno_spam_guard_assert_write($board, $subject, $content, $memberId, $isAdmin)
{
    if ($isAdmin) {
        return;
    }

    $allowed = array('cusTour' => true, 'qna' => true, 'write' => true);
    if (!isset($allowed[$board])) {
        return;
    }

    $ip = uno_spam_guard_client_ip();
    $combined = $subject . "\n" . $content;
    $context = array('board' => $board, 'memberId' => $memberId, 'ip' => $ip, 'subject' => $subject);

    $keyword = uno_spam_guard_contains_keyword($combined);
    if ($keyword !== '') {
        uno_spam_guard_log('write', 'keyword:' . $keyword, $context);
        uno_spam_guard_block('스팸 의심 문구가 포함되어 등록할 수 없습니다.');
    }

    if (uno_spam_guard_url_count($combined) >= 3) {
        uno_spam_guard_log('write', 'too_many_urls', $context);
        uno_spam_guard_block('링크가 과도하게 포함되어 등록할 수 없습니다.');
    }

    $age = uno_spam_guard_member_age_seconds($memberId);
    if ($age !== null && $age < 1800 && uno_spam_guard_url_count($combined) > 0) {
        uno_spam_guard_log('write', 'new_member_url', $context);
        uno_spam_guard_block('신규 가입 직후에는 링크가 포함된 문의를 등록할 수 없습니다.');
    }

    if (uno_spam_guard_recent_post_count($board, $memberId, $ip, 5) >= 2) {
        uno_spam_guard_log('write', 'rapid_posting', $context);
        uno_spam_guard_block('짧은 시간에 반복 작성이 감지되어 잠시 후 다시 시도해 주세요.');
    }
}

function uno_spam_guard_assert_signup($memberId, $name, $phone)
{
    $ip = uno_spam_guard_client_ip();
    $combined = $memberId . "\n" . $name . "\n" . $phone;
    $context = array('board' => 'member', 'memberId' => $memberId, 'ip' => $ip, 'subject' => 'signup');

    $keyword = uno_spam_guard_contains_keyword($combined);
    if ($keyword !== '') {
        uno_spam_guard_log('signup', 'keyword:' . $keyword, $context);
        uno_spam_guard_block('가입 정보에 사용할 수 없는 문구가 포함되어 있습니다.');
    }

    if (uno_spam_guard_recent_signup_count($ip, 60) >= 3) {
        uno_spam_guard_log('signup', 'rapid_signup', $context);
        uno_spam_guard_block('같은 접속 환경에서 가입 시도가 반복되어 잠시 후 다시 시도해 주세요.');
    }
}
