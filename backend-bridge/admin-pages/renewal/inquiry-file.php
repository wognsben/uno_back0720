<?php
/*
 * inquiry-file.php
 * Admin-only file viewer/downloader for renewal inquiry attachments.
 * Images and PDFs are opened inline; office documents are downloaded.
 */

require_once __DIR__ . '/_guard.php';

uno_renewal_admin_require_access('/admin/renewal/inquiries.php');

function uno_renewal_inquiry_file_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_inquiry_file_table_name($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_renewal_inquiry_file_error($message, $status = 404)
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo $message;
    exit;
}

function uno_renewal_inquiry_file_data_dir($board)
{
    if (defined('G5_DATA_PATH') && G5_DATA_PATH !== '') {
        return rtrim(G5_DATA_PATH, '/\\') . '/file/' . $board;
    }

    return dirname(__DIR__, 2) . '/bbs/data/file/' . $board;
}

function uno_renewal_inquiry_file_mime($extension)
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

    return isset($map[$extension]) ? $map[$extension] : 'application/octet-stream';
}

function uno_renewal_inquiry_file_disposition($extension)
{
    return in_array($extension, array('jpg', 'jpeg', 'png', 'pdf'), true) ? 'inline' : 'attachment';
}

if (!function_exists('sql_fetch')) {
    uno_renewal_inquiry_file_error('DB 함수를 사용할 수 없습니다.', 500);
}

$board = isset($_GET['bo_table']) ? (string) $_GET['bo_table'] : '';
$wrId = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
$fileNo = isset($_GET['no']) ? (int) $_GET['no'] : 0;

if (!in_array($board, array('cusTour', 'qna'), true) || $wrId <= 0 || $fileNo < 0) {
    uno_renewal_inquiry_file_error('잘못된 접근입니다.', 400);
}

$safeBoard = uno_renewal_inquiry_file_escape_db($board);
$boardFileTable = uno_renewal_inquiry_file_table_name('board_file_table', 'g5_board_file');
$file = sql_fetch(
    "select bf_source, bf_file, bf_filesize
       from {$boardFileTable}
      where bo_table = '{$safeBoard}'
        and wr_id = '{$wrId}'
        and bf_no = '{$fileNo}'
        and bf_file <> ''
      limit 1"
);

if (!$file || empty($file['bf_file'])) {
    uno_renewal_inquiry_file_error('첨부파일을 찾을 수 없습니다.', 404);
}

$storedName = str_replace(array('../', '..\\', '/', '\\'), '', (string) $file['bf_file']);
$sourceName = isset($file['bf_source']) && $file['bf_source'] !== ''
    ? (string) $file['bf_source']
    : $storedName;
$filePath = uno_renewal_inquiry_file_data_dir($board) . '/' . $storedName;

if ($storedName === '' || !is_file($filePath) || !is_readable($filePath)) {
    uno_renewal_inquiry_file_error('첨부파일을 열 수 없습니다.', 404);
}

$extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
if ($extension === '') {
    $extension = strtolower(pathinfo($storedName, PATHINFO_EXTENSION));
}

$mime = uno_renewal_inquiry_file_mime($extension);
$disposition = uno_renewal_inquiry_file_disposition($extension);
$fileSize = filesize($filePath);
$downloadName = str_replace(array("\r", "\n", '"'), '', basename($sourceName));

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . $fileSize);
header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($downloadName) . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');

readfile($filePath);
exit;
