<?php
/**
 * SQLite 数据库安装脚本
 * 运行此文件以创建表结构
 */
require_once 'config.php';

try {
    $db = db();

    // 创建测评记录表
    $sql = "CREATE TABLE IF NOT EXISTS test_records (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        session_id TEXT NOT NULL DEFAULT '',
        user_ip TEXT DEFAULT '',
        total_score INTEGER NOT NULL DEFAULT 0,
        severity_level TEXT DEFAULT '',
        severity_description TEXT DEFAULT '',
        recommendation TEXT DEFAULT '',
        answers TEXT DEFAULT '',
        test_duration INTEGER DEFAULT NULL,
        assessment_type TEXT DEFAULT 'phq9',
        user_agent TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $db->exec($sql);

    // 添加assessment_type列（如果不存在）
    try {
        $db->exec("ALTER TABLE test_records ADD COLUMN assessment_type TEXT DEFAULT 'phq9'");
    } catch (Exception $e) {
        // 列已存在，忽略错误
    }

    // 添加 user_agent 列（如果不存在）
    try {
        $db->exec("ALTER TABLE test_records ADD COLUMN user_agent TEXT DEFAULT ''");
    } catch (Exception $e) {}

    // 创建索引
    $db->exec("CREATE INDEX IF NOT EXISTS idx_session_id ON test_records(session_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_created_at ON test_records(created_at)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_type ON test_records(assessment_type)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_user_ip ON test_records(user_ip)");

    // ==================== 创建管理员表 ====================
    $sql = "CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        display_name TEXT DEFAULT '',
        role TEXT DEFAULT 'admin',
        is_active INTEGER DEFAULT 1,
        last_login_at DATETIME DEFAULT NULL,
        updated_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);

    // 给 admins 表添加 updated_at 字段（如果不存在）
    try { $db->exec("ALTER TABLE admins ADD COLUMN updated_at DATETIME DEFAULT NULL"); } catch (Exception $e) {}

    // ==================== 创建管理员操作日志表 ====================
    $sql = "CREATE TABLE IF NOT EXISTS admin_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        admin_id INTEGER NOT NULL DEFAULT 0,
        username TEXT NOT NULL DEFAULT '',
        action TEXT NOT NULL DEFAULT '',
        success INTEGER NOT NULL DEFAULT 1,
        detail TEXT DEFAULT '',
        ip_address TEXT DEFAULT '',
        user_agent TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    $db->exec("CREATE INDEX IF NOT EXISTS idx_log_action ON admin_logs(action)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_log_admin ON admin_logs(admin_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_log_time ON admin_logs(created_at)");

    // 插入默认管理员账户 (用户名: admin, 密码: admin123)
    $defaultPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
    try {
        $stmt = $db->prepare("SELECT id FROM admins WHERE username = 'admin'");
        $stmt->execute();
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO admins (username, password_hash, display_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', $defaultPasswordHash, '系统管理员', 'super_admin']);
            $adminCreated = true;
        } else {
            $adminCreated = false;
        }
    } catch (Exception $e) {
        $adminCreated = false;
    }

    // 创建PHQ-9问题表
    $sql = "CREATE TABLE IF NOT EXISTS phq9_questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        question_number INTEGER NOT NULL,
        question_text TEXT NOT NULL,
        category TEXT DEFAULT ''
    )";

    $db->exec($sql);

    // 插入默认的PHQ-9问题
    $questions = [
        [1, '做事时提不起劲或没有兴趣', '兴趣减退'],
        [2, '感到心情低落、沮丧或绝望', '情绪低落'],
        [3, '入睡困难、睡不着或睡眠过多', '睡眠问题'],
        [4, '感觉疲倦或没有活力', '精力不足'],
        [5, '食欲不振或吃得太多', '饮食变化'],
        [6, '觉得自己很糟糕，或觉得自己很失败，让自己或家人失望', '自我评价低'],
        [7, '对事物专注有困难，例如阅读报纸或看电视时', '注意力困难'],
        [8, '动作或说话速度缓慢到别人已经察觉？或者相反，烦躁或坐立不安、动来动去', '精神运动性改变'],
        [9, '有不如死掉或用某种方式伤害自己的念头', '自杀意念']
    ];

    // 清空并重新插入
    $db->exec("DELETE FROM phq9_questions");
    
    $stmt = $db->prepare("INSERT INTO phq9_questions (question_number, question_text, category) VALUES (?, ?, ?)");
    
    foreach ($questions as $q) {
        $stmt->execute($q);
    }

    echo "<!DOCTYPE html>
