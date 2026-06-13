<?php
require_once 'config.php';
require_once 'assessments.php';

if (!isset($_SESSION['last_result'])) {
    header('Location: test.php');
    exit;
}

$result = $_SESSION['last_result'] ?? [];
if (!is_array($result) || empty($result)) {
    header('Location: test.php');
    exit;
}
$answers = $_SESSION['last_answers'] ?? [];
$assessmentType = $_SESSION['last_assessment_type'] ?? 'phq9';
$assessmentConfig = AssessmentManager::getAssessment($assessmentType);
$recordId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$levelColor = $result['level_color'] ?? '#6366F1';
$assessmentName = $result['assessment_name'] ?? ($assessmentConfig ? $assessmentConfig['full_name'] : '心理健康测评');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="<?php echo $levelColor; ?>">
    <title>测评结果 - 心理健康测评中心</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="result-page">
    <!-- 导航栏 -->
    <nav class="navbar">
        <div class="wrap" style="display:flex;align-items:center;justify-content:space-between;width:100%;max-width:100%;padding:0 28px;">
            <a href="index.php" class="nav-brand">
                <span class="logo-icon">M</span>
                <span>心理测评</span>
            </a>
            <div class="nav-links">
                <a href="index.php">首页</a>
                <a href="test.php?type=<?php echo $assessmentType; ?>">测评</a>
                <a href="community.php">社区</a>
                <a href="history.php">记录</a>
            </div>
            <button class="mobile-menu-btn" aria-label="菜单" onclick="toggleMobileMenu()">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- AI 生成内容风险提示 -->
    <div class="ai-disclaimer-banner" id="aiDisclaimer">
        <div class="ai-disclaimer-inner">
            <div class="ai-disclaimer-icon" aria-label="AI 生成内容提示">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="ai-disclaimer-content">
                <div class="ai-disclaimer-title">⚠️ AI 生成内容声明</div>
                <p>本页面的测评结果分析、建议及心理健康技巧均由<strong>人工智能（AI）自动生成</strong>。AI 可能存在信息不准确、过时或产生"幻觉"的情况，<strong>不构成任何医学诊断或专业治疗建议</strong>。</p>
                <ul class="ai-disclaimer-list">
                    <li>测评结果仅供参考，不能替代专业医生的诊断</li>
                    <li>建议内容可能存在偏差，请勿作为唯一决策依据</li>
                    <li>涉及健康、医疗等重要决策前，请务必咨询持证专业人士</li>
                </ul>
            </div>
            <button class="ai-dismiss-btn" onclick="dismissAiDisclaimer()" aria-label="关闭提示">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="ai-disclaimer-footer">
            <span class="ai-badge">🤖 AI Generated</span>
            <span>本系统由 AI 驱动，输出内容仅供科普参考</span>
        </div>
    </div>

    <div class="result-container">
        <div class="result-header">
            <?php if (isset($result['has_risk']) && $result['has_risk']): ?>
                <div style="background:linear-gradient(135deg,#FEE2E2,#FECACA);color:#991B1B;padding:16px 24px;border-radius:12px;margin-bottom:24px;display:inline-flex;align-items:center;gap:10px;font-weight:500;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <?php echo htmlspecialchars($result['risk_message'] ?? '检测结果存在风险因素，请关注自身状态'); ?>
                </div>
            <?php endif; ?>

            <!-- 测评类型标签 -->
            <div class="assessment-result-badge" style="background:<?php echo $levelColor; ?>15;color:<?php echo $levelColor; ?>;border:1px solid <?php echo $levelColor; ?>30;margin-bottom:16px;padding:8px 18px;border-radius:20px;display:inline-flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;">
                <?php if ($assessmentConfig): ?>
                    <span><?php echo htmlspecialchars($assessmentConfig['icon']); ?></span>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($assessmentName); ?></span>
            </div>

            <span class="result-badge" style="background:<?php echo $levelColor; ?>20;color:<?php echo $levelColor; ?>;">
                <?php echo htmlspecialchars($result['level_name'] ?? '未知等级'); ?>
            </span>

            <div class="score-circle">
                <svg width="160" height="160" viewBox="0 0 160 160">
                    <circle class="bg" cx="80" cy="80" r="70"/>
                    <circle class="progress" cx="80" cy="80" r="70"
                        stroke="<?php echo $levelColor; ?>"
                        stroke-dasharray="440"
                        stroke-dashoffset="<?php echo 440 - (440 * ($result['percentage'] ?? 0) / 100); ?>"
                        id="scoreProgress"/>
                </svg>
                <div class="score-text">
                    <div class="score-number" style="color:<?php echo $levelColor; ?>" data-target="<?php echo $result['total_score'] ?? 0; ?>">
                        0
                    </div>
                    <div class="score-max">/ <?php echo $result['max_score'] ?? '--'; ?> 分</div>
                </div>
            </div>

            <h2 class="result-level"><?php echo htmlspecialchars($result['level_name'] ?? '未知等级'); ?></h2>
            <p class="result-description"><?php echo htmlspecialchars($result['description'] ?? '暂无描述信息'); ?></p>

            <!-- ========== 心理治愈与安抚模块 ========== -->
            <?php
            function getHealingTier($level) {
                if (in_array($level, ['minimal','normal','low','high'])) return 'excellent';
                if (in_array($level, ['mild'])) return 'moderate';
                if (in_array($level, ['moderate','moderate_severe','moderately-severe','severe','very_low'])) return 'caring';
                return 'moderate';
            }

            $tier = getHealingTier($result['level'] ?? 'normal');

            if ($tier === 'excellent'):
            ?>
            <section class="healing-module">
                <div class="hm-main-card hm-excellent">
                    <div class="hm-card-inner">
                        <div class="hm-badge">✨ 此刻的你</div>
                        <h2 class="hm-title">看见你正在用心照顾自己</h2>
                        <p class="hm-text">
                            你愿意花时间停下来，认真了解自己的内心状态——这件事本身就值得被认真对待。
                            无论数字说了什么，<strong>那个愿意面对真实的你</strong>，已经做了一件很棒的事。
                            测评不是审判，而是一次与自己安静对话的机会。
                            你今天坐在这里阅读这段文字的样子，就是一个人在好好爱着自己的证明。
                        </p>
                    </div>
                </div>
                <div class="hm-perspective">
                    <div class="hm-persp-icon">💭</div>
                    <div class="hm-persp-body">
                        <h4>关于这个分数，想和你聊聊</h4>
                        <ul>
                            <li>这个测评只反映了<strong>特定时间段内、特定维度上</strong>的一个快照——就像天气，不等于气候。</li>
                            <li>它无法衡量你的创造力、善良、幽默感，或者你在朋友眼中的温暖。</li>
                            <li>今天的「好」不需要成为明天的负担。<strong>允许自己有起伏</strong>，本来就是生命力的一部分。</li>
                            <li>你的全部潜能，远远超出任何一张问卷所能触及的范围。</li>
                        </ul>
                    </div>
                </div>
                <div class="hm-glow-grid">
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🌱</span>
                        <div><strong>觉察力</strong><br>你已经拥有了最宝贵的能力——愿意关注自己。</div>
                    </div>
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🛡️</span>
                        <div><strong>主动性</strong><br>主动寻求了解，而不是回避，这是勇气的体现。</div>
                    </div>
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">⭐</span>
                        <div><strong>独特性</strong><br>没有任何一个分数可以定义你这个完整而丰富的人。</div>
                    </div>
                </div>
                <div class="hm-promise">
                    <div class="hm-promise-label">💌 给自己的一份小约定</div>
                    <p>今天做完这个测评之后，奖励自己做一件小小的喜欢的事吧——喝一杯热茶、听一首喜欢的歌、或者只是在窗前发一会儿呆。<strong>你值得这些温柔的瞬间。</strong></p>
                </div>
            </section>

            <?php elseif ($tier === 'moderate'): ?>
            <section class="healing-module">
                <div class="hm-main-card hm-moderate">
                    <div class="hm-card-inner">
                        <div class="hm-badge">🌤 此刻的你</div>
                        <h2 class="hm-title">没关系，我们都在这里陪你</h2>
                        <p class="hm-text">
                            如果此刻你觉得一切还好，但也说不清哪里有点不一样——那完全没问题。
                            人生不是一条笔直向上的线，<strong>偶尔的云朵飘过，不代表太阳消失了</strong>。
                            你能察觉到内心细微的变化，说明你对自己足够在意和敏感，
                            这份敏锐本身就是一种天赋。不用急着给任何感受下结论，
                            就像你不会因为一天多云就断定整个季节都是阴天一样。
                        </p>
                    </div>
                </div>
                <div class="hm-perspective">
                    <div class="hm-persp-icon">🔍</div>
                    <div class="hm-persp-body">
                        <h4>关于这个分数，想和你聊聊</h4>
                        <ul>
                            <li>这个测评捕捉到的，只是你生命中<strong>某一个切面</strong>的瞬间影像。你远比这更宽广。</li>
                            <li>情绪像潮汐一样来去，<strong>感到一些波动是正常且健康的</strong>——说明你的内在感知在运作。</li>
                            <li>不要把「平平」解读为「不够好」。<strong>稳定和平凡里藏着巨大的力量</strong>，那是很多人梦寐以求的状态。</li>
                            <li>你的价值不依附于任何测评结果。你之所以珍贵，是因为你是你。</li>
                        </ul>
                    </div>
                </div>
                <div class="hm-glow-grid">
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🎨</span>
                        <div><strong>敏感力</strong><br>你能感受到细腻的情绪变化，这是一种难得的内在智慧。</div>
                    </div>
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🪴</span>
                        <div><strong>成长性</strong><br>每一次觉察都是土壤里的养分，滋养着未来的你。</div>
                    </div>
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🕊️</span>
                        <div><strong>平衡感</strong><br>能在波动中保持基本稳定，说明你拥有很好的内在韧性基础。</div>
                    </div>
                </div>
                <div class="hm-promise">
                    <div class="hm-promise-label">💌 给自己的一份小约定</div>
                    <p>今晚临睡前，试着回想今天发生的一件让你嘴角微微上扬的小事——哪怕只是路边一朵好看的花、一句温暖的问候。<strong>把注意力放在这些微小却真实存在的美好上</strong>，它们会慢慢积攒成心里的光。</p>
                </div>
            </section>

            <?php else: ?>
            <section class="healing-module">
                <div class="hm-main-card hm-caring">
                    <div class="hm-card-inner">
                        <div class="hm-badge">🫂 此刻的你</div>
                        <h2 class="hm-title">谢谢你愿意在这里，让我们陪你一会儿</h2>
                        <p class="hm-text">
                            我知道，走到这一步可能并不容易。也许你犹豫过，也许你不确定是否该打开这份报告。
                            但你还是来了，<strong>这说明你内心深处有一股不想放弃自己的力量</strong>——
                            那股力量或许现在还很小，但它一直在那里，从来没有离开过。
                            这一次的测评结果只是一个数字，它不能定义你是谁，
                            也不能预言你会变成什么样的人。它只是一张地图上的一个点，
                            而你的人生，是一整片辽阔的海域。
                        </p>
                    </div>
                </div>
                <div class="hm-perspective">
                    <div class="hm-persp-icon">💝</div>
                    <div class="hm-persp-body">
                        <h4>关于这个分数，我想郑重地告诉你</h4>
                        <ul>
                            <li><strong>这个数字≠你的人</strong>。它只记录了某段时间某些方面的感受，而你是一个完整、复杂、充满可能性的人。</li>
                            <li>困难的感觉是真实的，但它<strong>不是永恒的</strong>。无数人在经历过类似时刻后，都找到了属于自己的出路。</li>
                            <li>你不需要立刻「变好」。允许自己暂时不好，本身就是一种对自己的温柔。<strong>休息也是前进的一部分。</strong></li>
                            <li>如果此刻觉得孤单，请记得：<strong>寻求帮助不是示弱，而是选择不再独自承担</strong>。这是非常勇敢的决定。</li>
                        </ul>
                    </div>
                </div>
                <div class="hm-glow-grid">
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🔥</span>
                        <div><strong>勇气</strong><br>直面内心的真实感受需要巨大的力量，而你刚刚做到了这一点。</div>
                    </div>
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">🌊</span>
                        <div><strong>韧性</strong><br>你能扛住这么多还能来到这里，说明你的内在比你想象的更坚韧。</div>
                    </div>
                    <div class="hm-glow-item">
                        <span class="hm-glow-icon">💎</span>
                        <div><strong>不可替代</strong><br>这个世界只有一个你。你的经历、感受、思考方式，都是独一无二的礼物。</div>
                    </div>
                </div>
                <div class="hm-promise">
                    <div class="hm-promise-label">💌 给自己的一份小约定</div>
                    <p>今天，先不做任何「应该」做的事。只做一件让你感到安全或平静的小事：
                        盖着毯子发呆也可以，给信任的人发一句「最近有点累」也好。
                        <strong>你不需要立刻好起来，你只需要知道——有人希望你好好的，而那个人首先应该是你自己。</strong></p>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <div class="result-cards">
            <div class="result-card">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-500)" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                    </svg>
                    专业建议
                </h3>
                <ul class="recommendation-list">
                    <?php foreach (($result['recommendation'] ?? []) as $rec): ?>
                        <li><?php echo htmlspecialchars($rec); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if (!empty($result['dimensions'])): ?>
            <div class="result-card">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary-500)" stroke-width="2">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    维度分析
                </h3>
                <div class="dimension-bars">
                    <?php foreach (($result['dimensions'] ?? []) as $dim): 
                        $percentage = round(($dim['score'] / $dim['max']) * 100);
                        if ($percentage <= 33) { $barColor = '#4CAF50'; }
                        elseif ($percentage <= 66) { $barColor = '#FF9800'; }
                        else { $barColor = '#F44336'; }
                    ?>
                        <div class="dimension-item">
                            <div class="dimension-header">
                                <span class="dimension-name"><?php echo htmlspecialchars($dim['name']); ?></span>
                                <span class="dimension-score"><?php echo $dim['score']; ?>/<?php echo $dim['max']; ?></span>
                            </div>
                            <p class="dimension-desc" style="font-size:0.82rem;color:var(--gray-400);margin-top:2px;"><?php echo isset($dim['desc']) ? htmlspecialchars($dim['desc']) : ''; ?></p>
                            <div class="dimension-bar">
                                <div class="dimension-fill" style="width:<?php echo $percentage; ?>%;background:<?php echo $barColor; ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- 心理健康技巧 -->
        <section class="tips-section">
            <h2 class="tips-title">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary-500)" stroke-width="2">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                每日心理健康小贴士
            </h2>
            <p class="tips-subtitle">以下是一些简单实用的自我调节方法，你可以从今天开始尝试</p>

            <div class="tips-grid">
                <div class="tip-card">
                    <div class="tip-icon-wrap" style="background:linear-gradient(135deg,#DBEAFE,#BFDBFE);">
                        <span>🌬️</span>
                    </div>
                    <h4>4-7-8 呼吸法</h4>
                    <p class="tip-desc">快速平复焦虑情绪的经典方法</p>
                    <ol class="tip-steps">
                        <li>用鼻子吸气，默数 <strong>4秒</strong></li>
                        <li>屏住呼吸，默数 <strong>7秒</strong></li>
                        <li>用嘴缓缓呼气，默数 <strong>8秒</strong></li>
                        <li>重复 <strong>4个循环</strong></li>
                    </ol>
                </div>

                <div class="tip-card">
                    <div class="tip-icon-wrap" style="background:linear-gradient(135deg,#FCE7F3,#FBCFE8);">
                        <span>🧘</span>
                    </div>
                    <h4>5分钟身体扫描</h4>
                    <p class="tip-desc">觉察身体的紧张，逐步释放压力</p>
                    <ol class="tip-steps">
                        <li>安静坐下，闭上眼睛</li>
                        <li>从脚趾开始，慢慢向上感受每个部位</li>
                        <li>注意哪里有紧绷或不适</li>
                        <li>每次呼气时，想象放松那个部位</li>
                    </ol>
                </div>

                <div class="tip-card">
                    <div class="tip-icon-wrap" style="background:linear-gradient(135deg,#D1FAE5,#A7F3D0);">
                        <span>💭</span>
                    </div>
                    <h4>想法 vs 事实</h4>
                    <p class="tip-desc">识别并挑战消极的自动化思维</p>
                    <ol class="tip-steps">
                        <li>写下让你困扰的想法</li>
                        <li>问自己：「这是事实还是猜测？」</li>
                        <li>找证据支持或反驳这个想法</li>
                        <li>用一个更平衡的想法替代它</li>
                    </ol>
                </div>

                <div class="tip-card">
                    <div class="tip-icon-wrap" style="background:linear-gradient(135deg,#FEF3C7,#FDE68A);">
                        <span>🙏</span>
                    </div>
                    <h4>三件好事日记</h4>
                    <p class="tip-desc">每天记录积极事物，重塑大脑回路</p>
                    <ol class="tip-steps">
                        <li>每晚睡前花 <strong>5分钟</strong></li>
                        <li>写下今天发生的 <strong>3件好事</strong></li>
                        <li>每件事写一句「为什么这让我感到美好」</li>
                        <li>坚持 <strong>21天</strong> 形成习惯</li>
                    </ol>
                </div>

                <div class="tip-card">
                    <div class="tip-icon-wrap" style="background:linear-gradient(135deg,#EDE9FE,#DDD6FE);">
                        <span>💪</span>
                    </div>
                    <h4>肌肉渐进放松</h4>
                    <p class="tip-desc">通过肌肉收紧和释放来减轻躯体紧张</p>
                    <ol class="tip-steps">
                        <li>握紧拳头 <strong>5秒</strong>，感受紧张</li>
                        <li>突然松开，感受放松 <strong>10秒</strong></li>
                        <li>依次做：肩膀、眉头、腹部、腿部</li>
                        <li>全身扫描一遍约需 <strong>15分钟</strong></li>
                    </ol>
                </div>

                <div class="tip-card">
                    <div class="tip-icon-wrap" style="background:linear-gradient(135deg,#FEE2E2,#FECACA);">
                        <span>🤝</span>
                    </div>
                    <h4>主动建立连接</h4>
                    <p class="tip-desc">社交支持是心理健康的保护伞</p>
                    <ol class="tip-steps">
                        <li>每周至少联系 <strong>一位</strong> 朋友/家人</li>
                        <li>不一定是谈心，简单的问候也有效</li>
                        <li>尝试加入一个兴趣小组或社群</li>
                        <li>如果难以开口，可以先从文字消息开始</li>
                    </ol>
                </div>
            </div>

            <!-- 紧急求助信息 -->
            <div class="crisis-box">
                <div class="crisis-header">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
                        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>如果你需要即时帮助</span>
                </div>
                <div class="crisis-links">
                    <a href="tel:4001619995" class="crisis-link">
                        <span class="crisis-num">400-161-9995</span>
                        <span class="crisis-label">全国心理援助热线（24小时）</span>
                    </a>
                    <a href="tel:01082951332" class="crisis-link">
                        <span class="crisis-num">010-82951332</span>
                        <span class="crisis-label">北京心理危机干预热线</span>
                    </a>
                    <a href="tel:12320" class="crisis-link">
                        <span class="crisis-num">12320</span>
                        <span class="crisis-label">公共卫生公益热线</span>
                    </a>
                </div>
                <p class="crisis-note">* 这些热线提供专业的心理支持和危机干预服务，通话免费且保密</p>
            </div>
        </section>

        <div class="result-actions">
            <a href="test.php?type=<?php echo $assessmentType; ?>" class="btn btn-primary btn-lg">
                重新测评
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </a>
            <a href="index.php#assessments" class="btn btn-outline btn-lg">
                更多量表
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
            </a>
            <a href="history.php" class="btn btn-outline btn-lg">
                查看历史
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
        </div>

        <div style="margin-top:40px;padding:24px;background:var(--gray-50);border-radius:12px;border-left:4px solid var(--accent-yellow);">
            <strong style="display:block;margin-bottom:8px;color:#92400E;">重要提醒</strong>
            <p style="font-size:0.9rem;color:#78716C;line-height:1.7;">
                本测评结果仅供参考，不构成医学诊断。如果您对自己的心理健康状况感到担忧，或症状持续超过两周，
                建议您咨询专业的精神科医生或心理咨询师。
            </p>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        window.addEventListener('load', function() {
            const progress = document.getElementById('scoreProgress');
            if (progress) {
                const targetOffset = progress.getAttribute('stroke-dashoffset');
                progress.style.strokeDashoffset = '440';
                setTimeout(() => {
                    progress.style.strokeDashoffset = targetOffset;
                }, 300);
            }

            document.querySelectorAll('.dimension-fill').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 600);
            });

            const scoreNum = document.querySelector('.score-number');
            if (scoreNum && scoreNum.dataset.target) {
                const target = parseInt(scoreNum.dataset.target);
                let current = 0;
                const step = Math.ceil(target / 40);
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        scoreNum.textContent = target;
                        clearInterval(timer);
                    } else {
                        scoreNum.textContent = current;
                    }
                }, 25);
            }
        });

        function dismissAiDisclaimer() {
            var banner = document.getElementById('aiDisclaimer');
            if (banner) {
                banner.style.transition = 'all 0.3s ease';
                banner.style.opacity = '0';
                banner.style.transform = 'translateY(-10px)';
                banner.style.maxHeight = '0';
                banner.style.paddingTop = '0';
                banner.style.paddingBottom = '0';
                banner.style.marginBottom = '0';
                banner.style.borderBottom = 'none';
                banner.style.overflow = 'hidden';
                setTimeout(function() { banner.style.display = 'none'; }, 300);
                try { sessionStorage.setItem('ai_disclaimer_dismissed', '1'); } catch(e) {}
            }
        }

        (function() {
            try {
                if (sessionStorage.getItem('ai_disclaimer_dismissed') === '1') {
                    dismissAiDisclaimer();
                }
            } catch(e) {}
        })();
    </script>
</body>
</html>
