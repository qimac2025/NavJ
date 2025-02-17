<?php
session_start();

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

require_once '../includes/Database.php';
require_once '../includes/WebsiteManager.php';

// 初始化变量
$websites = [];
$totalPages = 1;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$error = null;

try {
    $websiteManager = new WebsiteManager();
    
    // 获取网站列表和总页数
    $websites = $websiteManager->getWebsites($currentPage);
    $totalPages = $websiteManager->getTotalPages();
    
    // 处理图片路径
    foreach ($websites as &$website) {
        if (!empty($website['logo_path'])) {
            if (!filter_var($website['logo_path'], FILTER_VALIDATE_URL)) {
                // 确保logo_path是相对于网站根目录的路径
                $website['logo_path'] = '../' . ltrim($website['logo_path'], '/');
            }
        } else {
            $website['logo_path'] = '../images/default-logo.png';
        }
    }
    unset($website); // 解除引用
    
} catch (Exception $e) {
    error_log("管理页面错误: " . $e->getMessage());
    $error = "系统错误：" . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站管理</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background-color: #fff;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
        }
        
        .header h1 {
            font-size: 24px;
            color: #e74c3c;
            margin: 0;
        }
        
        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            color: #fff;
            background-color: #e74c3c;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #c0392b;
        }
        
        .btn.logout {
            text-decoration: none;
            background-color: #95a5a6;
        }
        
        .btn.logout:hover {
            background-color: #7f8c8d;
        }
        
        .websites-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
        }
        
        .websites-table th,
        .websites-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #fce4e4;
            vertical-align: middle;
        }
        
        .websites-table th {
            background-color: #fdf1f0;
            font-weight: 600;
            color: #e74c3c;
        }
        
        .websites-table tr:hover {
            background-color: #fff1f0;
        }
        
        .logo-preview {
            width: 50px;
            height: 50px;
            object-fit: contain;
            background: #f8f9fa;
            border: 1px solid #eee;
            padding: 4px;
            border-radius: 4px;
            vertical-align: middle;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #e74c3c;
        }
        
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #fce4e4;
            border-radius: 4px;
            transition: all 0.2s ease-in-out;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="url"]:focus,
        .form-group input[type="number"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #e74c3c;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
            color: #e74c3c;
            background: #fff;
            border: 1px solid #fce4e4;
            border-radius: 4px;
            transition: all 0.2s ease-in-out;
        }
        
        .pagination a.active {
            background-color: #e74c3c;
            color: #fff;
            border-color: #e74c3c;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #fdf1f0;
            border-color: #e74c3c;
        }
        
        .preview-container {
            position: relative;
            width: 100%;
            height: 100%;
            margin: 10px 0;
            border: 1px solid #eee;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: transparent;
        }
        
        .preview-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
        }
        
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        #websiteForm {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.15);
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        
        #websiteForm h2 {
            margin-bottom: 20px;
            color: #e74c3c;
            font-size: 20px;
            font-weight: 600;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
            backdrop-filter: blur(2px);
        }
        
        .logo-input-group {
            margin-top: 10px;
        }
        
        .logo-input-group select {
            margin-bottom: 10px;
        }
        
        #urlInput {
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons .btn {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .websites-table {
                display: block;
                overflow-x: auto;
            }
            
            .websites-table th,
            .websites-table td {
                white-space: nowrap;
                padding: 8px;
            }
            
            #websiteForm {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>网站管理</h1>
            <div class="header-buttons">
                <button class="btn" onclick="showAddForm()">添加网站</button>
                <a href="change_password.php" class="btn">修改密码</a>
                <a href="logout.php" class="btn logout">退出登录</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table class="websites-table">
            <thead>
                <tr>
                    <th>排序</th>
                    <th>Logo</th>
                    <th>标题</th>
                    <th>描述</th>
                    <th>URL</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($websites)): ?>
                    <?php foreach ($websites as $website): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($website['sort_order']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($website['logo_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($website['title']); ?>" 
                                     style="max-width: 100px;"
                                     onerror="this.src='../images/default-logo.png'">
                            </td>
                            <td><?php echo htmlspecialchars($website['title']); ?></td>
                            <td><?php echo htmlspecialchars($website['description']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($website['url']); ?>" target="_blank" style="color: red;">
                                    <?php echo htmlspecialchars($website['url']); ?>
                                </a>
                            </td>
                            <td class="action-buttons">
                                <button class="btn" data-website='<?php echo str_replace("'", "&#39;", json_encode($website, JSON_UNESCAPED_UNICODE)); ?>' onclick="editWebsite(this)">编辑</button>
                                <button class="btn delete" onclick="deleteWebsite(<?php echo (int)$website['id']; ?>)" style="background-color: #95a5a6;">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">暂无数据</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $currentPage ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <div class="overlay" id="overlay"></div>

        <div id="websiteForm">
            <h2 id="formTitle">添加网站</h2>
            <form id="addEditForm" onsubmit="return saveWebsite(event)">
                <input type="hidden" id="websiteId" name="id">
                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">描述</label>
                    <textarea id="description" name="description" required rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="url">URL</label>
                    <input type="url" id="url" name="url" required>
                </div>
                <div class="form-group">
                    <label for="logo">Logo</label>
                    <div class="logo-input-group">
                        <select id="logoType" onchange="toggleLogoInput()" class="form-control">
                            <option value="file">本地上传</option>
                            <option value="url">远程图片</option>
                        </select>
                        <div id="fileUpload">
                            <input type="file" id="logo" name="logo" accept="image/*" onchange="previewImage(event)">
                        </div>
                        <div id="urlInput" style="display: none;">
                            <input type="url" id="logoUrl" placeholder="输入图片URL" onchange="previewRemoteImage(this.value)">
                        </div>
                        <div class="preview-container">
                            <img id="imagePreview" src="../images/1.png" alt="预览" style="display: block;">
                            <div class="loading">上传中...</div>
                        </div>
                        <input type="hidden" id="logoPath" name="logo_path">
                    </div>
                </div>
                <div class="form-group">
                    <label for="sortOrder">排序</label>
                    <input type="number" id="sortOrder" name="sort_order" value="0" min="0">
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn" id="submitBtn">保存</button>
                    <button type="button" class="btn" onclick="hideForm()" style="background-color: #95a5a6;">取消</button>
                </div>
            </form>
        </div>

        <script>
            let isSubmitting = false;
            let isLoading = false;

            function showAddForm() {
                resetForm();
                document.getElementById('formTitle').textContent = '添加网站';
                showModal();
            }

            function resetForm() {
                const form = document.getElementById('addEditForm');
                form.reset();
                document.getElementById('websiteId').value = '';
                document.getElementById('logoPath').value = '';
                document.getElementById('imagePreview').src = '../images/1.png';
                document.getElementById('logoType').value = 'file';
                toggleLogoInput();
            }

            function showModal() {
                document.getElementById('websiteForm').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
            }

            function hideModal() {
                document.getElementById('websiteForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
            }

            function editWebsite(button) {
                try {
                    // 从按钮的data-website属性获取数据
                    const websiteData = JSON.parse(button.getAttribute('data-website'));

                    // 设置表单值
                    document.getElementById('websiteId').value = websiteData.id || '';
                    document.getElementById('title').value = websiteData.title || '';
                    document.getElementById('description').value = websiteData.description || '';
                    document.getElementById('url').value = websiteData.url || '';
                    document.getElementById('logoPath').value = websiteData.logo_path || '';
                    document.getElementById('sortOrder').value = websiteData.sort_order || '0';

                    // 处理logo显示
                    const preview = document.getElementById('imagePreview');
                    if (websiteData.logo_path && websiteData.logo_path.startsWith('http')) {
                        preview.src = websiteData.logo_path;
                    } else {
                        preview.src = websiteData.logo_path || '../images/default-logo.png';
                    }
                    preview.style.display = 'block';

                    // 设置logo类型
                    const logoType = websiteData.logo_path && websiteData.logo_path.startsWith('http') ? 'url' : 'file';
                    document.getElementById('logoType').value = logoType;
                    
                    // 如果是URL类型，设置URL输入框的值
                    document.getElementById('logoUrl').value = logoType === 'url' ? (websiteData.logo_path || '') : '';
                    
                    toggleLogoInput();
                    
                    // 更新标题并显示模态框
                    document.getElementById('formTitle').textContent = '编辑网站';
                    showModal();

                } catch (error) {
                    console.error('编辑网站时发生错误:', error);
                    alert('编辑网站时发生错误，请刷新页面后重试');
                }
            }

            function toggleLogoInput() {
                const logoType = document.getElementById('logoType').value;
                document.getElementById('fileUpload').style.display = logoType === 'file' ? 'block' : 'none';
                document.getElementById('urlInput').style.display = logoType === 'url' ? 'block' : 'none';
            }

            function previewRemoteImage(url) {
                if (!url) return;
                
                const preview = document.getElementById('imagePreview');
                const loading = document.querySelector('.loading');
                
                loading.style.display = 'block';
                preview.style.display = 'none';
                
                const img = new Image();
                img.onload = function() {
                    preview.src = url;
                    preview.style.display = 'block';
                    document.getElementById('logoPath').value = url;
                    loading.style.display = 'none';
                };
                
                img.onerror = function() {
                    alert('无法加载该图片，请检查URL是否正确或尝试其他图片');
                    loading.style.display = 'none';
                    preview.src = '../images/default-logo.png';
                    preview.style.display = 'block';
                    document.getElementById('logoPath').value = '';
                };
                
                img.src = url;
            }

            function hideForm() {
                document.getElementById('websiteForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('addEditForm').reset();
                isSubmitting = false;
                document.getElementById('logoType').value = 'file';
                toggleLogoInput();
            }

            function saveWebsite(event) {
                event.preventDefault();
                
                if (isSubmitting || isLoading) return false;
                isSubmitting = true;
                
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                
                const form = event.target;
                const formData = new FormData(form);
                const data = {};
                formData.forEach((value, key) => {
                    if (value) data[key] = value;
                });

                const id = data.id;
                delete data.id;
                delete data.logo;

                // 如果是远程图片，使用输入的URL
                if (document.getElementById('logoType').value === 'url') {
                    const logoUrl = document.getElementById('logoUrl').value;
                    if (logoUrl) {
                        data.logo_path = logoUrl;
                    }
                }

                fetch(`api.php?action=${id ? 'update' : 'add'}&id=${id || ''}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        window.location.href = window.location.pathname + (window.location.search || '?page=1');
                    } else {
                        alert(result.error || '保存失败');
                        submitBtn.disabled = false;
                        isSubmitting = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('保存失败');
                    submitBtn.disabled = false;
                    isSubmitting = false;
                });

                return false;
            }

            function deleteWebsite(id) {
                if (isLoading) return;
                if (!confirm('确定要删除这个网站吗？')) return;
                
                isLoading = true;
                fetch(`api.php?action=delete&id=${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(result => {
                    isLoading = false;
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.error || '删除失败');
                    }
                })
                .catch(error => {
                    isLoading = false;
                    console.error('Error:', error);
                    alert('删除失败');
                });
            }

            // 图片加载错误时使用默认图片
            document.querySelectorAll('.logo-preview').forEach(img => {
                img.onerror = function() {
                    this.src = '../images/default-logo.png';
                };
            });
        </script>
    </div>
</body>
</html>
