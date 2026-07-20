<?php
/*
 * inquiries/index.php
 * React 1:1 문의 화면에서 로그인 회원의 기존 cusTour 문의 스레드와 댓글 목록을 조회하는 API endpoint입니다.
 * 기존 my_qna.php의 원글 + 댓글 대화 구조를 JSON으로 변환해 프런트 대화형 UI가 사용할 수 있게 합니다.
 * 문의 작성 API와 분리되어 읽기 전용으로 동작하며, 본인 문의만 반환합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';

uno_api_require_method('GET');
uno_api_require_login();

function uno_api_inquiry_index_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_api_inquiry_index_table()
{
    global $g5;
    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . 'cusTour';
}

function uno_api_inquiry_index_table_name($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_api_inquiry_index_text($value)
{
    return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));
}

function uno_api_inquiry_index_is_admin_message($row)
{
    global $g5;

    if (!isset($row['mb_id']) || $row['mb_id'] === '') {
        return false;
    }

    $memberId = uno_api_inquiry_index_escape($row['mb_id']);
    $memberTable = isset($g5['member_table']) && $g5['member_table'] !== ''
        ? $g5['member_table']
        : 'g5_member';
    $member = sql_fetch("select mb_level from {$memberTable} where mb_id = '{$memberId}' limit 1");
    return $member && isset($member['mb_level']) && (int) $member['mb_level'] > 2;
}

function uno_api_inquiry_index_file_url($wrId, $fileNo)
{
    return '/api/inquiries/file.php?wr_id=' . (int) $wrId . '&no=' . (int) $fileNo;
}

function uno_api_inquiry_index_fetch_files($wrId)
{
    $wrId = (int) $wrId;
    if ($wrId <= 0) {
        return array();
    }

    $boardFileTable = uno_api_inquiry_index_table_name('board_file_table', 'g5_board_file');
    $result = sql_query(
        "select bf_no, bf_source, bf_filesize
           from {$boardFileTable}
          where bo_table = 'cusTour'
            and wr_id = '{$wrId}'
            and bf_file <> ''
          order by bf_no asc"
    );

    $files = array();
    while ($row = sql_fetch_array($result)) {
        $fileNo = isset($row['bf_no']) ? (int) $row['bf_no'] : 0;
        $files[] = array(
            'no' => $fileNo,
            'source' => isset($row['bf_source']) ? (string) $row['bf_source'] : '',
            'size' => isset($row['bf_filesize']) ? (int) $row['bf_filesize'] : 0,
            'url' => uno_api_inquiry_index_file_url($wrId, $fileNo),
        );
    }

    return $files;
}

function uno_api_inquiry_index_message($row, $role)
{
    $wrId = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;

    return array(
        'id' => $wrId,
        'role' => $role,
        'author' => isset($row['wr_name']) ? (string) $row['wr_name'] : '',
        'content' => uno_api_inquiry_index_text(isset($row['wr_content']) ? $row['wr_content'] : ''),
        'createdAt' => isset($row['wr_datetime']) ? (string) $row['wr_datetime'] : '',
        'attachments' => uno_api_inquiry_index_fetch_files($wrId),
    );
}

if (!function_exists('sql_query') || !function_exists('sql_fetch') || !function_exists('sql_fetch_array')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$member = uno_api_member();
$memberId = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
$safeMemberId = uno_api_inquiry_index_escape($memberId);
$table = uno_api_inquiry_index_table();

$thread = sql_fetch(
    "select *
       from {$table}
      where wr_is_comment = '0'
        and mb_id = '{$safeMemberId}'
      order by wr_id asc
      limit 1"
);

if (!$thread || empty($thread['wr_id'])) {
    uno_api_success(array(
        'thread' => null,
        'messages' => array(),
    ));
}

$threadId = (int) $thread['wr_id'];
$messages = array();
$messages[] = uno_api_inquiry_index_message($thread, 'user');

$result = sql_query(
    "select *
       from {$table}
      where wr_is_comment = '1'
        and wr_parent = '{$threadId}'
      order by wr_comment asc, wr_comment_reply asc, wr_id asc"
);

while ($row = sql_fetch_array($result)) {
    $messages[] = uno_api_inquiry_index_message(
        $row,
        uno_api_inquiry_index_is_admin_message($row) ? 'admin' : 'user'
    );
}

uno_api_success(array(
    'thread' => array(
        'id' => $threadId,
        'subject' => isset($thread['wr_subject']) ? (string) $thread['wr_subject'] : '',
        'createdAt' => isset($thread['wr_datetime']) ? (string) $thread['wr_datetime'] : '',
        'updatedAt' => isset($thread['wr_last']) ? (string) $thread['wr_last'] : '',
        'commentCount' => isset($thread['wr_comment']) ? (int) $thread['wr_comment'] : 0,
    ),
    'messages' => $messages,
));
