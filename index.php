<?php
require_once __DIR__ . '/includes/Database.php';
require_once __DIR__ . '/includes/WebsiteManager.php';

// 实例化网站管理器
$websiteManager = new WebsiteManager();

// 获取所有网站
try {
    $websites = $websiteManager->getAllWebsites();
    if (empty($websites)) {
        error_log("未找到网站信息", 0);
        $websites = [[
            'title' => '拣金吧',
            'description' => '挑选最有价值的部分',
            'url' => 'https://jianjinba.com',
            'logo_path' => './images/1.png'
        ]];
    }
} catch (Exception $e) {
    error_log("获取网站列表失败: " . $e->getMessage(), 0);
    $websites = [[
        'title' => '拣金吧',
        'description' => '挑选最有价值的部分',
        'url' => 'https://jianjinba.com',
        'logo_path' => './images/1.png'
    ]];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拣金吧</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 内联Font Awesome样式，移除CDN依赖 */
        .fas {
            display: inline-block;
            width: 1em;
            height: 1em;
            background-size: contain;
            background-repeat: no-repeat;
            vertical-align: middle;
        }
        .fa-moon {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M283.211 512c78.962 0 151.079-35.925 198.857-94.792 7.068-8.708-.639-21.43-11.562-19.35-124.203 23.654-238.262-71.576-238.262-196.954 0-72.222 38.662-138.635 101.498-174.394 9.686-5.512 7.25-20.197-3.756-22.23A258.156 258.156 0 0 0 283.211 0c-141.309 0-256 114.511-256 256 0 141.309 114.511 256 256 256z"/></svg>');
        }
        .fa-arrow-right {
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M190.5 66.9l22.2-22.2c9.4-9.4 24.6-9.4 33.9 0L441 239c9.4 9.4 9.4 24.6 0 33.9L246.6 467.3c-9.4 9.4-24.6 9.4-33.9 0l-22.2-22.2c-9.5-9.5-9.3-25 .4-34.3L311.4 296H24c-13.3 0-24-10.7-24-24v-32c0-13.3 10.7-24 24-24h287.4L190.9 101.2c-9.8-9.3-10-24.8-.4-34.3z"/></svg>');
        }
    </style>
</head>
<body>
    <div class="theme-toggle" title="切换主题">
        <i class="fas fa-moon"></i>
    </div>

    <div class="container">
        <header class="header">
            <h1>拣金吧</h1>
            <style>
                .header h1::selection {
                    background: var(--button-gradient-start);
                    -webkit-text-fill-color: #ffffff;
                }
            </style>
            <p>挑选最有价值的部分</p>
        </header>

        <div class="sites-grid">
            <?php foreach ($websites as $website): ?>
            <div class="site-card">
                <div class="content">
                    <img src="<?php echo htmlspecialchars($website['logo_path']); ?>" alt="<?php echo htmlspecialchars($website['title']); ?>" class="logo">
                    <h3><?php echo htmlspecialchars($website['title']); ?></h3>
                    <p><?php echo htmlspecialchars($website['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($website['url']); ?>" target="_blank">
                        <i class="fas fa-arrow-right"></i> 立即访问
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 错误提示 -->
        <div class="error-message"></div>

        <!-- 加载提示 -->
        <div class="loading"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const root = document.documentElement;
            const themeToggle = document.querySelector('.theme-toggle');
            const themeIcon = document.querySelector('.theme-toggle i');
            
            // 检查系统主题
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
            updateTheme(prefersDark.matches);
            
            // 主题切换事件
            themeToggle.addEventListener('click', () => {
                const isDark = root.getAttribute('data-theme') === 'dark';
                updateTheme(!isDark);
            });
            
            // 更新主题
            function updateTheme(isDark) {
                root.setAttribute('data-theme', isDark ? 'dark' : 'light');
                themeIcon.style.color = isDark ? '#ff4d4d' : '#e60012';
                
                // 保存主题设置
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            }
            
            // 加载保存的主题设置
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                updateTheme(savedTheme === 'dark');
            }
            
            // 错误处理函数
            window.handleError = function(message) {
                const errorMessage = document.querySelector('.error-message');
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';
                console.error('错误:', message);
                
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            };
        });
    </script>
</body>
</html>
