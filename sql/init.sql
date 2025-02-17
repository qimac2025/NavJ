-- 创建网站数据表
CREATE TABLE IF NOT EXISTS `websites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '网站标题',
  `description` varchar(255) NOT NULL COMMENT '网站描述',
  `url` varchar(255) NOT NULL COMMENT '网站地址',
  `logo_path` varchar(255) NOT NULL COMMENT 'logo路径',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='网站导航表';

-- 插入示例数据
INSERT INTO `websites` (`title`, `description`, `url`, `logo_path`, `sort_order`) VALUES
('拣金吧', '拣金吧，挑选最有价值的部分', 'https://jianjinba.com', './images/1.png', 1);

-- 创建管理员表
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入默认管理员账号 默认密码：admin
INSERT INTO `admin` (`username`, `password`) VALUES
('admin', '$2y$10$92dXpbgNu.V69HuGU2HXEOXg5tZL.yN8NGtZAOHOXvBAK.Ac66YTi');