<html lang='zh-CN'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>数据库安装完成</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .container { text-align: center; padding: 40px; background: rgba(255,255,255,0.15); border-radius: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); max-width: 500px; margin: 20px; }
        h1 { font-size: 2em; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        p { opacity: 0.95; line-height: 1.6; margin-bottom: 25px; font-size: 1.05em; }
        .info { background: rgba(255,255,255,0.1); padding: 16px 24px; border-radius: 12px; margin-bottom: 25px; text-align: left; font-size: 0.92em; }
        a { display: inline-block; padding: 14px 36px; background: white; color: #667eea; text-decoration: none; border-radius: 30px; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        a:hover { transform: translateY(-3px); box-shadow: 0 6px 25px rgba(0,0,0,0.3); }
        .check { width: 50px; height: 50px; background: #4CAF50; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='check'>✓</div>
        <h1>安装成功</h1>
        <div class='info'>
            <strong>📦 数据库类型：</strong>SQLite<br>
            <strong>📁 存储位置：</strong>" . htmlspecialchars(DB_FILE) . "<br>
            <strong>📊 表结构：</strong>test_records, phq9_questions, admins, admin_logs<br>
            <strong>⏰ 安装时间：</strong>" . date('Y-m-d H:i:s') .
            (!empty($adminCreated) && $adminCreated
                ? '<br><br><span style=\"color:#4CAF50;font-weight:bold;\">✅ 默认管理员已创建！</span>'
                : '') . "
        </div>
        <p>所有表结构已创建完成，PHQ-9量表题目已初始化</p>";

    if (!empty($adminCreated) && $adminCreated) {
        echo "
        <div style='background:#E8F5E9;border:1px solid #A5D6A7;border-radius:12px;padding:20px;margin:20px 0;text-align:left;'>
            <strong style='display:block;color:#2E7D32;font-size:1.05em;margin-bottom:10px;'>🔐 管理员账户信息（请妥善保管）</strong>
            <table style='width:100%;font-size:0.95em;'>
                <tr><td style='padding:5px 0;color:#555;'><strong>用户名：</strong></td><td style='color:#1565C0;'>admin</td></tr>
                <tr><td style='padding:5px 0;color:#555;'><strong>初始密码：</strong></td><td style='color:#1565C0;'>admin123</td></tr>
                <tr><td style='padding:5px 0;color:#555;'><strong>后台地址：</strong></td><td style='color:#1565C0;'>admin/login.php</td></tr>
            </table>
            <p style='margin-top:12px;color:#E65100;font-size:0.88em;'>⚠️ 首次登录后请立即修改默认密码！</p>
        </div>";
    }

    echo "<a href='index.php'>返回首页开始使用 →</a>
    </div>
</body>
</html>";

} catch (PDOException $e) {
    echo "<!DOCTYPE html>
<html lang='zh-CN'>
<head><meta charset='UTF-8'><title>安装失败</title></head>
<body style='font-family:sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#fee;padding:40px;text-align:center;'>
    <h1 style='color:#c00;'>安装失败</h1>
    <p style='color:#600;font-size:1.1em;'>" . htmlspecialchars($e->getMessage()) . "</p>
</body>
</html>";
}
?>
