<?php
/*
 * inquiries/create.php
 * React 1:1 문의 화면의 메시지를 기존 Gnuboard cusTour 게시판에 저장하는 API endpoint입니다.
 * 첫 문의는 원글로 만들고, 이미 문의 스레드가 있으면 댓글로 이어 붙여 기존 my_qna.php 흐름을 유지합니다.
 * 로그인 회원 검증, 텍스트 검증, 스팸 가드, 게시판 신규글/댓글 카운트 반영을 담당합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_spam_guard.php';

uno_api_require_method('POST');
uno_api_require_login();

function uno_api_inquiry_read_json()
{
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        uno_api_error('VALIDATION_ERROR', '요청 형식이 올바르지 않습니다.', 400);
    }

    return $payload;
}

function uno_api_inquiry_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_api_inquiry_text($value, $maxLength)
{
    $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    $text = preg_replace("/\r\n|\r|\n/", "\n", $text);
    $text = preg_replace("/[ \t]+/", " ", $text);
    $text = trim($text);

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLength, 'UTF-8');
    }

    return substr($text, 0, $maxLength);
}

function uno_api_inquiry_text_length($value)
{
    if (function_exists('mb_strlen')) {
        return mb_strlen((string) $value, 'UTF-8');
    }

    return strlen((string) $value);
}

function uno_api_inquiry_table()
{
    global $g5;
    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . 'cusTour';
}

function uno_api_inquiry_insert_id()
{
    if (function_exists('sql_insert_id')) {
        return sql_insert_id();
    }

    if (function_exists('mysql_insert_id')) {
        return mysql_insert_id();
    }

    return 0;
}

function uno_api_inquiry_next_num($table)
{
    if (function_exists('get_next_num')) {
        return get_next_num($table);
    }

    $row = sql_fetch("select min(wr_num) as min_wr_num from {$table}");
    $min = isset($row['min_wr_num']) ? (int) $row['min_wr_num'] : 0;
    return $min - 1;
}

function uno_api_inquiry_board_tables()
{
    global $g5;

    return array(
        'boardNewTable' => isset($g5['board_new_table']) && $g5['board_new_table'] !== ''
            ? $g5['board_new_table']
            : 'g5_board_new',
        'boardTable' => isset($g5['board_table']) && $g5['board_table'] !== ''
            ? $g5['board_table']
            : 'g5_board',
    );
}

function uno_api_inquiry_query($sql, $message)
{
    $result = sql_query($sql, false);

    if (!$result) {
        uno_api_error('SERVER_ERROR', $message, 500);
    }

    return $result;
}

function uno_api_inquiry_is_admin()
{
    if (function_exists('uno_api_is_admin')) {
        return uno_api_is_admin();
    }

    $member = uno_api_member();
    return isset($member['mb_level']) && (int) $member['mb_level'] >= 10;
}

function uno_api_inquiry_find_thread($table, $memberId)
{
    $safeMemberId = uno_api_inquiry_escape($memberId);
    return sql_fetch(
        "select *
           from {$table}
          where wr_is_comment = '0'
            and mb_id = '{$safeMemberId}'
          order by wr_id asc
          limit 1"
    );
}

function uno_api_inquiry_insert_thread($table, $member, $subject, $content, $now, $ip)
{
    $memberId = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
    $memberName = isset($member['mb_name']) && $member['mb_name'] !== ''
        ? (string) $member['mb_name']
        : $memberId;
    $memberEmail = isset($member['mb_email']) ? (string) $member['mb_email'] : '';
    $memberPassword = isset($member['mb_password']) ? (string) $member['mb_password'] : '';

    $wrNum = uno_api_inquiry_next_num($table);
    $safeSubject = uno_api_inquiry_escape($subject);
    $safeContent = uno_api_inquiry_escape($content);
    $safeMemberId = uno_api_inquiry_escape($memberId);
    $safeMemberName = uno_api_inquiry_escape(function_exists('clean_xss_tags') ? clean_xss_tags($memberName) : $memberName);
    $safeEmail = uno_api_inquiry_escape($memberEmail);
    $safePassword = uno_api_inquiry_escape($memberPassword);
    $safeNow = uno_api_inquiry_escape($now);
    $safeIp = uno_api_inquiry_escape($ip);
    $safeWr10 = uno_api_inquiry_escape((string) time());

    uno_api_inquiry_query(
        "insert into {$table}
            set wr_num = '{$wrNum}',
                wr_reply = '',
                wr_is_comment = '0',
                wr_comment = '0',
                ca_name = '',
                wr_option = 'secret',
                wr_subject = '{$safeSubject}',
                wr_content = '{$safeContent}',
                wr_link1 = '',
                wr_link2 = '',
                wr_link1_hit = '0',
                wr_link2_hit = '0',
                wr_hit = '0',
                wr_good = '0',
                wr_nogood = '0',
                mb_id = '{$safeMemberId}',
                wr_password = '{$safePassword}',
                wr_name = '{$safeMemberName}',
                wr_email = '{$safeEmail}',
                wr_homepage = '',
                wr_datetime = '{$safeNow}',
                wr_last = '{$safeNow}',
                wr_ip = '{$safeIp}',
                wr_1 = '',
                wr_2 = '',
                wr_3 = '',
                wr_4 = '',
                wr_5 = '',
                wr_6 = '',
                wr_7 = '',
                wr_8 = '',
                wr_9 = '',
                wr_10 = '{$safeWr10}'",
        '문의 저장에 실패했습니다.'
    );

    $wrId = uno_api_inquiry_insert_id();
    if (!$wrId) {
        uno_api_error('SERVER_ERROR', '문의 저장에 실패했습니다.', 500);
    }

    uno_api_inquiry_query(
        "update {$table} set wr_parent = '{$wrId}' where wr_id = '{$wrId}'",
        '문의 원글 연결에 실패했습니다.'
    );

    $tables = uno_api_inquiry_board_tables();
    uno_api_inquiry_query(
        "insert into {$tables['boardNewTable']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
         values
            ('cusTour', '{$wrId}', '{$wrId}', '{$safeNow}', '{$safeMemberId}')",
        '문의 새 글 기록에 실패했습니다.'
    );
    uno_api_inquiry_query(
        "update {$tables['boardTable']} set bo_count_write = bo_count_write + 1 where bo_table = 'cusTour'",
        '문의 게시판 카운트 갱신에 실패했습니다.'
    );

    return array('threadId' => (int) $wrId, 'messageId' => (int) $wrId, 'isNewThread' => true);
}

function uno_api_inquiry_insert_comment($table, $thread, $member, $content, $now, $ip)
{
    $memberId = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
    $memberName = isset($member['mb_name']) && $member['mb_name'] !== ''
        ? (string) $member['mb_name']
        : $memberId;
    $memberEmail = isset($member['mb_email']) ? (string) $member['mb_email'] : '';
    $memberPassword = isset($member['mb_password']) ? (string) $member['mb_password'] : '';
    $threadId = (int) $thread['wr_id'];

    $commentRow = sql_fetch(
        "select max(wr_comment) as max_comment
           from {$table}
          where wr_parent = '{$threadId}'
            and wr_is_comment = '1'"
    );
    $commentNo = isset($commentRow['max_comment']) ? (int) $commentRow['max_comment'] + 1 : 1;

    $safeContent = uno_api_inquiry_escape($content);
    $safeMemberId = uno_api_inquiry_escape($memberId);
    $safeMemberName = uno_api_inquiry_escape(function_exists('clean_xss_tags') ? clean_xss_tags($memberName) : $memberName);
    $safeEmail = uno_api_inquiry_escape($memberEmail);
    $safePassword = uno_api_inquiry_escape($memberPassword);
    $safeNow = uno_api_inquiry_escape($now);
    $safeIp = uno_api_inquiry_escape($ip);

    uno_api_inquiry_query(
        "insert into {$table}
            set ca_name = '" . uno_api_inquiry_escape(isset($thread['ca_name']) ? $thread['ca_name'] : '') . "',
                wr_option = '',
                wr_num = '" . uno_api_inquiry_escape($thread['wr_num']) . "',
                wr_reply = '',
                wr_parent = '{$threadId}',
                wr_is_comment = '1',
                wr_comment = '{$commentNo}',
                wr_comment_reply = '',
                wr_subject = '',
                wr_content = '{$safeContent}',
                mb_id = '{$safeMemberId}',
                wr_password = '{$safePassword}',
                wr_name = '{$safeMemberName}',
                wr_email = '{$safeEmail}',
                wr_homepage = '',
                wr_datetime = '{$safeNow}',
                wr_last = '',
                wr_ip = '{$safeIp}',
                wr_1 = '',
                wr_2 = '',
                wr_3 = '',
                wr_4 = '',
                wr_5 = '',
                wr_6 = '',
                wr_7 = '',
                wr_8 = '',
                wr_9 = '',
                wr_10 = ''",
        '문의 댓글 저장에 실패했습니다.'
    );

    $commentId = uno_api_inquiry_insert_id();
    if (!$commentId) {
        uno_api_error('SERVER_ERROR', '문의 댓글 저장에 실패했습니다.', 500);
    }

    uno_api_inquiry_query(
        "update {$table} set wr_comment = wr_comment + 1, wr_last = '{$safeNow}' where wr_id = '{$threadId}'",
        '문의 댓글 카운트 갱신에 실패했습니다.'
    );

    $tables = uno_api_inquiry_board_tables();
    uno_api_inquiry_query(
        "insert into {$tables['boardNewTable']}
            (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
         values
            ('cusTour', '{$commentId}', '{$threadId}', '{$safeNow}', '{$safeMemberId}')",
        '문의 댓글 새 글 기록에 실패했습니다.'
    );
    uno_api_inquiry_query(
        "update {$tables['boardTable']} set bo_count_comment = bo_count_comment + 1 where bo_table = 'cusTour'",
        '문의 댓글 게시판 카운트 갱신에 실패했습니다.'
    );

    return array('threadId' => $threadId, 'messageId' => (int) $commentId, 'isNewThread' => false);
}

if (!function_exists('sql_query') || !function_exists('sql_fetch')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$payload = uno_api_inquiry_read_json();
$member = uno_api_member();
$memberId = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
$memberName = isset($member['mb_name']) && $member['mb_name'] !== ''
    ? (string) $member['mb_name']
    : $memberId;

$content = uno_api_inquiry_text(isset($payload['content']) ? $payload['content'] : '', 65536);
$subject = uno_api_inquiry_text(
    isset($payload['subject']) && $payload['subject'] !== ''
        ? $payload['subject']
        : $memberName . '님의 상담 문의입니다.',
    255
);

if (uno_api_inquiry_text_length($content) < 20) {
    uno_api_error('VALIDATION_ERROR', '문의 내용은 한글 기준 20자 이상 입력해 주세요.', 400);
}

if ($subject === '') {
    $subject = $memberName . '님의 상담 문의입니다.';
}

uno_spam_guard_assert_write('cusTour', $subject, $content, $memberId, uno_api_inquiry_is_admin());

$tables = uno_api_inquiry_board_tables();
$board = sql_fetch("select * from {$tables['boardTable']} where bo_table = 'cusTour' limit 1");
if (!$board || empty($board['bo_table'])) {
    uno_api_error('SERVER_ERROR', '1:1 문의 게시판을 찾을 수 없습니다.', 500);
}

$table = uno_api_inquiry_table();
$now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
$ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
$thread = uno_api_inquiry_find_thread($table, $memberId);

$result = $thread && !empty($thread['wr_id'])
    ? uno_api_inquiry_insert_comment($table, $thread, $member, $content, $now, $ip)
    : uno_api_inquiry_insert_thread($table, $member, $subject, $content, $now, $ip);

uno_api_success(array(
    'board' => 'cusTour',
    'threadId' => $result['threadId'],
    'messageId' => $result['messageId'],
    'isNewThread' => $result['isNewThread'],
    'subject' => $subject,
    'createdAt' => $now,
    'nextUrl' => '/mypage/inquiry',
), 201);
