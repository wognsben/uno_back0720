<?php
/*
 * inquiries/file.php
 * Logged-in member file viewer/downloader for their own cusTour inquiry attachments.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';

uno_api_require_method('GET');
uno_api_require_login();

function uno_api_inquiry_file_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_api_inquiry_file_table()
{
    global $g5;
    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . 'cusTour';
}

function uno_api_inquiry_file_table_name($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_api_inquiry_file_error($message, $status = 404)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    echo $message;
    exit;
}

function uno_api_inquiry_file_data_dir()
{
    if (defined('G5_DATA_PATH') && G5_DATA_PATH !== '') {
        return rtrim(G5_DATA_PATH, '/\\') . '/file/cusTour';
    }

    return dirname(__DIR__, 2) . '/bbs/data/file/cusTour';
}

function uno_api_inquiry_file_mime($extension)
{
    $map = array(
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    );

    return isset($map[$extension]) ? $map[$extension] : '';
}

if (!function_exists('sql_fetch')) {
    uno_api_inquiry_file_error('DB 함수를 사용할 수 없습니다.', 500);
}

$wrId = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
$fileNo = isset($_GET['no']) ? (int) $_GET['no'] : 0;
if ($wrId <= 0 || $fileNo < 0) {
    uno_api_inquiry_file_error('잘못된 접근입니다.', 400);
}

$memberId = uno_api_current_member_id();
$safeMemberId = uno_api_inquiry_file_escape($memberId);
$table = uno_api_inquiry_file_table();
$target = sql_fetch(
    "select *
       from {$table}
      where wr_id = '{$wrId}'
      limit 1"
);

if (!$target || empty($target['wr_id'])) {
    uno_api_inquiry_file_error('잘못된 접근입니다.', 403);
}

$isComment = isset($target['wr_is_comment']) && (int) $target['wr_is_comment'] === 1;
if ($isComment) {
    $parentId = isset($target['wr_parent']) ? (int) $target['wr_parent'] : 0;
    if ($parentId <= 0) {
        uno_api_inquiry_file_error('잘못된 접근입니다.', 403);
    }

    $thread = sql_fetch(
        "select mb_id
           from {$table}
          where wr_id = '{$parentId}'
            and wr_is_comment = '0'
          limit 1"
    );
    if (!$thread || !isset($thread['mb_id']) || (string) $thread['mb_id'] !== $memberId) {
        uno_api_inquiry_file_error('잘못된 접근입니다.', 403);
    }
} elseif ((int) $target['wr_is_comment'] === 0) {
    if (!isset($target['mb_id']) || (string) $target['mb_id'] !== $memberId) {
        uno_api_inquiry_file_error('잘못된 접근입니다.', 403);
    }
} else {
    uno_api_inquiry_file_error('잘못된 접근입니다.', 403);
}

$boardFileTable = uno_api_inquiry_file_table_name('board_file_table', 'g5_board_file');
$file = sql_fetch(
    "select bf_source, bf_file, bf_filesize
       from {$boardFileTable}
      where bo_table = 'cusTour'
        and wr_id = '{$wrId}'
        and bf_no = '{$fileNo}'
        and bf_file <> ''
      limit 1"
);

if (!$file || empty($file['bf_file'])) {
    uno_api_inquiry_file_error('첨부파일을 찾을 수 없습니다.', 404);
}

$storedName = str_replace(array('../', '..\\', '/', '\\'), '', (string) $file['bf_file']);
$sourceName = isset($file['bf_source']) && $file['bf_source'] !== ''
    ? (string) $file['bf_source']
    : $storedName;
$filePath = uno_api_inquiry_file_data_dir() . '/' . $storedName;

if ($storedName === '' || !is_file($filePath) || !is_readable($filePath)) {
    uno_api_inquiry_file_error('첨부파일을 열 수 없습니다.', 404);
}

$extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
if ($extension === '') {
    $extension = strtolower(pathinfo($storedName, PATHINFO_EXTENSION));
}

$mime = uno_api_inquiry_file_mime($extension);
if ($mime === '') {
    uno_api_inquiry_file_error('허용되지 않은 첨부파일입니다.', 400);
}

$disposition = in_array($extension, array('jpg', 'jpeg', 'png', 'pdf'), true) ? 'inline' : 'attachment';
$downloadName = str_replace(array("\r", "\n", '"'), '', basename($sourceName));

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($downloadName) . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($filePath);
exit;
