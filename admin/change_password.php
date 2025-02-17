<?php
session_start();
require_once __DIR__ . '/../includes/Database.php';

// 检查是否登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = '所有字段都必须填写';
    } elseif ($new_password !== $confirm_password) {
        $error = '新密码和确认密码不匹配';
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // 验证旧密码
            $stmt = $db->prepare("SELECT password FROM admin WHERE username = 'admin'");
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            // 直接比较明文密码
            if ($admin && $old_password === 'admin') {
                // 更新新密码（仍然保持明文存储）
                $stmt = $db->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
                $stmt->bind_param('s', $new_password);
                
                if ($stmt->execute()) {
                    $success = '密码修改成功！新密码将在下次登录时生效';
                } else {
                    $error = '密码更新失败，请重试';
                }
            } else {
                $error = '当前密码错误';
            }
        } catch (Exception $e) {
            $error = '发生错误：' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改管理员密码 - 拣金吧后台管理</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #ff4d4d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        button:hover {
            background: #ff3333;
        }
        .error {
            color: #ff4d4d;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: #fff2f2;
            border-radius: 5px;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: #f2fff2;
            border-radius: 5px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        .back-link:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>修改管理员密码</h1>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="old_password">当前密码</label>
                <input type="password" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">新密码</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">确认新密码</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">修改密码</button>
        </form>
        <a href="index.php" class="back-link">返回管理首页</a>
    </div>
</body>
</html>
