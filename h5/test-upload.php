<?php
/**
 * 测试脚本 - 检查上传功能是否可用
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$checks = [];

// 检查PHP版本
$checks['php_version'] = PHP_VERSION;

// 检查上传目录
$uploadDirs = ['uploads/video', 'uploads/image', 'logs'];
foreach ($uploadDirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    $checks['dir_' . str_replace('/', '_', $dir)] = [
        'exists' => is_dir($path),
        'writable' => is_writable($path) || is_writable(__DIR__),
        'path' => $path
    ];

    // 尝试创建目录
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
        $checks['dir_' . str_replace('/', '_', $dir)]['created'] = is_dir($path);
    }
}

// 检查PHP上传配置
$checks['upload_config'] = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'file_uploads' => ini_get('file_uploads') ? 'On' : 'Off'
];

// 检查临时目录
$checks['tmp_dir'] = [
    'path' => sys_get_temp_dir(),
    'writable' => is_writable(sys_get_temp_dir())
];

// 检查fileinfo扩展
$checks['fileinfo_extension'] = extension_loaded('fileinfo');

// 总体状态
$checks['status'] = 'ok';
$checks['message'] = 'PHP服务正常运行';

echo json_encode($checks, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
