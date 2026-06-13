<?php
require_once 'config.php';
require_once 'assessments.php';

$assessmentType = $_GET['type'] ?? 'phq9';
$assessmentConfig = AssessmentManager::getAssessment($assessmentType);

if (!$assessmentConfig) {
    header('Location: index.php');
    exit;
}

$questions = $assessmentConfig['questions'];
$questionCount = count($questions);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessmentConfig['full_name']); ?> - 测评中</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="test-page">
    <!-- Progress Bar -->
    <div class="progress-bar-wrap">
        <div class="wrap" style="padding-top:0;padding-bottom:0;">
            <div class="progress-info">
                <span class="progress-type"><?php echo htmlspecialchars($assessmentConfig['icon'] . ' ' . $assessmentConfig['name']); ?></span>
                <span class="progress-text">第 <strong id="currentQ">1</strong> / <?php echo $questionCount; ?> 题</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" id="progressFill" style="width: calc(100% / <?php echo $questionCount; ?>);"></div>
            </div>
        </div>
    </div>

    <!-- Question Card -->
    <main class="test-container">
        <form id="testForm" action="" method="post">
            <input type="hidden" name="assessment_type" value="<?php echo htmlspecialchars($assessmentType); ?>">
            <?php echo csrf_field(); ?>
            
            <?php foreach ($questions as $idx => $q): ?>
            <div class="question-block <?php echo $idx === 0 ? 'active' : ''; ?>" data-question="<?php echo $idx + 1; ?>">
                <div class="q-number">第 <?php echo $idx + 1; ?> 题</div>
                <h2 class="q-text"><?php echo htmlspecialchars($q['text']); ?></h2>
                <?php if (!empty($q['description'])): ?>
                <p class="q-desc"><?php echo htmlspecialchars($q['description']); ?></p>
                <?php endif; ?>

                <div class="options-list">
                    <?php foreach ($q['options'] as $opt): ?>
                    <label class="option-item">
                        <input type="radio" name="q<?php echo $q['id']; ?>" value="<?php echo $opt['value']; ?>" required>
                        <span class="option-radio"></span>
                        <span class="option-label"><?php echo htmlspecialchars($opt['label']); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Navigation -->
            <div class="test-nav">
                <button type="button" class="btn btn-outline btn-nav" id="btnPrev" onclick="prevQuestion()" disabled style="visibility:hidden;">
                    ← 上一题
                </button>
                <button type="button" class="btn btn-primary btn-nav" id="btnNext" onclick="nextQuestion()">
                    下一题 →
                </button>
                <button type="submit" class="btn btn-primary btn-nav btn-submit" id="btnSubmit" style="display:none;" onclick="startTimer()">
                    提交问卷
                </button>
            </div>
        </form>
    </main>

    <script src="js/main.js"></script>
    <script>
        const TOTAL_Q = <?php echo $questionCount; ?>;
        let currentQuestion = 1;
        let startTime = Date.now();
        const form = document.getElementById('testForm');

        // Navigation
        function showQuestion(n) {
            document.querySelectorAll('.question-block').forEach(b => b.classList.remove('active'));
            document.querySelector('[data-question="' + n + '"]').classList.add('active');
            currentQuestion = n;
            document.getElementById('currentQ').textContent = n;
            document.getElementById('progressFill').style.width = (n / TOTAL_Q * 100) + '%';

            document.getElementById('btnPrev').style.visibility = n === 1 ? 'hidden' : 'visible';
            if (n >= TOTAL_Q) {
                document.getElementById('btnNext').style.display = 'none';
                document.getElementById('btnSubmit').style.display = 'inline-flex';
            } else {
                document.getElementById('btnNext').style.display = 'inline-flex';
                document.getElementById('btnSubmit').style.display = 'none';
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function nextQuestion() {
            // Validate current question has an answer
            const activeBlock = document.querySelector('.question-block.active');
            const checked = activeBlock.querySelector('input[type=radio]:checked');
            if (!checked) { shakeCard(activeBlock); return; }
            if (currentQuestion < TOTAL_Q) showQuestion(currentQuestion + 1);
        }

        function prevQuestion() {
            if (currentQuestion > 1) showQuestion(currentQuestion - 1);
        }

        function shakeCard(el) {
            el.style.animation = 'shake 0.4s ease-in-out';
            setTimeout(() => el.style.animation = '', 450);
        }

        function startTimer() { /* already tracked */ }

        // 点击选项：非末题自动下一题，末题自动提交
        document.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function() {
                const input = this.querySelector('input[type=radio]');
                if (!input) return;
                // 延迟一点让 radio 选中状态先渲染
                setTimeout(() => {
                    if (currentQuestion < TOTAL_Q) {
                        showQuestion(currentQuestion + 1);
                    } else {
                        form.dispatchEvent(new Event('submit'));
                    }
                }, 180);
            });
        });

        // Form submit
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const answers = [];
            for (let i = 1; i <= TOTAL_Q; i++) {
                const val = formData.get('q' + i);
                answers.push(val !== null ? parseInt(val) : 0);
            }

            const duration = Math.round((Date.now() - startTime) / 1000);

            try {
                const res = await fetch('api/public.php?action=submit_assessment', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': '<?php echo csrf_token(); ?>'
                    },
                    body: 'assessment_type=<?php echo urlencode($assessmentType); ?>&answers=' + JSON.stringify(answers) + '&duration=' + duration + '&csrf_token=<?php echo csrf_token(); ?>'
                });
                const json = await res.json();
                if (json.success) {
                    window.location.href = 'result.php';
                } else {
                    alert(json.message || '提交失败');
                }
            } catch(err) {
                alert('网络错误，请重试');
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight' || e.key === 'Enter') { e.preventDefault(); if (currentQuestion < TOTAL_Q) nextQuestion(); else document.getElementById('btnSubmit').click(); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); prevQuestion(); }
        });
    </script>
    <style>
        @keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-8px)} 40%{transform:translateX(8px)} 60%{transform:translateX(-6px)} 80%{transform:translateX(6px)} }
    </style>
</body>
</html>
