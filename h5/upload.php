<?php
/**
 * H5视频上传接口
 * 支持视频和图片本地上传
 */

// 错误处理：捕获所有错误并返回JSON
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 捕获致命错误
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ret' => 500,
            'msg' => 'PHP错误: ' . $error['message'],
            'data' => []
        ], JSON_UNESCAPED_UNICODE);
    }
});

// 日志函数
function writeLog($message) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/upload_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

try {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-Requested-With');

    // 处理预检请求
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    writeLog("收到上传请求: " . $_SERVER['REQUEST_METHOD']);

// 错误响应函数
function jsonError($code, $msg) {
    writeLog("错误 [$code]: $msg");
    echo json_encode(['ret' => $code, 'msg' => $msg, 'data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

// 成功响应函数
function jsonSuccess($data) {
    writeLog("上传成功: " . ($data['path'] ?? ''));
    echo json_encode(['ret' => 200, 'msg' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

// 验证登录状态
$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$uid = isset($_POST['uid']) ? intval($_POST['uid']) : 0;

if (empty($token) || empty($uid)) {
    jsonError(700, '请先登录');
}

// 检查文件上传
writeLog("FILES: " . print_r($_FILES, true));
writeLog("POST: " . print_r($_POST, true));

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = '文件上传失败';
    $errorCode = isset($_FILES['file']['error']) ? $_FILES['file']['error'] : 'NO_FILE';
    writeLog("上传错误码: $errorCode");

    if (isset($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMsg = '文件超过php.ini限制 (upload_max_filesize=' . ini_get('upload_max_filesize') . ')';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = '文件超过表单限制';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = '文件上传不完整';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = '没有选择文件';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMsg = '服务器临时目录不存在';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMsg = '无法写入磁盘';
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMsg = 'PHP扩展阻止了上传';
                break;
        }
    }
    jsonError(1001, $errorMsg);
}

$file = $_FILES['file'];
$type = isset($_POST['type']) ? trim($_POST['type']) : 'video'; // video 或 image

// 获取文件扩展名
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// 验证文件类型
$allowedVideo = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'];
$allowedImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if ($type === 'video') {
    if (!in_array($ext, $allowedVideo)) {
        jsonError(1002, '不支持的视频格式，请上传 MP4、MOV 等格式');
    }
    $maxSize = 100 * 1024 * 1024; // 100MB
    $uploadDir = 'uploads/video/';
} else {
    if (!in_array($ext, $allowedImage)) {
        jsonError(1002, '不支持的图片格式，请上传 JPG、PNG 等格式');
    }
    $maxSize = 10 * 1024 * 1024; // 10MB
    $uploadDir = 'uploads/image/';
}

// 验证文件大小
if ($file['size'] > $maxSize) {
    jsonError(1003, '文件太大，视频最大100MB，图片最大10MB');
}

// 验证MIME类型（安全检查）
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = [
    'video' => ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska', 'video/webm'],
    'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
];

if (!in_array($mimeType, $allowedMimes[$type])) {
    jsonError(1004, '文件类型验证失败');
}

// 创建上传目录
$dateDir = date('Y/m/d/');
$fullDir = $uploadDir . $dateDir;
if (!is_dir($fullDir)) {
    if (!mkdir($fullDir, 0755, true)) {
        jsonError(1005, '创建目录失败');
    }
}

// 生成唯一文件名
$newName = md5(uniqid() . $uid . time()) . '.' . $ext;
$filePath = $fullDir . $newName;

// 移动上传文件
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    jsonError(1006, '保存文件失败');
}

// 获取完整URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$fileUrl = $protocol . '://' . $host . $basePath . '/' . $filePath;

// 返回成功
jsonSuccess([
    'url' => $fileUrl,
    'path' => $filePath,
    'name' => $file['name'],
    'size' => $file['size'],
    'type' => $type
]);

} catch (Exception $e) {
    writeLog("异常: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ret' => 500,
        'msg' => '服务器错误: ' . $e->getMessage(),
        'data' => []
    ], JSON_UNESCAPED_UNICODE);
}
