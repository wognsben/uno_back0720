<?php
/*
 * admin/spam-moderation.php
 * Renewal admin API for lightweight community spam moderation.
 * It lists suspicious inquiry-board posts, intercepts member accounts, and deletes verified spam posts through a Gnuboard-compatible cleanup path.
 * Bulk deletion is limited and skips reply-thread posts so board counters, files, latest posts, and scraps stay consistent.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_login();
uno_api_require_admin();

if (!function_exists('sql_fetch') || !function_exists('sql_query') || !function_exists('sql_fetch_array')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
}

function uno_api_spam_escape($value)
{
    return uno_api_reservation_escape((string) $value);
}

function uno_api_spam_json_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);
    return is_array($body) ? $body : array();
}

function uno_api_spam_excerpt($value)
{
    $content = trim(strip_tags((string) $value));
    if (function_exists('mb_substr')) {
        return mb_substr($content, 0, 160, 'UTF-8');
    }

    return substr($content, 0, 160);
}

function uno_api_spam_board_table($board)
{
    $allowed = array('cusTour' => true, 'qna' => true, 'write' => true);
    $board = isset($allowed[$board]) ? $board : 'cusTour';
    return array($board, 'g5_write_' . $board);
}

function uno_api_spam_table_name($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_api_spam_board_row($board)
{
    list($safeBoard) = uno_api_spam_board_table($board);
    $boardTable = uno_api_spam_table_name('board_table', 'g5_board');
    $safeBoardSql = uno_api_spam_escape($safeBoard);
    $row = sql_fetch("select * from {$boardTable} where bo_table = '{$safeBoardSql}' limit 1");

    if (!$row || empty($row['bo_table'])) {
        uno_api_error('NOT_FOUND', '게시판 정보를 찾을 수 없습니다.', 404);
    }

    return $row;
}

function uno_api_spam_keyword_where($keyword)
{
    $keyword = trim((string) $keyword);
    if ($keyword !== '') {
        $safe = uno_api_spam_escape($keyword);
        return "(w.wr_subject like '%{$safe}%' or w.wr_content like '%{$safe}%' or w.wr_name like '%{$safe}%' or w.mb_id like '%{$safe}%')";
    }

    $patterns = array('카지노', '도박', '바카라', '성인', '성매매', '섹스', '토토', '먹튀', '대출', 'http://', 'https://', 'telegram', '텔레그램');
    $parts = array();
    foreach ($patterns as $pattern) {
        $safe = uno_api_spam_escape($pattern);
        $parts[] = "w.wr_subject like '%{$safe}%'";
        $parts[] = "w.wr_content like '%{$safe}%'";
    }

    return '(' . implode(' or ', $parts) . ')';
}

function uno_api_spam_fetch_posts($board, $keyword, $memberId)
{
    list($safeBoard, $table) = uno_api_spam_board_table($board);
    $where = array("w.wr_is_comment = '0'");

    if (trim((string) $memberId) !== '') {
        $safeMember = uno_api_spam_escape($memberId);
        $where[] = "w.mb_id = '{$safeMember}'";
    } else {
        $where[] = uno_api_spam_keyword_where($keyword);
    }

    $whereSql = implode(' and ', $where);
    $result = sql_query(
        "select w.wr_id, w.mb_id, w.wr_name, w.wr_subject, w.wr_content, w.wr_datetime, w.wr_ip,
                m.mb_name, m.mb_email, m.mb_hp, m.mb_level, m.mb_intercept_date, m.mb_leave_date
           from {$table} w
           left join g5_member m on w.mb_id = m.mb_id
          where {$whereSql}
          order by w.wr_id desc
          limit 80"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'board' => $safeBoard,
            'id' => isset($row['wr_id']) ? (int) $row['wr_id'] : 0,
            'memberId' => isset($row['mb_id']) ? (string) $row['mb_id'] : '',
            'writer' => isset($row['wr_name']) ? (string) $row['wr_name'] : '',
            'subject' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '',
            'excerpt' => uno_api_spam_excerpt(isset($row['wr_content']) ? $row['wr_content'] : ''),
            'createdAt' => isset($row['wr_datetime']) ? (string) $row['wr_datetime'] : '',
            'ip' => isset($row['wr_ip']) ? (string) $row['wr_ip'] : '',
            'member' => array(
                'name' => isset($row['mb_name']) ? (string) $row['mb_name'] : '',
                'email' => isset($row['mb_email']) ? (string) $row['mb_email'] : '',
                'phone' => isset($row['mb_hp']) ? (string) $row['mb_hp'] : '',
                'level' => isset($row['mb_level']) ? (int) $row['mb_level'] : 0,
                'interceptDate' => isset($row['mb_intercept_date']) ? (string) $row['mb_intercept_date'] : '',
                'leaveDate' => isset($row['mb_leave_date']) ? (string) $row['mb_leave_date'] : '',
            ),
            'links' => array(
                'post' => '/admin/board.php?bo_table=' . rawurlencode($safeBoard) . '&wr_id=' . (isset($row['wr_id']) ? (int) $row['wr_id'] : 0),
                'member' => isset($row['mb_id']) && $row['mb_id'] !== '' ? '/admin/member_form.php?w=u&mb_id=' . rawurlencode((string) $row['mb_id']) : '',
            ),
        );
    }

    return $items;
}

function uno_api_spam_fetch_writers($board)
{
    list($safeBoard, $table) = uno_api_spam_board_table($board);
    $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
    $safeSince = uno_api_spam_escape($since);
    $result = sql_query(
        "select w.mb_id, w.wr_name, count(*) as cnt, max(w.wr_datetime) as latest, m.mb_intercept_date
           from {$table} w
           left join g5_member m on w.mb_id = m.mb_id
          where w.wr_is_comment = '0'
            and w.wr_datetime >= '{$safeSince}'
          group by w.mb_id, w.wr_name, m.mb_intercept_date
          having cnt >= 3
          order by cnt desc, latest desc
          limit 30"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'board' => $safeBoard,
            'memberId' => isset($row['mb_id']) ? (string) $row['mb_id'] : '',
            'writer' => isset($row['wr_name']) ? (string) $row['wr_name'] : '',
            'count' => isset($row['cnt']) ? (int) $row['cnt'] : 0,
            'latest' => isset($row['latest']) ? (string) $row['latest'] : '',
            'interceptDate' => isset($row['mb_intercept_date']) ? (string) $row['mb_intercept_date'] : '',
        );
    }

    return $items;
}

function uno_api_spam_block_member($memberId)
{
    $memberId = trim((string) $memberId);
    if ($memberId === '') {
        uno_api_error('VALIDATION_ERROR', '차단할 회원 ID를 확인해 주세요.', 400);
    }

    $safeMember = uno_api_spam_escape($memberId);
    $row = sql_fetch("select mb_id, mb_level from g5_member where mb_id = '{$safeMember}' limit 1");
    if (!$row || empty($row['mb_id'])) {
        uno_api_error('NOT_FOUND', '회원을 찾을 수 없습니다.', 404);
    }

    if (isset($row['mb_level']) && (int) $row['mb_level'] >= 10) {
        uno_api_error('VALIDATION_ERROR', '관리자 계정은 여기서 차단할 수 없습니다.', 400);
    }

    $today = date('Ymd');
    sql_query("update g5_member set mb_intercept_date = '{$today}' where mb_id = '{$safeMember}'");
}

function uno_api_spam_target_posts($board, $memberId, $postIds)
{
    list($safeBoard, $table) = uno_api_spam_board_table($board);
    $where = array("wr_is_comment = '0'");

    $ids = array();
    if (is_array($postIds)) {
        foreach ($postIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
    }

    if ($ids) {
        $where[] = 'wr_id in (' . implode(',', array_slice(array_unique($ids), 0, 100)) . ')';
    } else {
        $memberId = trim((string) $memberId);
        if ($memberId === '') {
            uno_api_error('VALIDATION_ERROR', '삭제할 작성자 또는 게시글을 선택해 주세요.', 400);
        }
        $safeMember = uno_api_spam_escape($memberId);
        $where[] = "mb_id = '{$safeMember}'";
    }

    $result = sql_query(
        "select *
           from {$table}
          where " . implode(' and ', $where) . "
          order by wr_id desc
          limit 100"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $row['_safe_board'] = $safeBoard;
        $items[] = $row;
    }

    return $items;
}

function uno_api_spam_delete_one_post($board, $write, $boardRow)
{
    if (!$write || empty($write['wr_id'])) {
        return array('deleted' => false, 'reason' => '게시글을 찾을 수 없습니다.');
    }

    list($safeBoard, $table) = uno_api_spam_board_table($board);
    $wrId = (int) $write['wr_id'];
    $reply = isset($write['wr_reply']) ? substr((string) $write['wr_reply'], 0, strlen((string) $write['wr_reply'])) : '';
    $wrNum = isset($write['wr_num']) ? uno_api_spam_escape($write['wr_num']) : '';
    $safeReply = uno_api_spam_escape($reply);

    $replyCount = sql_fetch(
        "select count(*) as cnt
           from {$table}
          where wr_reply like '{$safeReply}%'
            and wr_id <> '{$wrId}'
            and wr_num = '{$wrNum}'
            and wr_is_comment = '0'"
    );

    if ($replyCount && (int) $replyCount['cnt'] > 0) {
        return array('deleted' => false, 'reason' => '답글이 연결되어 있어 건너뜀');
    }

    $fileTable = uno_api_spam_table_name('board_file_table', 'g5_board_file');
    $newTable = uno_api_spam_table_name('board_new_table', 'g5_board_new');
    $scrapTable = uno_api_spam_table_name('scrap_table', 'g5_scrap');
    $boardTable = uno_api_spam_table_name('board_table', 'g5_board');
    $countWrite = 0;
    $countComment = 0;

    $children = sql_query("select wr_id, mb_id, wr_is_comment, wr_content from {$table} where wr_parent = '{$wrId}' order by wr_id");
    while ($child = sql_fetch_array($children)) {
        $childId = isset($child['wr_id']) ? (int) $child['wr_id'] : 0;
        if (!$childId) {
            continue;
        }

        if (empty($child['wr_is_comment'])) {
            if (function_exists('delete_point') && !delete_point(isset($child['mb_id']) ? $child['mb_id'] : '', $safeBoard, $childId, '쓰기') && function_exists('insert_point')) {
                $point = isset($boardRow['bo_write_point']) ? (int) $boardRow['bo_write_point'] * -1 : 0;
                insert_point(isset($child['mb_id']) ? $child['mb_id'] : '', $point, isset($boardRow['bo_subject']) ? $boardRow['bo_subject'] . ' ' . $childId . ' 글삭제' : '글삭제');
            }

            $files = sql_query("select * from {$fileTable} where bo_table = '{$safeBoard}' and wr_id = '{$childId}'");
            while ($file = sql_fetch_array($files)) {
                if (!empty($file['bf_file']) && defined('G5_DATA_PATH')) {
                    $fileName = str_replace('../', '', $file['bf_file']);
                    @unlink(G5_DATA_PATH . '/file/' . $safeBoard . '/' . $fileName);
                    if (function_exists('delete_board_thumbnail')) {
                        delete_board_thumbnail($safeBoard, $fileName);
                    }
                }
            }

            if (function_exists('delete_editor_thumbnail')) {
                delete_editor_thumbnail(isset($child['wr_content']) ? $child['wr_content'] : '');
            }

            sql_query("delete from {$fileTable} where bo_table = '{$safeBoard}' and wr_id = '{$childId}'");
            $countWrite++;
        } else {
            if (function_exists('delete_point') && !delete_point(isset($child['mb_id']) ? $child['mb_id'] : '', $safeBoard, $childId, '댓글') && function_exists('insert_point')) {
                $point = isset($boardRow['bo_comment_point']) ? (int) $boardRow['bo_comment_point'] * -1 : 0;
                insert_point(isset($child['mb_id']) ? $child['mb_id'] : '', $point, isset($boardRow['bo_subject']) ? $boardRow['bo_subject'] . ' ' . $wrId . '-' . $childId . ' 댓글삭제' : '댓글삭제');
            }
            $countComment++;
        }
    }

    sql_query("delete from {$table} where wr_parent = '{$wrId}'");
    sql_query("delete from {$newTable} where bo_table = '{$safeBoard}' and wr_parent = '{$wrId}'");
    sql_query("delete from {$scrapTable} where bo_table = '{$safeBoard}' and wr_id = '{$wrId}'");

    if (function_exists('board_notice')) {
        $boNotice = board_notice(isset($boardRow['bo_notice']) ? $boardRow['bo_notice'] : '', $wrId);
        $safeNotice = uno_api_spam_escape($boNotice);
        sql_query("update {$boardTable} set bo_notice = '{$safeNotice}' where bo_table = '{$safeBoard}'");
    }

    if ($countWrite > 0 || $countComment > 0) {
        sql_query("update {$boardTable} set bo_count_write = bo_count_write - '{$countWrite}', bo_count_comment = bo_count_comment - '{$countComment}' where bo_table = '{$safeBoard}'");
    }

    if (function_exists('delete_cache_latest')) {
        delete_cache_latest($safeBoard);
    }

    return array('deleted' => true, 'reason' => '', 'writeCount' => $countWrite, 'commentCount' => $countComment);
}

function uno_api_spam_delete_posts($board, $memberId, $postIds, $confirm)
{
    $targets = uno_api_spam_target_posts($board, $memberId, $postIds);
    if (!$targets) {
        uno_api_error('NOT_FOUND', '삭제할 게시글이 없습니다.', 404);
    }

    if (trim((string) $memberId) !== '' && trim((string) $confirm) !== trim((string) $memberId)) {
        uno_api_error('VALIDATION_ERROR', '작성자 일괄 삭제는 회원 ID를 한 번 더 입력해야 합니다.', 400);
    }

    $boardRow = uno_api_spam_board_row($board);
    $deleted = 0;
    $skipped = array();

    foreach ($targets as $target) {
        $result = uno_api_spam_delete_one_post($board, $target, $boardRow);
        if (!empty($result['deleted'])) {
            $deleted++;
        } else {
            $skipped[] = array(
                'id' => isset($target['wr_id']) ? (int) $target['wr_id'] : 0,
                'reason' => isset($result['reason']) ? $result['reason'] : '삭제하지 못함',
            );
        }
    }

    return array('deleted' => $deleted, 'skipped' => $skipped, 'targetCount' => count($targets));
}

$body = uno_api_spam_json_body();
$action = isset($body['action']) ? (string) $body['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'blockMember') {
    uno_api_spam_block_member(isset($body['memberId']) ? $body['memberId'] : '');
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'deleteSelectedPosts') {
    $result = uno_api_spam_delete_posts(
        isset($body['board']) ? $body['board'] : 'cusTour',
        '',
        isset($body['postIds']) ? $body['postIds'] : array(),
        ''
    );
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'deleteMemberPosts') {
    $result = uno_api_spam_delete_posts(
        isset($body['board']) ? $body['board'] : 'cusTour',
        isset($body['memberId']) ? $body['memberId'] : '',
        array(),
        isset($body['confirm']) ? $body['confirm'] : ''
    );
}

$board = isset($_GET['board']) ? (string) $_GET['board'] : 'cusTour';
$keyword = isset($_GET['keyword']) ? (string) $_GET['keyword'] : '';
$memberId = isset($_GET['memberId']) ? (string) $_GET['memberId'] : '';

uno_api_success(array(
    'posts' => uno_api_spam_fetch_posts($board, $keyword, $memberId),
    'writers' => uno_api_spam_fetch_writers($board),
    'mutation' => isset($result) ? $result : null,
));
