<?php
class WebsiteManager {
    private $db;
    private $table = 'websites';
    private $itemsPerPage = 10;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        if (!$this->db) {
            throw new Exception("无法获取数据库连接");
        }
    }

    public function getAllWebsites() {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC";
            $result = $this->db->query($sql);
            
            if (!$result) {
                throw new Exception("获取网站列表失败: " . $this->db->error);
            }
            
            $websites = [];
            while ($row = $result->fetch_assoc()) {
                $websites[] = $row;
            }
            
            return $websites;
            
        } catch (Exception $e) {
            error_log("获取所有网站列表失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function getWebsites($page = 1) {
        $offset = ($page - 1) * $this->itemsPerPage;
        $sql = "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id ASC LIMIT ? OFFSET ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $this->db->error);
            }
            
            $stmt->bind_param('ii', $this->itemsPerPage, $offset);
            if (!$stmt->execute()) {
                throw new Exception("SQL执行失败: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $websites = [];
            
            while ($row = $result->fetch_assoc()) {
                $websites[] = $row;
            }
            
            $stmt->close();
            return $websites;
            
        } catch (Exception $e) {
            error_log("获取网站列表失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalPages() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->db->query($sql);
            
            if (!$result) {
                throw new Exception("获取总页数失败: " . $this->db->error);
            }
            
            $row = $result->fetch_assoc();
            return ceil($row['total'] / $this->itemsPerPage);
            
        } catch (Exception $e) {
            error_log("获取总页数失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function addWebsite($title, $description, $url, $logoPath, $sortOrder) {
        $sql = "INSERT INTO {$this->table} (title, description, url, logo_path, sort_order) VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $this->db->error);
            }
            
            $stmt->bind_param('ssssi', $title, $description, $url, $logoPath, $sortOrder);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("添加网站失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateWebsite($id, $data) {
        $updates = [];
        $types = '';
        $values = [];
        
        if (isset($data['title'])) {
            $updates[] = 'title = ?';
            $types .= 's';
            $values[] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $updates[] = 'description = ?';
            $types .= 's';
            $values[] = $data['description'];
        }
        
        if (isset($data['url'])) {
            $updates[] = 'url = ?';
            $types .= 's';
            $values[] = $data['url'];
        }
        
        if (isset($data['logo_path'])) {
            $updates[] = 'logo_path = ?';
            $types .= 's';
            $values[] = $data['logo_path'];
        }
        
        if (isset($data['sort_order'])) {
            $updates[] = 'sort_order = ?';
            $types .= 'i';
            $values[] = $data['sort_order'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $types .= 'i';
        $values[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $this->db->error);
            }
            
            $stmt->bind_param($types, ...$values);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("更新网站失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteWebsite($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $id);
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
            
        } catch (Exception $e) {
            error_log("删除网站失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function getWebsite($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("SQL准备失败: " . $this->db->error);
            }
            
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $website = $result->fetch_assoc();
            
            $stmt->close();
            return $website;
            
        } catch (Exception $e) {
            error_log("获取网站信息失败: " . $e->getMessage());
            throw $e;
        }
    }
}
