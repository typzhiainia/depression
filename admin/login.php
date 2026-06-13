<?php
require_once '../config.php';

// 如果已登录，直接跳转到管理首页
if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username === '' || $password === '') {
        $error = '请输入用户名和密码';
    } else {
        $stmt = db()->prepare("SELECT id, username, password_hash, display_name, is_active FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            $error = '用户名或密码错误';
        } elseif (!password_verify($password, $admin['password_hash'])) {
            $error = '用户名或密码错误';
        } elseif (!$admin['is_active']) {
            $error = '该账户已被禁用';
        } else {
            // 登录成功
            db()->prepare("UPDATE admins SET last_login_at = datetime('now', 'localtime') WHERE id = ?")->execute([$admin['id']]);
            
            $_SESSION[ADMIN_SESSION_KEY] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_display_name'] = $admin['display_name'];
            
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台登录 - 心理健康测评中心</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #1e3a5f 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { width: 420px; max-width: 90vw; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; box-shadow: 0 25px 80px rgba(0,0,0,0.4); overflow: hidden; animation: slideUp 0.5s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .login-header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 40px 30px; text-align: center; color: white; position: relative; overflow: hidden; }
        .login-header::before { content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); animation:pulse 8s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{transform:scale(1);} 50%{transform:scale(1.1);} }
        .login-logo { width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px; font-weight: 700; backdrop-filter: blur(10px); }
        .login-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.5px; position: relative; z-index: 1; }
        .login-header p { font-size: 13px; opacity: 0.85; margin-top: 6px; position: relative; z-index: 1; }
        .login-body { padding: 36px 32px; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px; }
        .form-input { width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 15px; transition: all 0.25s; outline: none; font-family: inherit; }
        .form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.1); }
        .btn-login { width: 100%; padding: 15px; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.25s; letter-spacing: 0.3px; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(79,70,229,0.35); }
        .btn-login:active { transform: translateY(0); }
        .error-box { background: #fef2f2; color: #dc2626; padding: 14px 18px; border-radius: 12px; margin-bottom: 24px; font-size: 14px; border-left: 4px solid #ef4444; animation: shake 0.4s ease-in-out; }
        @keyframes shake { 0%,100%{transform:translateX(0);} 20%{transform:translateX(-8px);} 40%{transform:translateX(8px);} 60%{transform:translateX(-6px);} 80%{transform:translateX(6px);} }
        .footer-link { text-align: center; margin-top: 28px; }
        .footer-link a { color: #9ca3af; text-decoration: none; font-size: 13px; transition: color 0.2s; }
        .footer-link a:hover { color: #6366f1; }
        input[type="checkbox"] { accent-color: #6366f1; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">M</div>
            <h1>管理后台</h1>
            <p>MindCheck Admin Panel</p>
        </div>
        <div class="login-body">
            <?php if ($error): ?>
                <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" class="form-input" placeholder="请输入管理员用户名" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autocomplete="username" autofocus>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="请输入密码" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-login">登 录</button>
            </form>
            <div class="footer-link">
                <a href="../index.php">← 返回网站首页</a>
            </div>
        </div>
    </div>

    <script>
        // Enter 键提交
        document.querySelector('.form-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') e.target.closest('form').submit();
        });
    </script>
</body>
</html>
