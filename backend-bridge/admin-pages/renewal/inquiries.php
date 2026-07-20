<?php
/*
 * inquiries.php
 * Renewal admin inquiry manager for private cusTour and public qna boards.
 * cusTour is handled fully in the renewal screen: list, detail, reply, edit, and delete.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/inquiries.php');

function uno_renewal_inquiry_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_inquiry_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_inquiry_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_inquiry_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_inquiry_table_exists($tableName)
{
    $safeTable = uno_renewal_inquiry_escape_db($tableName);
    $row = uno_renewal_inquiry_fetch("show tables like '{$safeTable}'");
    return is_array($row) && count($row) > 0;
}

function uno_renewal_inquiry_board_config($board)
{
    $map = array(
        'cusTour' => array(
            'board' => 'cusTour',
            'title' => '1:1 문의 관리',
            'copy' => '마이페이지 1:1 문의 내역입니다. 리뉴얼 관리자 안에서 문의 확인, 답변, 수정, 삭제까지 처리합니다.',
            'legacyHref' => '',
        ),
        'qna' => array(
            'board' => 'qna',
            'title' => '공개 문의 관리',
            'copy' => '커뮤니티 문의하기에서 등록되는 공개 문의입니다. 다른 사용자에게 공개될 수 있는 질문을 관리합니다.',
            'legacyHref' => '/admin/board.php?bo_table=qna',
        ),
    );

    return isset($map[$board]) ? $map[$board] : $map['cusTour'];
}

function uno_renewal_inquiry_write_table($board)
{
    global $g5;
    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . $board;
}

function uno_renewal_inquiry_table_name($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_renewal_inquiry_is_admin_member($memberId)
{
    $memberId = trim((string) $memberId);
    if ($memberId === '') {
        return false;
    }

    $safeMemberId = uno_renewal_inquiry_escape_db($memberId);
    $memberTable = uno_renewal_inquiry_table_name('member_table', 'g5_member');
    $member = uno_renewal_inquiry_fetch("select mb_level from {$memberTable} where mb_id = '{$safeMemberId}' limit 1");
    return $member && isset($member['mb_level']) && (int) $member['mb_level'] > 2;
}

function uno_renewal_inquiry_date($value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    return substr((string) $value, 0, 16);
}

function uno_renewal_inquiry_text($value, $maxLength)
{
    $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    $text = preg_replace("/\r\n|\r|\n/", "\n", $text);
    $text = trim($text);

    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLength, 'UTF-8');
    }

    return substr($text, 0, $maxLength);
}

function uno_renewal_inquiry_textarea($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function uno_renewal_inquiry_admin_member()
{
    global $member;
    return isset($member) && is_array($member) ? $member : array();
}

function uno_renewal_inquiry_insert_id()
{
    if (function_exists('sql_insert_id')) {
        return sql_insert_id();
    }

    if (function_exists('mysql_insert_id')) {
        return mysql_insert_id();
    }

    return 0;
}

function uno_renewal_inquiry_board_row($board)
{
    $safeBoard = uno_renewal_inquiry_escape_db($board);
    $boardTable = uno_renewal_inquiry_table_name('board_table', 'g5_board');
    return uno_renewal_inquiry_fetch("select * from {$boardTable} where bo_table = '{$safeBoard}' limit 1");
}

function uno_renewal_inquiry_find_thread($table, $wrId)
{
    $wrId = (int) $wrId;
    if ($wrId <= 0) {
        return array();
    }

    return uno_renewal_inquiry_fetch("select * from {$table} where wr_id = '{$wrId}' and wr_is_comment = '0' limit 1");
}

function uno_renewal_inquiry_find_comment($table, $commentId, $threadId)
{
    $commentId = (int) $commentId;
    $threadId = (int) $threadId;
    if ($commentId <= 0 || $threadId <= 0) {
        return array();
    }

    return uno_renewal_inquiry_fetch("select * from {$table} where wr_id = '{$commentId}' and wr_parent = '{$threadId}' and wr_is_comment = '1' limit 1");
}

function uno_renewal_inquiry_download_href($board, $wrId, $fileNo)
{
    return '/admin/renewal/inquiry-file.php'
        . '?bo_table=' . rawurlencode($board)
        . '&wr_id=' . (int) $wrId
        . '&no=' . (int) $fileNo;
}

function uno_renewal_inquiry_fetch_files($board, $wrId)
{
    $wrId = (int) $wrId;
    if ($wrId <= 0) {
        return array();
    }

    $safeBoard = uno_renewal_inquiry_escape_db($board);
    $boardFileTable = uno_renewal_inquiry_table_name('board_file_table', 'g5_board_file');
    $result = uno_renewal_inquiry_query(
        "select bf_no, bf_source, bf_file, bf_filesize, bf_download, bf_datetime
           from {$boardFileTable}
          where bo_table = '{$safeBoard}'
            and wr_id = '{$wrId}'
            and bf_file <> ''
          order by bf_no asc"
    );

    $files = array();
    while ($row = uno_renewal_inquiry_fetch_array($result)) {
        $fileNo = isset($row['bf_no']) ? (int) $row['bf_no'] : 0;
        $files[] = array(
            'no' => $fileNo,
            'source' => isset($row['bf_source']) && $row['bf_source'] !== '' ? (string) $row['bf_source'] : (string) ($row['bf_file'] ?? ''),
            'size' => isset($row['bf_filesize']) ? (int) $row['bf_filesize'] : 0,
            'downloadCount' => isset($row['bf_download']) ? (int) $row['bf_download'] : 0,
            'href' => uno_renewal_inquiry_download_href($board, $wrId, $fileNo),
        );
    }

    return $files;
}

function uno_renewal_inquiry_file_size($size)
{
    $size = (int) $size;
    if ($size >= 1024 * 1024) {
        return number_format($size / 1024 / 1024, 1) . 'MB';
    }

    if ($size >= 1024) {
        return number_format($size / 1024, 1) . 'KB';
    }

    return number_format(max(0, $size)) . 'B';
}

function uno_renewal_inquiry_has_attachment()
{
    return isset($_FILES['attachment'])
        && is_array($_FILES['attachment'])
        && isset($_FILES['attachment']['error'])
        && (int) $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE;
}

function uno_renewal_inquiry_validate_attachment()
{
    if (!uno_renewal_inquiry_has_attachment()) {
        return null;
    }

    $file = $_FILES['attachment'];
    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        return array('error' => '첨부파일 업로드에 실패했습니다.');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return array('error' => '첨부파일을 확인할 수 없습니다.');
    }

    $size = isset($file['size']) ? (int) $file['size'] : 0;
    if ($size <= 0 || $size > 10 * 1024 * 1024) {
        return array('error' => '첨부파일은 10MB 이하만 업로드할 수 있습니다.');
    }

    $sourceName = isset($file['name']) ? basename((string) $file['name']) : 'attachment';
    $extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
    if (!in_array($extension, $allowed, true)) {
        return array('error' => 'JPG, PNG, PDF, DOC, DOCX, XLS, XLSX 파일만 업로드할 수 있습니다.');
    }

    return array(
        'tmpName' => $file['tmp_name'],
        'sourceName' => $sourceName,
        'extension' => $extension,
        'size' => $size,
    );
}

function uno_renewal_inquiry_upload_dir($board)
{
    if (defined('G5_DATA_PATH')) {
        return G5_DATA_PATH . '/file/' . $board;
    }

    return dirname(__DIR__, 2) . '/bbs/data/file/' . $board;
}

function uno_renewal_inquiry_save_attachment($board, $table, $wrId, $attachment, $now)
{
    if (!$attachment) {
        return '';
    }

    $wrId = (int) $wrId;
    if ($wrId <= 0) {
        return '첨부파일을 연결할 답변을 찾을 수 없습니다.';
    }

    $uploadDir = uno_renewal_inquiry_upload_dir($board);
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, defined('G5_DIR_PERMISSION') ? G5_DIR_PERMISSION : 0755, true);
    }

    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        return '첨부파일 저장 폴더를 사용할 수 없습니다.';
    }

    $storedFile = date('YmdHis') . '_admin_inquiry_' . $wrId . '_' . bin2hex(random_bytes(4)) . '.' . $attachment['extension'];
    $destination = $uploadDir . '/' . $storedFile;

    if (!move_uploaded_file($attachment['tmpName'], $destination)) {
        return '첨부파일을 저장하지 못했습니다.';
    }

    @chmod($destination, defined('G5_FILE_PERMISSION') ? G5_FILE_PERMISSION : 0644);

    $imageInfo = @getimagesize($destination);
    $width = $imageInfo && isset($imageInfo[0]) ? (int) $imageInfo[0] : 0;
    $height = $imageInfo && isset($imageInfo[1]) ? (int) $imageInfo[1] : 0;
    $type = $imageInfo ? 1 : 0;
    $safeBoard = uno_renewal_inquiry_escape_db($board);
    $source = uno_renewal_inquiry_escape_db($attachment['sourceName']);
    $stored = uno_renewal_inquiry_escape_db($storedFile);
    $fileSize = (int) @filesize($destination);
    $safeNow = uno_renewal_inquiry_escape_db($now);
    $boardFileTable = uno_renewal_inquiry_table_name('board_file_table', 'g5_board_file');

    uno_renewal_inquiry_query(
        "insert into {$boardFileTable}
            set bo_table = '{$safeBoard}',
                wr_id = '{$wrId}',
                bf_no = '0',
                bf_source = '{$source}',
                bf_file = '{$stored}',
                bf_download = '0',
                bf_content = '',
                bf_filesize = '{$fileSize}',
                bf_width = '{$width}',
                bf_height = '{$height}',
                bf_type = '{$type}',
                bf_datetime = '{$safeNow}'"
    );

    uno_renewal_inquiry_query("update {$table} set wr_file = '1' where wr_id = '{$wrId}'");
    return '';
}

function uno_renewal_inquiry_redirect($board, $wrId, $message)
{
    $url = '/admin/renewal/inquiries.php?board=' . rawurlencode($board);
    if ((int) $wrId > 0) {
        $url .= '&wr_id=' . (int) $wrId;
    }
    if ($message !== '') {
        $url .= '&message=' . rawurlencode($message);
    }

    header('Location: ' . $url);
    exit;
}

function uno_renewal_inquiry_update_thread($table, $threadId, $subject, $content)
{
    $threadId = (int) $threadId;
    $safeSubject = uno_renewal_inquiry_escape_db($subject);
    $safeContent = uno_renewal_inquiry_escape_db($content);

    uno_renewal_inquiry_query(
        "update {$table}
            set wr_subject = '{$safeSubject}',
                wr_content = '{$safeContent}'
          where wr_id = '{$threadId}'
            and wr_is_comment = '0'"
    );
}

function uno_renewal_inquiry_insert_answer($board, $table, $thread, $content, $attachment = null)
{
    $admin = uno_renewal_inquiry_admin_member();
    $threadId = (int) $thread['wr_id'];
    $now = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $memberId = isset($admin['mb_id']) ? (string) $admin['mb_id'] : 'admin';
    $memberName = isset($admin['mb_name']) && $admin['mb_name'] !== '' ? (string) $admin['mb_name'] : $memberId;
    $memberEmail = isset($admin['mb_email']) ? (string) $admin['mb_email'] : '';
    $memberPassword = isset($admin['mb_password']) ? (string) $admin['mb_password'] : '';
    $commentRow = uno_renewal_inquiry_fetch(
        "select max(wr_comment) as max_comment
           from {$table}
          where wr_parent = '{$threadId}'
            and wr_is_comment = '1'"
    );
    $commentNo = isset($commentRow['max_comment']) ? (int) $commentRow['max_comment'] + 1 : 1;

    $safeBoard = uno_renewal_inquiry_escape_db($board);
    $safeContent = uno_renewal_inquiry_escape_db($content);
    $safeMemberId = uno_renewal_inquiry_escape_db($memberId);
    $safeMemberName = uno_renewal_inquiry_escape_db(function_exists('clean_xss_tags') ? clean_xss_tags($memberName) : $memberName);
    $safeEmail = uno_renewal_inquiry_escape_db($memberEmail);
    $safePassword = uno_renewal_inquiry_escape_db($memberPassword);
    $safeNow = uno_renewal_inquiry_escape_db($now);
    $safeIp = uno_renewal_inquiry_escape_db($ip);
    $safeCaName = uno_renewal_inquiry_escape_db(isset($thread['ca_name']) ? $thread['ca_name'] : '');
    $safeWrNum = uno_renewal_inquiry_escape_db(isset($thread['wr_num']) ? $thread['wr_num'] : '');

    uno_renewal_inquiry_query(
        "insert into {$table}
            set ca_name = '{$safeCaName}',
                wr_option = '',
                wr_num = '{$safeWrNum}',
                wr_reply = '',
                wr_parent = '{$threadId}',
                wr_is_comment = '1',
                wr_comment = '{$commentNo}',
                wr_comment_reply = '',
                wr_subject = '',
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
                wr_file = '0',
                wr_facebook_user = '',
                wr_twitter_user = '',
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
                wr_10 = ''"
    );

    $commentId = uno_renewal_inquiry_insert_id();
    $attachmentError = '';
    if ($commentId && $attachment) {
        $attachmentError = uno_renewal_inquiry_save_attachment($board, $table, $commentId, $attachment, $now);
    }

    uno_renewal_inquiry_query("update {$table} set wr_comment = wr_comment + 1, wr_last = '{$safeNow}' where wr_id = '{$threadId}'");

    $boardNewTable = uno_renewal_inquiry_table_name('board_new_table', 'g5_board_new');
    $boardTable = uno_renewal_inquiry_table_name('board_table', 'g5_board');
    if ($commentId) {
        uno_renewal_inquiry_query(
            "insert into {$boardNewTable}
                (bo_table, wr_id, wr_parent, bn_datetime, mb_id)
             values
                ('{$safeBoard}', '{$commentId}', '{$threadId}', '{$safeNow}', '{$safeMemberId}')"
        );
    }
    uno_renewal_inquiry_query("update {$boardTable} set bo_count_comment = bo_count_comment + 1 where bo_table = '{$safeBoard}'");

    return $attachmentError;
}

function uno_renewal_inquiry_update_answer($table, $commentId, $threadId, $content)
{
    $commentId = (int) $commentId;
    $threadId = (int) $threadId;
    $safeContent = uno_renewal_inquiry_escape_db($content);

    uno_renewal_inquiry_query(
        "update {$table}
            set wr_content = '{$safeContent}'
          where wr_id = '{$commentId}'
            and wr_parent = '{$threadId}'
            and wr_is_comment = '1'"
    );
}

function uno_renewal_inquiry_delete_answer($board, $table, $commentId, $threadId)
{
    $commentId = (int) $commentId;
    $threadId = (int) $threadId;
    $safeBoard = uno_renewal_inquiry_escape_db($board);
    $boardNewTable = uno_renewal_inquiry_table_name('board_new_table', 'g5_board_new');
    $boardTable = uno_renewal_inquiry_table_name('board_table', 'g5_board');

    uno_renewal_inquiry_query("delete from {$table} where wr_id = '{$commentId}' and wr_parent = '{$threadId}' and wr_is_comment = '1'");
    uno_renewal_inquiry_query("delete from {$boardNewTable} where bo_table = '{$safeBoard}' and wr_id = '{$commentId}'");
    uno_renewal_inquiry_query("update {$table} set wr_comment = greatest(wr_comment - 1, 0) where wr_id = '{$threadId}'");
    uno_renewal_inquiry_query("update {$boardTable} set bo_count_comment = greatest(bo_count_comment - 1, 0) where bo_table = '{$safeBoard}'");
}

function uno_renewal_inquiry_delete_thread($board, $table, $thread)
{
    $threadId = (int) $thread['wr_id'];
    $safeBoard = uno_renewal_inquiry_escape_db($board);
    $boardNewTable = uno_renewal_inquiry_table_name('board_new_table', 'g5_board_new');
    $boardFileTable = uno_renewal_inquiry_table_name('board_file_table', 'g5_board_file');
    $scrapTable = uno_renewal_inquiry_table_name('scrap_table', 'g5_scrap');
    $boardTable = uno_renewal_inquiry_table_name('board_table', 'g5_board');
    $commentCount = isset($thread['wr_comment']) ? (int) $thread['wr_comment'] : 0;

    uno_renewal_inquiry_query("delete from {$table} where wr_parent = '{$threadId}'");
    uno_renewal_inquiry_query("delete from {$boardNewTable} where bo_table = '{$safeBoard}' and wr_parent = '{$threadId}'");
    uno_renewal_inquiry_query("delete from {$boardFileTable} where bo_table = '{$safeBoard}' and wr_id = '{$threadId}'");
    uno_renewal_inquiry_query("delete from {$scrapTable} where bo_table = '{$safeBoard}' and wr_id = '{$threadId}'");
    uno_renewal_inquiry_query("update {$boardTable} set bo_count_write = greatest(bo_count_write - 1, 0), bo_count_comment = greatest(bo_count_comment - '{$commentCount}', 0) where bo_table = '{$safeBoard}'");

    if (function_exists('delete_cache_latest')) {
        delete_cache_latest($board);
    }
}

$boardParam = isset($_GET['board']) ? (string) $_GET['board'] : 'cusTour';
$config = uno_renewal_inquiry_board_config($boardParam);
$board = $config['board'];
$table = uno_renewal_inquiry_write_table($board);
$hasInquiryTable = uno_renewal_inquiry_table_exists($table);
$selectedId = isset($_GET['wr_id']) ? max(0, (int) $_GET['wr_id']) : 0;
$message = isset($_GET['message']) ? trim((string) $_GET['message']) : '';
$csrfToken = function_exists('uno_api_csrf_token') ? uno_api_csrf_token() : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasInquiryTable) {
    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
    $postBoard = isset($_POST['board']) ? (string) $_POST['board'] : $board;
    $postConfig = uno_renewal_inquiry_board_config($postBoard);
    $board = $postConfig['board'];
    $config = $postConfig;
    $table = uno_renewal_inquiry_write_table($board);
    $threadId = isset($_POST['wr_id']) ? (int) $_POST['wr_id'] : 0;
    $thread = uno_renewal_inquiry_find_thread($table, $threadId);

    if (!$thread) {
        uno_renewal_inquiry_redirect($board, 0, '문의 원글을 찾을 수 없습니다.');
    }

    if ($action === 'update_thread') {
        $subject = uno_renewal_inquiry_text(isset($_POST['subject']) ? $_POST['subject'] : '', 255);
        $content = uno_renewal_inquiry_text(isset($_POST['content']) ? $_POST['content'] : '', 65536);
        if ($subject !== '' && $content !== '') {
            uno_renewal_inquiry_update_thread($table, $threadId, $subject, $content);
        }
        uno_renewal_inquiry_redirect($board, $threadId, '문의 내용을 수정했습니다.');
    }

    if ($action === 'create_answer') {
        $content = uno_renewal_inquiry_text(isset($_POST['answer']) ? $_POST['answer'] : '', 65536);
        $attachment = uno_renewal_inquiry_validate_attachment();
        if (isset($attachment['error'])) {
            uno_renewal_inquiry_redirect($board, $threadId, $attachment['error']);
        }
        if ($content !== '') {
            $attachmentError = uno_renewal_inquiry_insert_answer($board, $table, $thread, $content, $attachment);
            if ($attachmentError !== '') {
                uno_renewal_inquiry_redirect($board, $threadId, $attachmentError);
            }
        }
        uno_renewal_inquiry_redirect($board, $threadId, '답변을 등록했습니다.');
    }

    if ($action === 'update_answer') {
        $commentId = isset($_POST['comment_id']) ? (int) $_POST['comment_id'] : 0;
        $content = uno_renewal_inquiry_text(isset($_POST['answer']) ? $_POST['answer'] : '', 65536);
        if ($content !== '' && uno_renewal_inquiry_find_comment($table, $commentId, $threadId)) {
            uno_renewal_inquiry_update_answer($table, $commentId, $threadId, $content);
        }
        uno_renewal_inquiry_redirect($board, $threadId, '답변을 수정했습니다.');
    }

    if ($action === 'delete_answer') {
        $commentId = isset($_POST['comment_id']) ? (int) $_POST['comment_id'] : 0;
        if (uno_renewal_inquiry_find_comment($table, $commentId, $threadId)) {
            uno_renewal_inquiry_delete_answer($board, $table, $commentId, $threadId);
        }
        uno_renewal_inquiry_redirect($board, $threadId, '답변을 삭제했습니다.');
    }

    if ($action === 'delete_thread') {
        uno_renewal_inquiry_delete_thread($board, $table, $thread);
        uno_renewal_inquiry_redirect($board, 0, '문의 내역을 삭제했습니다.');
    }
}

$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;
$where = array("wr_is_comment = '0'");
if ($keyword !== '') {
    $safeKeyword = uno_renewal_inquiry_escape_db($keyword);
    $where[] = "(wr_subject like '%{$safeKeyword}%' or wr_content like '%{$safeKeyword}%' or mb_id like '%{$safeKeyword}%' or wr_name like '%{$safeKeyword}%')";
}
$whereSql = implode(' and ', $where);

$rows = array();
$totalRow = array('cnt' => 0);
$today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
$todayRow = array('cnt' => 0);
$unansweredRow = array('cnt' => 0);
$listTotalRow = array('cnt' => 0);
$listTotalCount = 0;
$totalPages = 1;
$selectedThread = array();
$selectedFiles = array();
$comments = array();

if ($hasInquiryTable) {
    $listTotalRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where {$whereSql}");
    $listTotalCount = isset($listTotalRow['cnt']) ? (int) $listTotalRow['cnt'] : 0;
    $totalPages = max(1, (int) ceil($listTotalCount / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    $result = uno_renewal_inquiry_query(
        "select wr_id, wr_subject, wr_content, wr_name, mb_id, wr_datetime, wr_last, wr_comment, wr_hit, wr_10
           from {$table}
          where {$whereSql}
          order by wr_id desc
          limit {$offset}, {$perPage}"
    );

    while ($row = uno_renewal_inquiry_fetch_array($result)) {
        $rows[] = $row;
    }

    $totalRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0'");
    $todayRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0' and substr(wr_datetime, 1, 10) = '{$today}'");
    $unansweredRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0' and wr_comment = '0'");

    if ($selectedId > 0) {
        $selectedThread = uno_renewal_inquiry_find_thread($table, $selectedId);
        if ($selectedThread) {
            $selectedFiles = uno_renewal_inquiry_fetch_files($board, $selectedId);
            $commentResult = uno_renewal_inquiry_query(
                "select *
                   from {$table}
                  where wr_parent = '{$selectedId}'
                    and wr_is_comment = '1'
                  order by wr_comment asc, wr_comment_reply asc, wr_id asc"
            );
            while ($comment = uno_renewal_inquiry_fetch_array($commentResult)) {
                $comment['files'] = uno_renewal_inquiry_fetch_files($board, (int) ($comment['wr_id'] ?? 0));
                $comments[] = $comment;
            }
        }
    }
}

$actions = array(
    array('label' => '1:1 문의', 'href' => '/admin/renewal/inquiries.php?board=cusTour', 'secondary' => $board !== 'cusTour'),
    array('label' => '공개 문의', 'href' => '/admin/renewal/inquiries.php?board=qna', 'secondary' => $board !== 'qna'),
);
if ($config['legacyHref'] !== '') {
    $actions[] = array('label' => '기존 관리자 보기', 'href' => $config['legacyHref'], 'secondary' => true);
}

$pageHref = function ($targetPage) use ($board, $keyword) {
    $params = array('board' => $board, 'page' => max(1, (int) $targetPage));
    if ($keyword !== '') {
        $params['keyword'] = $keyword;
    }
    return '/admin/renewal/inquiries.php?' . http_build_query($params);
};

uno_renewal_admin_render_head($config['title']);
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead('INQUIRY', $config['title'], $config['copy'], $actions);
?>

<style>
  .inquiry-filter { display:grid;grid-template-columns:auto minmax(220px, 1fr) auto;gap:8px;align-items:center; }
  .inquiry-table { width:100%;border-collapse:collapse;min-width:920px; }
  .inquiry-table th, .inquiry-table td { text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;vertical-align:top; }
  .inquiry-title-link { display:block;font-weight:900; }
  .inquiry-title-link:hover { text-decoration:underline; }
  .inquiry-preview { margin-top:6px;color:var(--uno-muted);font-size:12px;max-width:560px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
  .inquiry-detail-grid { display:grid;grid-template-columns:1fr;gap:16px;margin-top:16px; }
  .inquiry-detail-card { border:1px solid var(--uno-line);background:#fff;padding:18px; }
  .inquiry-detail-card h2, .inquiry-detail-card h3 { margin:0 0 12px; }
  .inquiry-meta { display:flex;flex-wrap:wrap;gap:8px;margin:10px 0 16px; }
  .inquiry-content { white-space:pre-wrap;line-height:1.75;color:#242424; }
  .inquiry-files { margin-top:16px;border-top:1px solid var(--uno-line);padding-top:14px; }
  .inquiry-files h3 { margin:0 0 10px;font-size:14px; }
  .inquiry-file-list { display:flex;flex-wrap:wrap;gap:8px; }
  .inquiry-file-link { display:inline-flex;align-items:center;gap:8px;border:1px solid var(--uno-line);padding:9px 12px;background:#fff;font-size:12px;font-weight:900;text-decoration:none; }
  .inquiry-file-link small { color:var(--uno-muted);font-weight:800; }
  .inquiry-form { display:grid;gap:10px; }
  .inquiry-form input, .inquiry-form textarea { width:100%;border:1px solid var(--uno-line);background:#fff;padding:12px; }
  .inquiry-form textarea { min-height:140px;resize:vertical;line-height:1.6; }
  .inquiry-actions { display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
  .inquiry-timeline { display:flex;flex-direction:column;gap:14px;margin-top:14px;padding:18px;background:#f6f6f4;border:1px solid var(--uno-line);max-height:620px;overflow:auto; }
  .inquiry-message { width:min(72%, 760px);border:1px solid var(--uno-line);background:#fff;padding:14px 16px; }
  .inquiry-message.customer { align-self:flex-start; }
  .inquiry-message.admin { align-self:flex-end;background:#fff7cf;border-color:#f0d35c; }
  .inquiry-message.other { align-self:center;background:#fafafa; }
  .inquiry-message-head { display:flex;justify-content:space-between;gap:12px;color:var(--uno-muted);font-size:12px;font-weight:900;margin-bottom:10px; }
  .inquiry-message-author { display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
  .inquiry-message-body { white-space:pre-wrap;line-height:1.75;color:#242424; }
  .inquiry-message-actions { display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;opacity:.12;transition:opacity .15s ease; }
  .inquiry-message:hover .inquiry-message-actions, .inquiry-message:focus-within .inquiry-message-actions { opacity:1; }
  .inquiry-reply-context { display:none;border:1px solid var(--uno-line);background:#fafafa;padding:10px 12px;color:var(--uno-muted);font-size:12px;font-weight:800;line-height:1.5; }
  .inquiry-reply-context.is-active { display:block; }
  .inquiry-attachment-field { display:grid;gap:8px;border:1px solid var(--uno-line);background:#fafafa;padding:12px; }
  .inquiry-attachment-field input[type="file"] { position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);border:0;padding:0; }
  .inquiry-attachment-controls { display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
  .inquiry-attachment-name { color:var(--uno-muted);font-size:12px;font-weight:800; }
  .inquiry-attachment-help { margin:0;color:var(--uno-muted);font-size:12px;line-height:1.5; }
  .inquiry-edit-panel { margin:0; }
  .inquiry-edit-panel summary { width:max-content;list-style:none; }
  .inquiry-edit-panel summary::-webkit-details-marker { display:none; }
  .inquiry-edit-panel[open] { flex:1 0 100%;order:3;margin-top:6px;border-top:1px solid var(--uno-line);padding-top:14px; }
  .inquiry-edit-panel[open] summary { margin-bottom:10px; }
  .inquiry-pager { display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:16px; }
  .inquiry-pager span { min-height:40px;display:inline-flex;align-items:center;color:var(--uno-muted);font-size:12px;font-weight:900; }
  @media (max-width: 980px) {
    .inquiry-filter, .inquiry-detail-grid { grid-template-columns:1fr; }
  }
</style>

<section class="uno-admin-grid">
  <article class="uno-admin-card"><div><h3>전체 문의</h3><p><?php echo number_format((int) ($totalRow['cnt'] ?? 0)); ?>건</p></div></article>
  <article class="uno-admin-card"><div><h3>오늘 등록</h3><p><?php echo number_format((int) ($todayRow['cnt'] ?? 0)); ?>건</p></div></article>
  <article class="uno-admin-card"><div><h3>미답변</h3><p><?php echo number_format((int) ($unansweredRow['cnt'] ?? 0)); ?>건</p></div></article>
</section>

<?php if ($message !== '') { ?>
  <section class="uno-admin-panel" style="margin-top:16px;"><p class="uno-admin-status ok"><?php echo uno_renewal_admin_escape($message); ?></p></section>
<?php } ?>

<?php if ($selectedThread) { ?>
  <section class="inquiry-detail-grid">
    <article class="inquiry-detail-card">
      <h2><?php echo uno_renewal_admin_escape($selectedThread['wr_subject'] ?? '(제목 없음)'); ?></h2>
      <div class="inquiry-meta">
        <span class="uno-admin-chip">#<?php echo (int) $selectedThread['wr_id']; ?></span>
        <span class="uno-admin-chip"><?php echo uno_renewal_admin_escape($selectedThread['wr_name'] ?? ''); ?></span>
        <span class="uno-admin-chip"><?php echo uno_renewal_admin_escape($selectedThread['mb_id'] ?? ''); ?></span>
        <span class="uno-admin-chip"><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_date($selectedThread['wr_datetime'] ?? '')); ?></span>
      </div>
      <div class="inquiry-actions" style="margin-top:16px;">
        <a class="uno-admin-button secondary" href="/admin/renewal/inquiries.php?board=<?php echo rawurlencode($board); ?>">목록</a>
        <details class="inquiry-edit-panel">
          <summary class="uno-admin-button secondary">수정</summary>
          <form class="inquiry-form" method="post">
            <input type="hidden" name="action" value="update_thread">
            <input type="hidden" name="unotravel_csrf_token" value="<?php echo uno_renewal_admin_escape($csrfToken); ?>">
            <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
            <input type="hidden" name="wr_id" value="<?php echo (int) $selectedThread['wr_id']; ?>">
            <input name="subject" value="<?php echo uno_renewal_admin_escape($selectedThread['wr_subject'] ?? ''); ?>" required>
            <textarea name="content" required><?php echo uno_renewal_inquiry_textarea($selectedThread['wr_content'] ?? ''); ?></textarea>
            <button class="uno-admin-button" type="submit">수정 저장</button>
          </form>
        </details>
        <form method="post" onsubmit="return confirm('이 문의와 답변을 모두 삭제할까요?');">
          <input type="hidden" name="action" value="delete_thread">
          <input type="hidden" name="unotravel_csrf_token" value="<?php echo uno_renewal_admin_escape($csrfToken); ?>">
          <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
          <input type="hidden" name="wr_id" value="<?php echo (int) $selectedThread['wr_id']; ?>">
          <button class="uno-admin-button secondary" type="submit">삭제</button>
        </form>
      </div>
    </article>
  </section>

  <section class="inquiry-detail-grid">
    <article class="inquiry-detail-card">
      <h2>문의 대화 내역</h2>
      <div class="inquiry-timeline">
        <div class="inquiry-message customer" data-message-author="<?php echo uno_renewal_admin_escape($selectedThread['wr_name'] ?? '고객'); ?>" data-message-text="<?php echo uno_renewal_admin_escape(uno_renewal_inquiry_text($selectedThread['wr_content'] ?? '', 90)); ?>">
          <div class="inquiry-message-head">
            <span class="inquiry-message-author">
              <span class="uno-admin-chip">고객 원글</span>
              <span><?php echo uno_renewal_admin_escape(($selectedThread['wr_name'] ?? '') . ' / ' . ($selectedThread['mb_id'] ?? '')); ?></span>
            </span>
            <span><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_date($selectedThread['wr_datetime'] ?? '')); ?></span>
          </div>
          <div class="inquiry-message-body"><?php echo uno_renewal_admin_escape($selectedThread['wr_content'] ?? ''); ?></div>
          <?php if ($selectedFiles) { ?>
            <div class="inquiry-files">
              <h3>첨부파일</h3>
              <div class="inquiry-file-list">
                <?php foreach ($selectedFiles as $file) { ?>
                  <a class="inquiry-file-link" href="<?php echo uno_renewal_admin_escape($file['href']); ?>" target="_blank" rel="noopener">
                    <?php echo uno_renewal_admin_escape($file['source']); ?>
                    <small><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_file_size($file['size'])); ?></small>
                  </a>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
          <div class="inquiry-message-actions">
            <button class="uno-admin-button secondary" type="button" data-reply-target>답장</button>
          </div>
        </div>

        <?php foreach ($comments as $comment) { ?>
          <?php
            $commentMemberId = isset($comment['mb_id']) ? (string) $comment['mb_id'] : '';
            $threadMemberId = isset($selectedThread['mb_id']) ? (string) $selectedThread['mb_id'] : '';
            if ($commentMemberId !== '' && $commentMemberId === $threadMemberId) {
                $commentLabel = '고객 추가 문의';
                $commentType = 'customer';
            } elseif (uno_renewal_inquiry_is_admin_member($commentMemberId)) {
                $commentLabel = '관리자 답변';
                $commentType = 'admin';
            } else {
                $commentLabel = '기타 작성자';
                $commentType = 'other';
            }
            $commentFiles = isset($comment['files']) && is_array($comment['files']) ? $comment['files'] : array();
          ?>
          <div class="inquiry-message <?php echo uno_renewal_admin_escape($commentType); ?>" data-message-author="<?php echo uno_renewal_admin_escape($comment['wr_name'] ?? '작성자'); ?>" data-message-text="<?php echo uno_renewal_admin_escape(uno_renewal_inquiry_text($comment['wr_content'] ?? '', 90)); ?>">
            <div class="inquiry-message-head">
              <span class="inquiry-message-author">
                <span class="uno-admin-chip"><?php echo uno_renewal_admin_escape($commentLabel); ?></span>
                <span><?php echo uno_renewal_admin_escape(($comment['wr_name'] ?? '') . ' / ' . ($comment['mb_id'] ?? '')); ?></span>
              </span>
              <span><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_date($comment['wr_datetime'] ?? '')); ?></span>
            </div>
            <div class="inquiry-message-body"><?php echo uno_renewal_admin_escape($comment['wr_content'] ?? ''); ?></div>
            <?php if ($commentFiles) { ?>
              <div class="inquiry-files">
                <h3>첨부파일</h3>
                <div class="inquiry-file-list">
                  <?php foreach ($commentFiles as $file) { ?>
                    <a class="inquiry-file-link" href="<?php echo uno_renewal_admin_escape($file['href']); ?>" target="_blank" rel="noopener">
                      <?php echo uno_renewal_admin_escape($file['source']); ?>
                      <small><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_file_size($file['size'])); ?></small>
                    </a>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
            <div class="inquiry-message-actions">
              <?php if ($commentType === 'customer') { ?>
                <button class="uno-admin-button secondary" type="button" data-reply-target>답장</button>
              <?php } ?>
              <?php if ($commentType === 'admin') { ?>
                <details class="inquiry-edit-panel">
                  <summary class="uno-admin-button secondary">수정</summary>
                  <form class="inquiry-form" method="post">
                    <input type="hidden" name="action" value="update_answer">
                    <input type="hidden" name="unotravel_csrf_token" value="<?php echo uno_renewal_admin_escape($csrfToken); ?>">
                    <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
                    <input type="hidden" name="wr_id" value="<?php echo (int) $selectedThread['wr_id']; ?>">
                    <input type="hidden" name="comment_id" value="<?php echo (int) $comment['wr_id']; ?>">
                    <textarea name="answer" required><?php echo uno_renewal_inquiry_textarea($comment['wr_content'] ?? ''); ?></textarea>
                    <button class="uno-admin-button" type="submit">답변 수정</button>
                  </form>
                </details>
                <form method="post" onsubmit="return confirm('이 답변을 삭제할까요?');">
                  <input type="hidden" name="action" value="delete_answer">
                  <input type="hidden" name="unotravel_csrf_token" value="<?php echo uno_renewal_admin_escape($csrfToken); ?>">
                  <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
                  <input type="hidden" name="wr_id" value="<?php echo (int) $selectedThread['wr_id']; ?>">
                  <input type="hidden" name="comment_id" value="<?php echo (int) $comment['wr_id']; ?>">
                  <button class="uno-admin-button secondary" type="submit">삭제</button>
                </form>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      </div>

      <form id="inquiry-answer-form" class="inquiry-form" method="post" enctype="multipart/form-data" style="margin-top:16px;">
        <input type="hidden" name="action" value="create_answer">
        <input type="hidden" name="unotravel_csrf_token" value="<?php echo uno_renewal_admin_escape($csrfToken); ?>">
        <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
        <input type="hidden" name="wr_id" value="<?php echo (int) $selectedThread['wr_id']; ?>">
        <div id="inquiry-reply-context" class="inquiry-reply-context"></div>
        <textarea id="inquiry-answer-textarea" name="answer" placeholder="고객에게 전달할 답변을 입력하세요." required></textarea>
        <div class="inquiry-attachment-field">
          <div class="inquiry-attachment-controls">
            <label class="uno-admin-button secondary" for="inquiry-answer-attachment">첨부파일 선택</label>
            <button class="uno-admin-button secondary" type="button" id="inquiry-answer-attachment-clear">삭제</button>
            <span class="inquiry-attachment-name" id="inquiry-answer-attachment-name">선택된 파일 없음</span>
          </div>
          <input id="inquiry-answer-attachment" name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
          <p class="inquiry-attachment-help">JPG, PNG, PDF, DOC, DOCX, XLS, XLSX / 최대 10MB / 1개</p>
        </div>
        <button class="uno-admin-button" type="submit">답변 등록</button>
      </form>
    </article>
  </section>
<?php } ?>

<script>
  document.addEventListener("click", function (event) {
    var trigger = event.target.closest("[data-reply-target]");
    if (!trigger) return;

    var message = trigger.closest(".inquiry-message");
    var form = document.getElementById("inquiry-answer-form");
    var textarea = document.getElementById("inquiry-answer-textarea");
    var context = document.getElementById("inquiry-reply-context");
    if (!message || !form || !textarea || !context) return;

    var author = message.getAttribute("data-message-author") || "고객";
    var text = message.getAttribute("data-message-text") || "";
    context.textContent = author + "님의 메시지에 답장 중" + (text ? " · " + text : "");
    context.classList.add("is-active");
    form.scrollIntoView({ behavior: "smooth", block: "center" });
    window.setTimeout(function () {
      textarea.focus();
    }, 220);
  });

  var attachmentInput = document.getElementById("inquiry-answer-attachment");
  var attachmentName = document.getElementById("inquiry-answer-attachment-name");
  var attachmentClear = document.getElementById("inquiry-answer-attachment-clear");
  if (attachmentInput && attachmentName && attachmentClear) {
    attachmentInput.addEventListener("change", function () {
      attachmentName.textContent = attachmentInput.files && attachmentInput.files.length
        ? attachmentInput.files[0].name
        : "선택된 파일 없음";
    });
    attachmentClear.addEventListener("click", function () {
      attachmentInput.value = "";
      attachmentName.textContent = "선택된 파일 없음";
    });
  }
</script>

<section class="uno-admin-panel" style="margin-top:16px;">
  <form class="inquiry-filter" method="get">
    <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
    <input type="hidden" name="page" value="1">
    <strong><?php echo uno_renewal_admin_escape($board === 'cusTour' ? '1:1 문의' : '공개 문의'); ?></strong>
    <input name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="제목, 내용, 작성자, 아이디 검색" style="height:42px;border:1px solid var(--uno-line);padding:0 12px;">
    <button class="uno-admin-button" type="submit">검색</button>
  </form>
</section>

<section class="uno-admin-panel" style="margin-top:16px;overflow:auto;">
  <?php if (!$hasInquiryTable) { ?>
    <p class="uno-admin-status warn">현재 DB에 <code><?php echo uno_renewal_admin_escape($table); ?></code> 테이블이 없어 문의 목록을 표시할 수 없습니다.</p>
  <?php } ?>
  <table class="inquiry-table">
    <thead>
      <tr>
        <th>번호</th>
        <th>제목</th>
        <th>작성자</th>
        <th>등록일</th>
        <th>답변</th>
        <th>관리</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows) { ?>
        <tr><td colspan="6" style="padding:28px 12px;color:var(--uno-muted);">문의 내역이 없습니다.</td></tr>
      <?php } ?>
      <?php foreach ($rows as $row) {
          $wrId = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;
          $subject = isset($row['wr_subject']) && $row['wr_subject'] !== '' ? (string) $row['wr_subject'] : '(제목 없음)';
          $commentCount = isset($row['wr_comment']) ? (int) $row['wr_comment'] : 0;
      ?>
        <tr>
          <td><?php echo $wrId; ?></td>
          <td>
            <a class="inquiry-title-link" href="/admin/renewal/inquiries.php?board=<?php echo rawurlencode($board); ?>&wr_id=<?php echo $wrId; ?>"><?php echo uno_renewal_admin_escape($subject); ?></a>
            <div class="inquiry-preview"><?php echo uno_renewal_admin_escape(trim(strip_tags((string) ($row['wr_content'] ?? '')))); ?></div>
          </td>
          <td>
            <?php echo uno_renewal_admin_escape((string) ($row['wr_name'] ?? '')); ?>
            <div style="color:var(--uno-muted);font-size:12px;"><?php echo uno_renewal_admin_escape((string) ($row['mb_id'] ?? '')); ?></div>
          </td>
          <td><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_date($row['wr_datetime'] ?? '')); ?></td>
          <td><span class="uno-admin-chip <?php echo $commentCount > 0 ? 'good' : 'warn'; ?>"><?php echo $commentCount > 0 ? '답변 ' . $commentCount . '개' : '미답변'; ?></span></td>
          <td><a class="uno-admin-button secondary" href="/admin/renewal/inquiries.php?board=<?php echo rawurlencode($board); ?>&wr_id=<?php echo $wrId; ?>">열기</a></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
  <nav class="inquiry-pager" aria-label="문의 페이지">
    <?php if ($page > 1) { ?>
      <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($pageHref($page - 1)); ?>">이전</a>
    <?php } ?>
    <span><?php echo number_format($page); ?> / <?php echo number_format($totalPages); ?> 페이지 · <?php echo number_format($listTotalCount); ?>건</span>
    <?php if ($page < $totalPages) { ?>
      <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($pageHref($page + 1)); ?>">다음</a>
    <?php } ?>
  </nav>
</section>

<?php
uno_renewal_admin_render_footer();
