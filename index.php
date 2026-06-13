<?php
require_once 'config.php';
require_once 'assessments.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>心理健康测评中心</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="wrap">
        <a href="index.php" class="nav-brand">
            <mark>M</mark>
            <span>心理健康测评</span>
        </a>
        <div class="nav-links">
            <a href="index.php" class="active">首页</a>
            <a href="#assessments">测评</a>
            <a href="history.php">记录</a>
        </div>
        <button class="mobile-menu-btn" aria-label="菜单" onclick="toggleMobileMenu()">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="wrap">
        <div class="hero-inner">
            <div class="hero-tag">免费 · 匿名 · 即时出结果</div>
            <h1>花几分钟，<br>了解一下自己的状态</h1>
            <p>基于国际通用的心理量表，帮你快速了解情绪、压力等维度。结果仅供参考，不构成医疗诊断。</p>
            <div class="hero-actions">
                <a href="#assessments" class="btn btn-primary btn-lg">选择量表开始</a>
                <a href="#about" class="btn btn-outline btn-lg">了解更多</a>
            </div>
            <div class="hero-meta">
                <div><strong id="statTotal">--</strong><span>累计测评</span></div>
                <div><strong id="statToday">--</strong><span>今日完成</span></div>
                <div><strong><?php echo count(AssessmentManager::getAllAssessments()); ?></strong><span>可用量表</span></div>
            </div>
        </div>
    </div>
</section>

<!-- Assessments -->
<section class="section assessments-section" id="assessments">
    <div class="wrap">
        <div class="section-header">
            <h2>选择一个量表</h2>
            <p>以下均为国际通用的标准化测量工具</p>
        </div>
        <div class="assessment-grid">
            <?php foreach (AssessmentManager::getAllAssessments() as $key => $assessment): ?>
            <a href="test.php?type=<?php echo $key; ?>" class="assessment-card">
                <div class="ac-top">
                    <div class="ac-icon"><?php echo htmlspecialchars($assessment['icon']); ?></div>
                    <div class="ac-badge"><?php echo htmlspecialchars($assessment['category']); ?></div>
                </div>
                <h3 class="ac-name"><?php echo htmlspecialchars($assessment['full_name']); ?></h3>
                <p class="ac-desc"><?php echo htmlspecialchars($assessment['description']); ?></p>
                <div class="ac-footer">
                    <span><?php echo $assessment['questions_count']; ?> 题</span>
                    <span><?php echo $assessment['duration']; ?></span>
                    <span>满分 <?php echo $assessment['max_score']; ?> 分</span>
                </div>
                <div class="ac-arrow">&rarr;</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- About -->
<section class="section about-section" id="about">
    <div class="wrap">
        <div class="about-grid">
            <div class="about-text">
                <h2 class="section-header h2">关于这个工具</h2>
                <p style="margin-bottom:14px;font-size:14px;color:var(--sub);line-height:1.7;">这是一个基于国际通用量表构建的自我评估平台。我们提供经过科学验证的心理学量表，帮助用户了解自身心理状态的参考信息。</p>
                <ul class="about-list">
                    <li>量表来自国际公认的心理研究文献</li>
                    <li>完全匿名，不收集个人身份信息</li>
                    <li>结果仅供自我参考，不能替代专业诊断</li>
                    <li>如发现严重症状，请及时寻求专业帮助</li>
                </ul>
            </div>
            <div class="about-features">
                <div class="feature-item">
                    <div class="fi-icon">🔒</div>
                    <div><strong>数据本地存储</strong><p>不传云端，浏览器关闭即清除</p></div>
                </div>
                <div class="feature-item">
                    <div class="fi-icon">📋</div>
                    <div><strong>标准量表</strong><p>PHQ-9 / GAD-7 / PSS-10 等</p></div>
                </div>
                <div class="feature-item">
                    <div class="fi-icon">⚡</div>
                    <div><strong>即时反馈</strong><p>提交后立刻看到分析报告</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="wrap">
        <div class="footer-grid">
            <div class="footer-brand">
                心理健康测评
                <p>专业的心理健康自我评估工具</p>
            </div>
            <div>
                <strong>导航</strong>
                <a href="index.php">首页</a>
                <a href="#assessments">测评量表</a>
                <a href="history.php">历史记录</a>
            </div>
            <div>
                <strong>求助热线</strong>
                <a href="tel:4001619995">400-161-9995 全国心理援助</a>
                <a href="tel:12320">12320 公共卫生热线</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> 内容仅供参考，不构成任何医疗建议。
        </div>
    </div>
</footer>

<script src="js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('api/public.php?action=get_stats')
        .then(r => r.json())
        .then(json => {
            if(json.success && json.data) {
                var total = json.data.by_type ? json.data.by_type.reduce(function(s, t){ return s + t.c; }, 0) : 0;
                document.getElementById('statTotal').textContent = total;
                document.getElementById('statToday').textContent = json.data.today_count;
            }
        })
        .catch(function(){});
});
</script>
</body>
</html>
