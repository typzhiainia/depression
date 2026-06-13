<?php
require_once 'config.php';
require_once 'assessments.php';

$records = AssessmentManager::getHistory(20);

function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '';
    $diff = time() - $timestamp;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    if ($diff < 2592000) return floor($diff / 86400) . '天前';
    if ($diff < 31536000) return floor($diff / 2592000) . '个月前';
    return floor($diff / 31536000) . '年前';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>测评历史 - 心理健康测评中心</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="wrap">
            <a href="index.php" class="nav-brand"><span class="logo-icon">M</span><span>心理测评</span></a>
            <div class="nav-links">
                <a href="index.php">首页</a>
                <a href="test.php">测评</a>
                <a href="community.php">社区</a>
                <a href="history.php" class="active">记录</a>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()"><span></span><span></span><span></span></button>
        </div>
    </nav>

    <main class="wrap main-content">
        <div class="page-header">
            <h1>测评历史记录</h1>
            <p>查看你过往的测评结果，追踪心理状态的变化趋势</p>
        </div>

        <?php if (empty($records)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>暂无测评记录</h3>
            <p>完成一次测评后，你的结果将显示在这里</p>
            <a href="test.php" class="btn btn-primary">开始首次测评</a>
        </div>
        <?php else: ?>

        <!-- 趋势图 -->
        <?php 
        $trendData = AssessmentManager::getTrendData();
        if (count($trendData) > 1): 
        ?>
        <section class="trend-section">
            <h2 style="font-size:18px;margin-bottom:16px;">📈 分数趋势</h2>
            <div class="chart-container">
                <canvas id="trendChart"></canvas>
            </div>
        </section>
        <?php endif; ?>

        <!-- 记录列表 -->
        <section class="history-list">
            <?php foreach ($records as $record): 
                $date = date('Y年m月d日 H:i', strtotime($record['created_at']));
                $timeAgo = getTimeAgo($record['created_at']);
                $recs = json_decode($record['recommendation'], true) ?: [];
            ?>
            <div class="history-card">
                <div class="history-left">
                    <div class="history-score"><?php echo $record['total_score']; ?></div>
                    <div class="history-type"><?php echo htmlspecialchars($record['assessment_type']); ?></div>
                </div>
                <div class="history-body">
                    <div class="history-top">
                        <strong><?php echo htmlspecialchars($record['severity_level']); ?></strong>
                        <span class="history-time" title="<?php echo $date; ?>"><?php echo $timeAgo; ?></span>
                    </div>
                    <p class="history-desc"><?php echo htmlspecialchars(mb_substr($record['severity_description'], 0, 80)); ?>...</p>
                    <?php if (!empty($recs)): ?>
                    <ul class="history-recs">
                        <?php foreach (array_slice($recs, 0, 2) as $rec): ?>
                        <li><?php echo htmlspecialchars($rec); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </main>

    <script src="js/main.js"></script>
    <?php if (!empty($trendData) && count($trendData) > 1): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        const trendData = <?php echo json_encode($trendData); ?>;
        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.created_at.substring(5, 10)),
                datasets: [{
                    label: '总分',
                    data: trendData.map(d => d.total_score),
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#6366F1',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display:false } },
                scales: { y: { beginAtZero:true } }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
