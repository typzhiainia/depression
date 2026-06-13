<?php
/**
 * 管理员 API 接口
 */
require_once '../config.php';

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// 统一响应格式
function admin_response($success, $message = '', $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 写入管理员操作日志（自动建表兜底）
 */
function write_admin_log($adminId, $username, $action, $success, $detail = '') {
    try {
        $db = db();
        // 自动创建日志表（如果不存在）
        $db->exec("CREATE TABLE IF NOT EXISTS admin_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_id INTEGER NOT NULL DEFAULT 0,
            username TEXT NOT NULL DEFAULT '',
            action TEXT NOT NULL DEFAULT '',
            success INTEGER NOT NULL DEFAULT 1,
            detail TEXT DEFAULT '',
            ip_address TEXT DEFAULT '',
            user_agent TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        // 自动创建索引
        $db->exec("CREATE INDEX IF NOT EXISTS idx_log_action ON admin_logs(action)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_log_admin ON admin_logs(admin_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_log_time ON admin_logs(created_at)");

        $stmt = $db->prepare("INSERT INTO admin_logs 
            (admin_id, username, action, success, detail, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $adminId, $username, $action, $success ? 1 : 0, 
            $detail, get_real_ip(), $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("写入管理员日志失败: " . $e->getMessage());
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        // 管理员登录
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            admin_response(false, '请输入用户名和密码');
        }

        $stmt = db()->prepare("SELECT id, username, password_hash, display_name, is_active FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            write_admin_log(0, $username, 'login', false, '登录失败：用户名或密码错误');
            admin_response(false, '用户名或密码错误');
        }

        if (!$admin['is_active']) {
            write_admin_log($admin['id'], $username, 'login', false, '账户已禁用');
            admin_response(false, '该账户已被禁用，请联系超级管理员');
        }

        // 更新最后登录时间
        db()->prepare("UPDATE admins SET last_login_at = datetime('now', 'localtime') WHERE id = ?")->execute([$admin['id']]);

        $_SESSION[ADMIN_SESSION_KEY] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_display_name'] = $admin['display_name'];

        write_admin_log($admin['id'], $username, 'login', true, '登录成功');
        admin_response(true, '登录成功', [
            'redirect' => 'index.php'
        ]);
        break;

    case 'logout':
        $adminId = $_SESSION['admin_id'] ?? 0;
        $adminName = $_SESSION['admin_username'] ?? '';
        
        admin_logout();
        write_admin_log($adminId, $adminName, 'logout', true, '退出登录');
        admin_response(true, '已退出登录', ['redirect' => 'login.php']);
        break;

    case 'change_password':
        // 密码修改功能
        session_start(); // 确保 session 可用
        
        if (!isset($_SESSION['admin_id']) || !$_SESSION['admin_id']) {
            admin_response(false, '请先登录');
        }
        
        $currentPwd = $_POST['current_password'] ?? '';
        $newPwd = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';
        $adminId = (int)$_SESSION['admin_id'];
        $adminName = $_SESSION['admin_username'] ?? '';
        
        // 1. 非空校验
        if ($currentPwd === '' || $newPwd === '' || $confirmPwd === '') {
            admin_response(false, '所有字段都不能为空');
        }
        
        // 2. 一致性检查
        if ($newPwd !== $confirmPwd) {
            write_admin_log($adminId, $adminName, 'change_password', false, '两次输入的新密码不一致');
            admin_response(false, '两次输入的新密码不一致');
        }
        
        // 3. 复杂度检查
        $errors = [];
        if (strlen($newPwd) < 8) {
            $errors[] = '密码长度不能少于8位';
        }
        if (!preg_match('/[a-z]/', $newPwd)) {
            $errors[] = '需包含至少一个小写字母';
        }
        if (!preg_match('/[A-Z]/', $newPwd)) {
            $errors[] = '需包含至少一个大写字母';
        }
        if (!preg_match('/[0-9]/', $newPwd)) {
            $errors[] = '需包含至少一个数字';
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $newPwd)) {
            $errors[] = '需包含至少一个特殊字符';
        }
        
        // 弱口令黑名单
        $blacklist = ['password','123456','qwerty','abc123','admin111','admin123','11111111','00000000'];
        if (in_array(strtolower($newPwd), $blacklist)) {
            $errors[] = '此密码过于简单，已被列入弱口令黑名单';
        }
        if ($newPwd === $currentPwd) {
            $errors[] = '新密码不能与当前密码相同';
        }
        
        if (!empty($errors)) {
            write_admin_log($adminId, $adminName, 'change_password', false, '密码复杂度不符合要求: '.implode('; ', $errors));
            admin_response(false, implode('<br>', $errors), ['field_errors' => true]);
        }
        
        // 4. 验证当前密码
        $stmt = db()->prepare("SELECT password_hash FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $row = $stmt->fetch();
        
        if (!$row || !password_verify($currentPwd, $row['password_hash'])) {
            write_admin_log($adminId, $adminName, 'change_password', false, '当前密码验证失败');
            admin_response(false, '当前密码不正确', ['field' => 'current_password']);
        }
        
        // 5. 更新密码
        $newHash = password_hash($newPwd, PASSWORD_DEFAULT, ['cost' => 12]);
        $stmt = db()->prepare("UPDATE admins SET password_hash = ?, updated_at = datetime('now', 'localtime') WHERE id = ?");
        $stmt->execute([$newHash, $adminId]);
        
        write_admin_log($adminId, $adminName, 'change_password', true, '密码修改成功');
        admin_response(true, '密码修改成功！下次登录请使用新密码');
        break;

    case 'get_stats':
        // 获取统计数据
        require_admin_login();
        
        try {
            $db = db();
            
            // 今日测评数量
            $today = date('Y-m-d');
            $stmt = $db->prepare("SELECT COUNT(*) as c FROM test_records WHERE date(created_at) = ?");
            $stmt->execute([$today]);
            $todayCount = $stmt->fetch()['c'];
            
            // 总测评数量
            $totalStmt = $db->query("SELECT COUNT(*) as c FROM test_records");
            $totalCount = $totalStmt->fetch()['c'];
            
            // 最近7天趋势
            $trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $s = $db->prepare("SELECT COUNT(*) as c, AVG(total_score) as avg FROM test_records WHERE date(created_at) = ?");
                $s->execute([$d]);
                $r = $s->fetch();
                $trend[] = ['date' => $d, 'count' => (int)$r['c'], 'avg' => round($r['avg'] ?? 0, 1)];
            }
            
            // 按类型分布
            $typeStmt = $db->query("SELECT assessment_type, COUNT(*) as c FROM test_records GROUP BY assessment_type ORDER BY c DESC");
            $byType = $typeStmt->fetchAll();
            
            // 按等级分布
            $levelStmt = $db->query("SELECT severity_level, COUNT(*) as c FROM test_records GROUP BY severity_level ORDER BY c DESC LIMIT 10");
            $byLevel = $levelStmt->fetchAll();
            
            // 最近记录
            $recentStmt = $db->query("
                SELECT id, total_score, severity_level, assessment_type, test_duration, created_at 
                FROM test_records 
                ORDER BY created_at DESC 
                LIMIT 20
            ");
            $recentRecords = $recentStmt->fetchAll();
            
            admin_response(true, '获取统计成功', [
                'today_count' => (int)$todayCount,
                'total_count' => (int)$totalCount,
                'trend' => $trend,
                'by_type' => $byType,
                'by_level' => $byLevel,
                'recent_records' => $recentRecords
            ]);
            
        } catch (PDOException $e) {
            error_log("获取统计数据失败: " . $e->getMessage());
            admin_response(false, '获取数据时出错: ' . $e->getMessage());
        }
        break;

    case 'get_records':
        // 获取测评记录列表
        require_admin_login();
        
        $page = max(1, intval($_POST['page'] ?? 1));
        $pageSize = min(100, max(10, intval($_POST['page_size'] ?? 20)));
        $offset = ($page - 1) * $pageSize;
        $assessmentType = $_POST['assessment_type'] ?? '';
        $search = trim($_POST['search'] ?? '');
        
        try {
            $db = db();
            $where = "WHERE 1=1";
            $params = [];
            
            if (!empty($assessmentType)) {
                $where .= " AND assessment_type = ?";
                $params[] = $assessmentType;
            }
            if (!empty($search)) {
                $where .= " AND (severity_level LIKE ? OR user_ip LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $countSql = "SELECT COUNT(*) as total FROM test_records {$where}";
            $countStmt = $db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            $sql = "SELECT id, session_id, user_ip, total_score, severity_level, severity_description, 
                           recommendation, assessment_type, test_duration, created_at 
                    FROM test_records 
                    {$where}
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            $params[] = $pageSize;
            $params[] = $offset;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll();
            
            foreach ($records as &$r) {
                $r['recommendation_arr'] = json_decode($r['recommendation'], true) ?: [];
            }
            unset($r);
            
            admin_response(true, '', [
                'records' => $records,
                'total' => (int)$total,
                'page' => $page,
                'page_size' => $pageSize,
                'total_pages' => ceil($total / $pageSize)
            ]);
            
        } catch (PDOException $e) {
            admin_response(false, '获取记录失败: ' . $e->getMessage());
        }
        break;

    case 'export_csv':
        // 导出CSV
        require_admin_login();
        
        try {
            $db = db();
            $stmt = $db->query("
                SELECT id, user_ip, total_score, severity_level, severity_description, 
                       assessment_type, test_duration, created_at 
                FROM test_records 
                ORDER BY created_at DESC
            ");
            $records = $stmt->fetchAll();
            
            if (empty($records)) {
                admin_response(false, '没有可导出的数据');
            }
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="assessment_records_' . date('Ymd_His') . '.csv"');
            
            echo "\xEF\xBB\xBF"; // BOM for Excel UTF-8
            echo "ID,IP地址,得分,严重程度,描述,测评类型,用时(秒),提交时间\n";
            
            foreach ($records as $r) {
                echo '"' . $r['id'] . '",';
                echo '"' . $r['user_ip'] . '",';
                echo '"' . $r['total_score'] . '",';
                echo '"' . $r['severity_level'] . '",';
                echo '"' . str_replace('"', '""', $r['severity_description']) . '",';
                echo '"' . $r['assessment_type'] . '",';
                echo '"' . $r['test_duration'] . '",';
                echo '"' . $r['created_at'] . "\"\n";
            }
            exit;
            
        } catch (PDOException $e) {
            admin_response(false, '导出失败: ' . $e->getMessage());
        }
        break;

    case 'delete_record':
        // 删除单条记录
        require_admin_login();
        
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            admin_response(false, '无效的记录ID');
        }
        
        try {
            $stmt = db()->prepare("DELETE FROM test_records WHERE id = ?");
            $stmt->execute([$id]);
            
            write_admin_log(
                $_SESSION['admin_id'], 
                $_SESSION['admin_username'], 
                'delete_record', 
                $stmt->rowCount() > 0, 
                "删除记录 ID={$id}"
            );
            
            admin_response(true, '记录已删除');
        } catch (PDOException $e) {
            admin_response(false, '删除失败: ' . $e->getMessage());
        }
        break;

    case 'get_record_detail':
        // 获取记录详情
        require_admin_login();
        
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            admin_response(false, '无效的记录ID');
        }
        
        try {
            $stmt = db()->prepare("SELECT * FROM test_records WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch();
            
            if (!$record) {
                admin_response(false, '记录不存在');
            }
            
            $record['answers'] = json_decode($record['answers'], true) ?: [];
            $record['recommendation'] = json_decode($record['recommendation'], true) ?: [];
            
            admin_response(true, '', ['record' => $record]);
            
        } catch (PDOException $e) {
            admin_response(false, '查询失败: ' . $e->getMessage());
        }
        break;

default:
        admin_response(false, '未知操作');
}
?>
