<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/WebsiteManager.php';

// 错误处理函数
function handleError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// 实例化网站管理器
$websiteManager = new WebsiteManager();

// 获取所有网站列表
try {
    $websites = $websiteManager->getAllWebsites();
    echo json_encode([
        'success' => true,
        'data' => $websites
    ]);
} catch (Exception $e) {
    error_log("API错误: " . $e->getMessage(), 0);
    handleError("获取网站列表失败");
}
