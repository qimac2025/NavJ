<?php
class Database {
    private static $instance = null;
    private $connection = null;

    private function __construct() {
        $config = require_once __DIR__ . '/../config/database.php';
        
        try {
            $this->connection = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['dbname']
            );

            if ($this->connection->connect_error) {
                throw new Exception("数据库连接失败: " . $this->connection->connect_error);
            }

            $this->connection->set_charset($config['charset']);
        } catch (Exception $e) {
            error_log("数据库连接错误: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function __clone() {
        throw new Exception("不允许克隆单例对象");
    }
    
    public function __wakeup() {
        throw new Exception("不允许反序列化单例对象");
    }
}
