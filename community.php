<?php
require_once 'config.php';
require_once 'assessments.php';

// 获取公开评论/帖子（简化版）
$posts = [];
try {
    $db = db();
    // 这里可以扩展为真实的社区功能，目前使用示例数据
} catch(Exception $e) {}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>心理健康社区 - 心理健康测评中心</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="wrap">
            <a href="index.php" class="nav-brand"><span class="logo-icon">M</span><span>心理测评</span></a>
            <div class="nav-links">
                <a href="index.php">首页</a>
                <a href="test.php">测评</a>
                <a href="community.php" class="active">社区</a>
                <a href="history.php">记录</a>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()"><span></span><span></span><span></span></button>
        </div>
    </nav>

    <main class="wrap main-content">
        <div class="page-header">
            <h1>💬 心理健康社区</h1>
            <p>分享你的感受、经验和成长故事。在这里，你并不孤单。</p>
        </div>

        <section class="community-section">
            <!-- 发帖区域 -->
            <div class="compose-box">
                <textarea placeholder="分享你的想法或经历...（支持匿名）" rows="3" id="postInput"></textarea>
                <div class="compose-actions">
                    <select id="postTag">
                        <option value="general">日常交流</option>
                        <option value="anxiety">焦虑应对</option>
                        <option value="mood">情绪管理</option>
                        <option value="growth">个人成长</option>
                        <option value="support">寻求支持</option>
                    </select>
                    <button class="btn btn-primary btn-sm" onclick="submitPost()">发布</button>
                </div>
            </div>

            <!-- 帖子列表 -->
            <div class="posts-feed" id="postsFeed">
                <div class="community-tip" style="grid-column:1/-1;text-align:center;padding:40px;color:#94a3b8;">
                    <p style="font-size:48px;margin-bottom:12px;">🌱</p>
                    <p>社区功能开发中，敬请期待...</p>
                    <p style="font-size:13px;">你可以先去完成一次测评，了解自己的心理状态</p>
                    <br>
                    <a href="test.php" class="btn btn-outline btn-sm">开始测评 →</a>
                </div>
            </div>
        </section>
    </main>

    <script src="js/main.js"></script>
    <script>
        function submitPost() {
            var text = document.getElementById('postInput').value.trim();
            if (!text) { alert('请输入内容'); return; }
            alert('感谢分享！社区功能正在建设中，您的留言已被记录。');
            document.getElementById('postInput').value = '';
        }

        function timeAgoStr(ts) {
            if (!ts) return '';
            var diff = Date.now() - new Date(ts).getTime();
            var mins = Math.floor(diff / 60000);
            if (mins < 1) return '刚刚';
            if (mins < 60) return mins + '分钟前';
            var hours = Math.floor(mins / 60);
            if (hours < 24) return hours + '小时前';
            var days = Math.floor(hours / 24);
            if (days < 30) return days + '天前';
            return Math.floor(days / 30) + '个月前';
        }
    </script>
</body>
</html>
