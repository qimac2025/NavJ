<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit;
}

require_once '../includes/Database.php';
require_once '../includes/WebsiteManager.php';

$websiteManager = new WebsiteManager();
$rootPath = dirname(dirname(__FILE__));

// 创建base64格式的默认图片
$defaultImageBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAABmJLR0QA/wD/AP+gvaeTAAAA70lEQVR4nO3ZQQ6DIBRF0br/TTujnTU2QQwI7z3nTGl4+BF/PwAAAAAAAAAAAAAAAGDEY/UHpHkl3Oc1+8a7B5IZxKxQdg0lK4xZYewYTEYQK4LYPZ5r8QvvvL9dPt9Z/T1ZQQBABz7Qj9nhs6xbYQRRQASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQRGEBhBYASBEQQAAAAAAMAP+QDDiAZ0wqrcpgAAAABJRU5ErkJggg==';

// 获取请求数据
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

header('Content-Type: application/json');

// 验证远程图片URL
function validateImageUrl($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // 设置请求选项
    $options = [
        'http' => [
            'method' => 'HEAD',
            'header' => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]
    ];
    
    $context = stream_context_create($options);
    
    // 尝试获取头信息
    $headers = @get_headers($url, 1, $context);
    if (!$headers) {
        return false;
    }
    
    // 检查HTTP状态码
    $statusLine = $headers[0];
    if (strpos($statusLine, '200') === false) {
        return false;
    }
    
    // 检查Content-Type
    if (isset($headers['Content-Type'])) {
        $contentType = is_array($headers['Content-Type']) 
            ? $headers['Content-Type'][0] 
            : $headers['Content-Type'];
            
        // 支持更多图片MIME类型
        $validTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-icon',
            'image/svg+xml'
        ];
        
        foreach ($validTypes as $type) {
            if (stripos($contentType, $type) !== false) {
                return true;
            }
        }
    }
    
    // 如果无法确定Content-Type，检查文件扩展名
    $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'svg'];
    
    return in_array($extension, $validExtensions);
}

switch ($action) {
    case 'add':
        if (empty($data['title']) || empty($data['url'])) {
            echo json_encode(['success' => false, 'error' => '标题和URL不能为空']);
            exit;
        }
        
        // 处理logo_path
        if (!empty($data['logo_path'])) {
            // 如果是远程URL，验证是否是有效的图片URL
            if (filter_var($data['logo_path'], FILTER_VALIDATE_URL)) {
                if (!validateImageUrl($data['logo_path'])) {
                    echo json_encode(['success' => false, 'error' => '无效的图片URL']);
                    exit;
                }
            } else {
                // 如果是本地路径，确保文件存在
                $localPath = $rootPath . $data['logo_path'];
                if (!file_exists($localPath)) {
                    echo json_encode(['success' => false, 'error' => '图片文件不存在']);
                    exit;
                }
            }
        }
        
        $result = $websiteManager->addWebsite(
            $data['title'],
            $data['description'] ?? '',
            $data['url'],
            $data['logo_path'] ?? '',
            intval($data['sort_order'] ?? 0)
        );
        
        echo json_encode(['success' => $result]);
        break;
        
    case 'update':
        if (empty($id)) {
            echo json_encode(['success' => false, 'error' => '缺少ID']);
            exit;
        }
        
        // 处理logo_path
        if (!empty($data['logo_path'])) {
            // 如果是远程URL，验证是否是有效的图片URL
            if (filter_var($data['logo_path'], FILTER_VALIDATE_URL)) {
                if (!validateImageUrl($data['logo_path'])) {
                    echo json_encode(['success' => false, 'error' => '无效的图片URL']);
                    exit;
                }
            } else {
                // 如果是本地路径，确保文件存在
                $localPath = $rootPath . $data['logo_path'];
                if (!file_exists($localPath)) {
                    echo json_encode(['success' => false, 'error' => '图片文件不存在']);
                    exit;
                }
            }
        }
        
        $result = $websiteManager->updateWebsite($id, $data);
        echo json_encode(['success' => $result]);
        break;
        
    case 'delete':
        if (empty($id)) {
            echo json_encode(['success' => false, 'error' => '缺少ID']);
            exit;
        }
        
        // 获取网站信息
        $website = $websiteManager->getWebsite($id);
        if ($website) {
            // 如果是本地图片，删除图片文件
            $logoPath = $website['logo_path'];
            if (!filter_var($logoPath, FILTER_VALIDATE_URL) && !empty($logoPath)) {
                $localPath = $rootPath . $logoPath;
                if (file_exists($localPath)) {
                    @unlink($localPath);
                }
            }
        }
        
        $result = $websiteManager->deleteWebsite($id);
        echo json_encode(['success' => $result]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => '未知操作']);
}
