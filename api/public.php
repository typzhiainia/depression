<?php
/**
 * 公共 API 接口（前端调用）
 */
require_once '../config.php';
require_once '../assessments.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'submit_assessment':
        // 提交测评结果
        $assessmentType = $_POST['assessment_type'] ?? 'phq9';
        $answersRaw = $_POST['answers'] ?? '[]';
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : null;
        
        $answers = json_decode($answersRaw, true);
        if (!is_array($answers)) {
            json_response(['success' => false, 'message' => '答案格式错误']);
        }
        
        // 计算结果
        $result = AssessmentManager::calculateResult($assessmentType, $answers);
        
        // 保存到数据库
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $recordId = AssessmentManager::saveResultToDB($assessmentType, $result, $answers, $duration, $userAgent);
        
        // 存入session
        $_SESSION['last_result'] = $result;
        $_SESSION['last_answers'] = $answers;
        $_SESSION['last_assessment_type'] = $assessmentType;
        
        json_response([
            'success' => true,
            'message' => '测评完成',
            'data' => array_merge($result, ['record_id' => $recordId])
        ]);
        break;

    case 'get_history':
        // 获取个人历史记录
        $limit = min(50, max(5, intval($_GET['limit'] ?? 10)));
        $records = AssessmentManager::getHistory($limit);
        
        foreach ($records as &$r) {
            $r['recommendation'] = json_decode($r['recommendation'], true) ?: [];
            $r['time_ago'] = getTimeAgoString($r['created_at']);
        }
        unset($r);
        
        json_response(['success' => true, 'data' => $records]);
        break;

    case 'get_stats':
        // 公开统计数据（用于首页展示）
        try {
            $db = db();
            
            $today = date('Y-m-d');
            $stmt = $db->prepare("SELECT COUNT(*) as c FROM test_records WHERE date(created_at) = ?");
            $stmt->execute([$today]);
            $todayCount = $stmt->fetch()['c'];
            
            $trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $s = $db->prepare("SELECT COUNT(*) as c, AVG(total_score) as avg FROM test_records WHERE date(created_at) = ?");
                $s->execute([$d]);
                $r = $s->fetch();
                $trend[] = ['date' => $d, 'count' => (int)$r['c'], 'avg' => round($r['avg'] ?? 0, 1)];
            }
            
            // 各量表参与人数
            $typeStmt = $db->query("SELECT assessment_type, COUNT(*) as c FROM test_records GROUP BY assessment_type ORDER BY c DESC");
            $byType = $typeStmt->fetchAll();
            
            json_response([
                'success' => true,
                'data' => [
                    'today_count' => (int)$todayCount,
                    'trend' => $trend,
                    'by_type' => $byType
                ]
            ]);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_distribution':
        // 获取分数分布
        $type = $_GET['type'] ?? 'phq9';
        try {
            $db = db();
            $stmt = $db->prepare("SELECT total_score, severity_level FROM test_records WHERE assessment_type = ? ORDER BY created_at DESC LIMIT 500");
            $stmt->execute([$type]);
            $rows = $stmt->fetchAll();
            
            $distribution = [];
            $levelCounts = [];
            
            foreach ($rows as $r) {
                $score = (int)$r['total_score'];
                $bucket = floor($score / 5) * 5;
                $distribution[$bucket] = ($distribution[$bucket] ?? 0) + 1;
                
                $level = $r['severity_level'];
                $levelCounts[$level] = ($levelCounts[$level] ?? 0) + 1;
            }
            
            json_response([
                'success' => true,
                'data' => [
                    'distribution' => $distribution,
                    'levels' => $levelCounts,
                    'total' => count($rows)
                ]
            ]);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_recent':
        // 获取最近记录（公开）
        $type = $_GET['type'] ?? null;
        $limit = min(20, max(5, intval($_GET['limit'] ?? 10)));
        
        try {
            $db = db();
            if ($type) {
                $stmt = $db->prepare("SELECT id, total_score, severity_level, assessment_type, test_duration, created_at FROM test_records WHERE assessment_type = ? ORDER BY created_at DESC LIMIT ?");
                $stmt->execute([$type, $limit]);
            } else {
                $stmt = $db->prepare("SELECT id, total_score, severity_level, assessment_type, test_duration, created_at FROM test_records ORDER BY created_at DESC LIMIT {$limit}");
                $stmt->execute([]);
            }
            $records = $stmt->fetchAll();
            
            json_response(['success' => true, 'data' => $records]);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        json_response(['success' => false, 'message' => '未知操作']);
}

/**
 * 获取时间差的可读字符串
 */
function getTimeAgoString($datetime) {
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
