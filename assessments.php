<?php
/**
 * 多类型心理测评量表系统
 */

class AssessmentManager {
    
    private static $assessments = null;
    
    public static function getAllAssessments() {
        if (self::$assessments === null) {
            self::$assessments = [
                'phq9' => self::getPHQ9Config(),
                'gad7' => self::getGAD7Config(),
                'pss10' => self::getPSS10Config(),
                'ucla3' => self::getUCLA3Config(),
                'iai' => self::getIAIConfig(),
                'self_esteem' => self::getSelfEsteemConfig()
            ];
        }
        return self::$assessments;
    }
    
    public static function getAssessment($type) {
        $all = self::getAllAssessments();
        return isset($all[$type]) ? $all[$type] : null;
    }
    
    public static function calculateResult($type, $answers) {
        switch ($type) {
            case 'phq9': return self::calculatePHQ9($answers);
            case 'gad7': return self::calculateGAD7($answers);
            case 'pss10': return self::calculatePSS10($answers);
            case 'ucla3': return self::calculateUCLA3($answers);
            case 'iai': return self::calculateIAI($answers);
            case 'self_esteem': return self::calculateSelfEsteem($answers);
            default: return self::calculatePHQ9($answers);
        }
    }

    // ========== PHQ-9 抑郁症量表 ==========
    public static function getPHQ9Config() {
        return [
            'id' => 'phq9', 'name' => 'PHQ-9', 'full_name' => '抑郁症自评量表',
            'description' => '评估过去两周内抑郁症状的频率和严重程度',
            'icon' => "\xf0\x9f\xa7\xa0", 'color' => '#6366F1',
            'duration' => '约2-3分钟', 'questions_count' => 9, 'max_score' => 27,
            'category' => '情绪健康',
            'questions' => [
                ['id'=>1,'text'=>'做事时提不起劲或没有兴趣','description'=>'包括工作、家务、娱乐活动等','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>2,'text'=>'感到心情低落、沮丧或绝望','description'=>'持续的情绪低落状态','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>3,'text'=>'入睡困难、睡不着或睡眠过多','description'=>'包括失眠、早醒、嗜睡等','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>4,'text'=>'感觉疲倦或没有活力','description'=>'即使休息后也感到疲惫','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>5,'text'=>'食欲不振或吃得太多','description'=>'明显的饮食量变化','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>6,'text'=>'觉得自己很糟糕——或觉得自己很失败，让自己或家人失望','description'=>'过度的自我否定和内疚感','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>7,'text'=>'对事物专注有困难，例如阅读报纸或看电视时不能集中注意力','description'=>'注意力难以集中','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>8,'text'=>'动作或说话速度缓慢到别人已经察觉？或相反——烦躁或坐立不安、动来动去','描述'=>'明显的行动迟缓或焦躁不安','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>9,'text'=>'有不如死掉或用某种方式伤害自己的念头','description'=>'自杀或自伤的想法','options'=>[['value'=>0,'label'=>'完全不会'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'一半以上的天数'],['value'=>3,'label'=>'几乎每天']]]
            ]
        ];
    }

    public static function calculatePHQ9($answers) {
        $totalScore = array_sum($answers);
        
        if ($totalScore <= 4) {
            $level = 'minimal'; $levelName = '无/极轻度'; $levelColor = '#4CAF50';
            $description = '您目前的心理健康状况良好，没有明显的抑郁症状。建议保持健康的生活方式。';
            $recommendation = ['保持规律作息和适度运动','与家人朋友保持良好社交联系','定期进行心理健康自我监测'];
        } elseif ($totalScore <= 9) {
            $level = 'mild'; $levelName = '轻度'; $levelColor = '#8BC34A';
            $description = '您存在轻度抑郁症状，可能对日常生活有一定影响，但通过适当自我调节通常可改善。';
            $recommendation = ['增加户外活动和体育锻炼(每周至少3次)','保证充足睡眠，建立规律作息','与信任的人分享感受','减少酒精和咖啡因摄入'];
        } elseif ($totalScore <= 14) {
            $level = 'moderate'; $levelName = '中度'; $levelColor = '#FF9800';
            $description = '您存在中度抑郁症状，可能正在对工作学习和人际关系产生明显影响。建议尽快寻求专业帮助。';
            $recommendation = ['建议预约专业心理咨询师进行评估','考虑认知行为疗法(CBT)','不要独自承受，向亲友倾诉'];
        } elseif ($totalScore <= 19) {
            $level = 'moderately-severe'; $levelName = '中重度'; $levelColor = '#FF5722';
            $description = '您存在中重度抑郁症状，可能严重干扰日常生活功能。请务必尽快寻求精神科医生帮助。';
            $recommendation = ['请尽快前往医院精神科就诊','可能需要药物配合心理咨询','如有自伤想法立即拨打热线400-161-9995'];
        } else {
            $level = 'severe'; $levelName = '重度'; $levelColor = '#F44336';
            $description = '测试结果显示重度抑郁症状，这是一个需要认真对待的情况，请立即寻求专业医疗帮助。';
            $recommendation = ['立即前往医院精神科就诊','拨打24小时心理援助热线400-161-9995','紧急情况或有自伤风险请立即拨打120或110'];
        }

        return [
            'total_score' => $totalScore, 'max_score' => 27,
            'level' => $level, 'level_name' => $levelName,
            'level_color' => $levelColor, 'description' => $description,
            'recommendation' => $recommendation,
            'dimensions' => [
                ['name'=>'情绪维度','score'=>(int)$answers[1]+(int)$answers[2],'max'=>6,'desc'=>'情绪低落、沮丧等症状'],
                ['name'=>'躯体化症状','score'=>(int)$answers[3]+(int)$answers[4]+(int)$answers[5],'max'=>9,'desc'=>'睡眠、精力、食欲问题'],
                ['name'=>'认知功能','score'=>(int)$answers[6]+(int)$answers[7],'max'=>6,'desc'=>'注意力、思维速度'],
                ['name'=>'风险评估','score'=>(int)$answers[8],'max'=>3,'desc'=>'是否存在自伤风险']
            ],
            'percentage' => round(($totalScore / 27) * 100),
            'has_risk' => (int)$answers[8] > 0
        ];
    }

    // ========== GAD-7 焦虑量表 ==========
    public static function getGAD7Config() {
        return [
            'id' => 'gad7', 'name' => 'GAD-7', 'full_name' => '广泛性焦虑量表',
            'description' => '评估过去两周内的焦虑症状程度，包括紧张、担忧等',
            'icon' => "\xf0\x9f\x98\xb0", 'color' => '#F59E0B',
            'duration' => '约1-2分钟', 'questions_count' => 7, 'max_score' => 21,
            'category' => '焦虑评估',
            'questions' => [
                ['id'=>1,'text'=>'感到紧张、焦虑或急切','description'=>'无法放松的紧绷感','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>2,'text'=>'无法控制或停止担忧','description'=>'反复出现难以控制的担心念头','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>3,'text'=>'对各种事情担忧过度','description'=>'对很多事情过分担心','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>4,'text'=>'很难放松下来','description'=>'身体和精神都处于紧张状态','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>5,'text'=>'坐立不安，静不下来','description'=>'感觉需要不停移动或活动','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>6,'text'=>'容易烦躁或恼火','description'=>'比平时更容易发脾气或不耐烦','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]],
                ['id'=>7,'text'=>'感到害怕，似乎有什么可怕的事情会发生','description'=>'预感会有不好的事情发生','options'=>[['value'=>0,'label'=>'完全没有'],['value'=>1,'label'=>'好几天'],['value'=>2,'label'=>'超过一半的天数'],['value'=>3,'label'=>'几乎每天']]]
            ]
        ];
    }

    public static function calculateGAD7($answers) {
        $totalScore = array_sum($answers);

        if ($totalScore <= 4) {
            $level = 'minimal'; $levelName = '无/极轻度'; $levelColor = '#4CAF50';
            $description = '您的焦虑水平在正常范围内。偶尔的紧张是正常的情绪反应。';
            $recommendation = ['继续保持健康的应对压力的方式','尝试正念冥想或深呼吸练习','保持规律的运动习惯'];
        } elseif ($totalScore <= 9) {
            $level = 'mild'; $levelName = '轻度'; $levelColor = '#8BC34A';
            $description = '您存在轻度焦虑症状。适当的自我调节可以帮助缓解。';
            $recommendation = ['学习并实践渐进式肌肉放松','限制咖啡因和酒精摄入','建立规律的睡眠时间表'];
        } elseif ($totalScore <= 14) {
            $level = 'moderate'; $levelName = '中度'; $levelColor = '#FF9800';
            $description = '您存在中度焦虑症状，这些症状可能正在影响您的日常生活质量。建议寻求专业帮助。';
            $recommendation = ['建议咨询心理咨询师了解焦虑管理技巧','考虑学习认知行为疗法(CBT)','记录焦虑日记帮助识别触发因素'];
        } else {
            $level = 'severe'; $levelName = '重度'; $levelColor = '#F44336';
            $description = '您存在重度焦虑症状，已经显著影响了您的生活功能，强烈建议尽快寻求专业医疗帮助。';
            $recommendation = ['请尽快前往医院心理科或精神科就诊','医生可能会建议药物治疗配合心理治疗','不要独自承受，告诉信任的人您的状况'];
        }

        return [
            'total_score' => $totalScore, 'max_score' => 21,
            'level' => $level, 'level_name' => $levelName,
            'level_color' => $levelColor, 'description' => $description,
            'recommendation' => $recommendation,
            'dimensions' => [
                ['name'=>'紧张感','score'=>(int)$answers[0]+(int)$answers[3],'max'=>6,'desc'=>'身体和心理上的紧张状态'],
                ['name'=>'担忧倾向','score'=>(int)$answers[1]+(int)$answers[2],'max'=>6,'desc'=>'控制不住的担心念头'],
                ['name'=>'行为反应','score'=>(int)$answers[4]+(int)$answers[5],'max'=>6,'desc'=>'坐立不安、易怒等表现'],
                ['name'=>'恐惧预期','score'=>(int)$answers[6],'max'=>3,'desc'=>'对未来事件的负面预期']
            ],
            'percentage' => round(($totalScore / 21) * 100), 'has_risk' => false
        ];
    }

    // ========== PSS-10 压力量表 ==========
    public static function getPSS10Config() {
        return [
            'id' => 'pss10', 'name' => 'PSS-10', 'full_name' => '感知压力量表',
            'description' => '测量过去一个月内感受到的压力程度和应对能力',
            'icon' => "\xe2\x9a\xa1", 'color' => '#EF4444',
            'duration' => '约2分钟', 'questions_count' => 10, 'max_score' => 40,
            'category' => '压力评估',
            'questions' => [
                ['id'=>1,'text'=>'在过去一个月里，有多少时间因某些意料之外的事情而感到心烦意乱？','description'=>'','options'=>[['value'=>0,'label'=>'从不'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'相当多时候'],['value'=>4,'label'=>'非常经常']]],
                ['id'=>2,'text'=>'在过去一个月里，有多少时间感觉到自己不能控制生活中的重要事情？','description'=>'','options'=>[['value'=>0,'label'=>'从不'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'相当多时候'],['value'=>4,'label'=>'非常经常']]],
                ['id'=>3,'text'=>'在过去一个月里，有多少时间感到紧张和有压力？','description'=>'','options'=>[['value'=>0,'label'=>'从不'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'相当多时候'],['value'=>4,'label'=>'非常经常']]],
                ['id'=>4,'text'=>'在过去一个月里，有多少时间能够自信地处理个人问题？','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'从不'],['value'=>3,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>1,'label'=>'相当多时候'],['value'=>0,'label'=>'非常经常']]],
                ['id'=>5,'text'=>'在过去一个月里，有多少时间感觉到事情按照自己的意愿在进行？','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'从不'],['value'=>3,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>1,'label'=>'相当多时候'],['value'=>0,'label'=>'非常经常']]],
                ['id'=>6,'text'=>'在过去一个月里，有多少时间发现自己不能够处理所有必须做的事情？','description'=>'','options'=>[['value'=>0,'label'=>'从不'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'相当多时候'],['value'=>4,'label'=>'非常经常']]],
                ['id'=>7,'text'=>'在过去一个月里，有多少时间能够控制自己生活中的烦恼？','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'从不'],['value'=>3,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>1,'label'=>'相当多时候'],['value'=>0,'label'=>'非常经常']]],
                ['id'=>8,'text'=>'在过去一个月里，有多少时间感觉自己一切都在掌控之中？','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'从不'],['value'=>3,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>1,'label'=>'相当多时候'],['value'=>0,'label'=>'非常经常']]],
                ['id'=>9,'text'=>'在过去一个月里，有多少时间因为事情超出自己的控制能力而生气？','description'=>'','options'=>[['value'=>0,'label'=>'从不'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'相当多时候'],['value'=>4,'label'=>'非常经常']]],
                ['id'=>10,'text'=>'在过去一个月里，有多少时间发觉困难已堆积得太多，以至于无法克服它们？','description'=>'','options'=>[['value'=>0,'label'=>'从不'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'相当多时候'],['value'=>4,'label'=>'非常经常']]]
            ]
        ];
    }

    public static function calculatePSS10($answers) {
        $totalScore = array_sum($answers);

        if ($totalScore <= 13) {
            $level = 'low'; $levelName = '低压力'; $levelColor = '#4CAF50';
            $description = '您的感知压力水平较低，说明您能够较好地适应生活挑战。继续保持！';
            $recommendation = ['保持当前的健康生活方式','继续使用有效的压力管理策略','定期进行自我关怀活动'];
        } elseif ($totalScore <= 26) {
            $level = 'moderate'; $levelName = '中等压力'; $levelColor = '#FF9800';
            $description = '您感知到中等程度的压力。虽然还能应付，但长期如此会影响身心健康。';
            $recommendation = ['学习时间管理和优先级设定','增加体育锻炼，每周至少150分钟','尝试正念冥想或瑜伽','确保充足的睡眠(7-9小时)'];
        } else {
            $level = 'high'; $levelName = '高感知压力'; $levelColor = '#F44336';
            $description = '您的感知压力水平很高。这可能严重影响身心健康，强烈建议采取行动减压。';
            $recommendation = ['认真审视生活优先级，学会说"不"','考虑寻求专业的压力管理指导','建立固定的放松仪式(如睡前冥想)','如果感到不堪重负，请及时求助'];
        }

        return [
            'total_score' => $totalScore, 'max_score' => 40,
            'level' => $level, 'level_name' => $levelName,
            'level_color' => $levelColor, 'description' => $description,
            'recommendation' => $recommendation,
            'dimensions' => [
                ['name'=>'失控感','score'=>(int)$answers[1]+(int)$answers[5]+(int)$answers[9],'max'=>12,'desc'=>'对生活缺乏控制的感觉'],
                ['name'=>'紧张状态','score'=>(int)$answers[0]+(int)$answers[2],'max'=>8,'desc'=>'身心紧张的程度'],
                ['name'=>'应对效能','score'=>(int)$answers[3]+(int)$answers[4]+(int)$answers[6]+(int)$answers[7],'max'=>16,'desc'=>'处理问题的信心和能力'],
                ['name'=>'情绪反应','score'=>(int)$answers[8],'max'=>4,'desc'=>'因失控而产生的愤怒']
            ],
            'percentage' => round(($totalScore / 40) * 100), 'has_risk' => false
        ];
    }

    // ========== UCLA-3 孤独感量表 ==========
    public static function getUCLA3Config() {
        return [
            'id' => 'ucla3', 'name' => 'UCLA-3', 'full_name' => '孤独感量表(简化版)',
            'description' => '评估当前的孤独感和社交连接程度',
            'icon' => "\xf0\x9f\xa4\x9d", 'color' => '#06B6D4',
            'duration' => '约1分钟', 'questions_count' => 3, 'max_score' => 9,
            'category' => '社交健康',
            'questions' => [
                ['id'=>1,'text'=>'您有多常感到缺少陪伴？','description'=>'身边没有人在身边的感觉','options'=>[['value'=>0,'label'=>'从未'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'经常']]],
                ['id'=>2,'text'=>'您有多常感到被孤立？','description'=>'与他人脱节、格格不入的感觉','options'=>[['value'=>0,'label'=>'从未'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'经常']]],
                ['id'=>3,'text'=>'您有多常感到与他人疏远？','description'=>'即使与人在一起也无法真正连接','options'=>[['value'=>0,'label'=>'从未'],['value'=>1,'label'=>'很少'],['value'=>2,'label'=>'有时'],['value'=>3,'label'=>'经常']]]
            ]
        ];
    }

    public static function calculateUCLA3($answers) {
        $totalScore = array_sum($answers);

        if ($totalScore <= 3) {
            $level = 'low'; $levelName = '低孤独感'; $levelColor = '#4CAF50';
            $description = '您的孤独感水平很低，说明您拥有良好的社交支持和人际关系。';
            $recommendation = ['珍惜和维护现有的人际关系','主动关心身边的人','保持开放的沟通态度'];
        } elseif ($totalScore <= 6) {
            $level = 'moderate'; $levelName = '中等孤独感'; $levelColor = '#FF9800';
            $description = '您体验到了一定程度的孤独感。这是现代人常见的感受，可以通过积极行动来改善。';
            $recommendation = ['尝试参加感兴趣的社团或活动','主动联系许久未见的朋友或家人','减少社交媒体的使用时间，增加面对面交流'];
        } else {
            $level = 'high'; $levelName = '高孤独感'; $levelColor = '#EF4444';
            $description = '您报告了较高的孤独感。持续的孤独感可能与心理健康问题相关，建议重视这一点。';
            $recommendation = ['考虑加入互助小组或社区组织','如果孤独感伴随抑郁症状，请寻求专业帮助','培养兴趣爱好以创造社交机会','记住: 感到孤独并不代表你真的孤单'];
        }

        return [
            'total_score' => $totalScore, 'max_score' => 9,
            'level' => $level, 'level_name' => $levelName,
            'level_color' => $levelColor, 'description' => $description,
            'recommendation' => $recommendation,
            'dimensions' => [
                ['name'=>'陪伴缺失','score'=>(int)$answers[0],'max'=>3,'desc'=>'缺少身边人的感觉'],
                ['name'=>'社会隔离','score'=>(int)$answers[1],'max'=>3,'desc'=>'被排斥或孤立的感觉'],
                ['name'=>'情感疏离','score'=>(int)$answers[2],'max'=>3,'desc'=>'与他人无法真正连接']
            ],
            'percentage' => round(($totalScore / 9) * 100), 'has_risk' => false
        ];
    }

    // ========== IAI 社交焦虑量表 ==========
    public static function getIAIConfig() {
        return [
            'id' => 'iai', 'name' => 'IAI-7', 'full_name' => '互动焦虑量表(简化版)',
            'description' => '评估在社交场合中的不适感和恐惧程度',
            'icon' => "\xf0\x9f\x92\xac", 'color' => '#8B5CF6',
            'duration' => '约2分钟', 'questions_count' => 7, 'max_score' => 28,
            'category' => '社交功能',
            'questions' => [
                ['id'=>1,'text'=>'当我和一群人在一起时会感到紧张','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]],
                ['id'=>2,'text'=>'在社交场合中我感到不舒服','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]],
                ['id'=>3,'text'=>'当我被介绍给陌生人时会感到非常紧张','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]],
                ['id'=>4,'text'=>'我在人群中会感到局促不安','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]],
                ['id'=>5,'text'=>'我担心别人如何评价我','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]],
                ['id'=>6,'text'=>'在公共场合发言让我感到焦虑','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]],
                ['id'=>7,'text'=>'我尽量避免成为众人关注的焦点','description'=>'','options'=>[['value'=>0,'label'=>'完全不符'],['value'=>1,'label'=>'稍微有点'],['value'=>2,'label'=>'中等程度'],['value'=>3,'label'=>'非常符合'],['value'=>4,'label'=>'极其符合']]]
            ]
        ];
    }

    public static function calculateIAI($answers) {
        $totalScore = array_sum($answers);

        if ($totalScore <= 11) {
            $level = 'low'; $levelName = '低社交焦虑'; $levelColor = '#4CAF50';
            $description = '您的社交焦虑水平正常。在大多数社交场合中都能感到自在。';
            $recommendation = ['继续保持积极的社交态度','可以适当走出舒适圈尝试新事物'];
        } elseif ($totalScore <= 20) {
            $level = 'moderate'; $levelName = '中等社交焦虑'; $levelColor = '#FF9800';
            $description = '您在某些社交场合中会感到不适。这种程度的焦虑是可以理解和改善的。';
            $recommendation = ['逐步暴露疗法：从小型安全场合开始练习','准备一些开场白话题以减轻尴尬','如果影响到日常生活，考虑寻求专业帮助'];
        } else {
            $level = 'high'; $levelName = '高社交焦虑'; $levelColor = '#8B5CF6';
            $description = '您报告了较高水平的社交焦虑。这可能显著影响您的社交生活和职业发展。';
            $recommendation = ['强烈建议寻求认知行为疗法(CBT)治疗','学习并练习社交技能','了解社交焦虑障碍的相关知识','参加社交焦虑支持小组'];
        }

        $d1 = (int)$answers[0] + (int)$answers[3];
        $d4 = (int)$answers[4] + (int)$answers[5] + (int)$answers[6];

        return [
            'total_score' => $totalScore, 'max_score' => 28,
            'level' => $level, 'level_name' => $levelName,
            'level_color' => $levelColor, 'description' => $description,
            'recommendation' => $recommendation,
            'dimensions' => [
                ['name' => '群体焦虑', 'score' => $d1, 'max' => 8, 'desc' => '在人群中的紧张感'],
                ['name' => '社交不适', 'score' => (int)$answers[1], 'max' => 4, 'desc' => '社交场合的整体不适'],
                ['name' => '陌生人恐惧', 'score' => (int)$answers[2], 'max' => 4, 'desc' => '面对陌生人的焦虑'],
                ['name' => '评价担忧', 'score' => $d4, 'max' => 12, 'desc' => '对他人评价的关注和逃避']
            ],
            'percentage' => round(($totalScore / 28) * 100), 'has_risk' => false
        ];
    }

    // ========== SES 自尊评估量表 ==========
    public static function getSelfEsteemConfig() {
        return [
            'id' => 'self_esteem', 'name' => 'SES-10', 'full_name' => '自尊评估量表',
            'description' => '评估个人的整体自我价值感和自信心水平',
            'icon' => "\xe2\x9c\xa8", 'color' => '#EC4899',
            'duration' => '约2分钟', 'questions_count' => 10, 'max_score' => 40,
            'category' => '自我认知',
            'questions' => [
                ['id'=>1,'text'=>'我觉得我是一个有价值的人，至少与其他人一样','description'=>'','options'=>[['value'=>0,'label'=>'非常不符合'],['value'=>1,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>3,'label'=>'符合'],['value'=>4,'label'=>'非常符合']]],
                ['id'=>2,'text'=>'我觉得我有许多好的品质','description'=>'','options'=>[['value'=>0,'label'=>'非常不符合'],['value'=>1,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>3,'label'=>'符合'],['value'=>4,'label'=>'非常符合']]],
                ['id'=>3,'text'=>'总的来说，我倾向于认为自己是个失败者','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'非常不符合'],['value'=>3,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>1,'label'=>'符合'],['value'=>0,'label'=>'非常符合']]],
                ['id'=>4,'text'=>'我能像大多数人一样把事情做好','description'=>'','options'=>[['value'=>0,'label'=>'非常不符合'],['value'=>1,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>3,'label'=>'符合'],['value'=>4,'label'=>'非常符合']]],
                ['id'=>5,'text'=>'我觉得没有什么值得自豪的地方','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'非常不符合'],['value'=>3,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>1,'label'=>'符合'],['value'=>0,'label'=>'非常符合']]],
                ['id'=>6,'text'=>'我对自己持有积极的态度','description'=>'','options'=>[['value'=>0,'label'=>'非常不符合'],['value'=>1,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>3,'label'=>'符合'],['value'=>4,'label'=>'非常符合']]],
                ['id'=>7,'text'=>'总体而言，我对自已感到满意','description'=>'','options'=>[['value'=>0,'label'=>'非常不符合'],['value'=>1,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>3,'label'=>'符合'],['value'=>4,'label'=>'非常符合']]],
                ['id'=>8,'text'=>'我希望我能更加尊重自己','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'非常不符合'],['value'=>3,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>1,'label'=>'符合'],['value'=>0,'label'=>'非常符合']]],
                ['id'=>9,'text'=>'有时候我确实觉得自己很没用','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'非常不符合'],['value'=>3,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>1,'label'=>'符合'],['value'=>0,'label'=>'非常符合']]],
                ['id'=>10,'text'=>'我认为我是个一无是处的人','description'=>'反向计分题','options'=>[['value'=>4,'label'=>'非常不符合'],['value'=>3,'label'=>'不符合'],['value'=>2,'label'=>'不确定'],['value'=>1,'label'=>'符合'],['value'=>0,'label'=>'非常符合']]]
            ]
        ];
    }

    public static function calculateSelfEsteem($answers) {
        $totalScore = array_sum($answers);

        if ($totalScore >= 31) {
            $level = 'high'; $levelName = '高自尊'; $levelColor = '#4CAF50';
            $description = '您具有较高的自尊水平。您对自己有积极的看法和自信的态度。';
            $recommendation = ['继续保持积极的自我对话','用自己的优势去帮助他人','设定具有挑战性但可实现的目标'];
        } elseif ($totalScore >= 21) {
            $level = 'normal'; $levelName = '正常范围'; $levelColor = '#8BC34A';
            $description = '您的自尊水平在正常范围内。大部分时候您对自己有合理的评价。';
            $recommendation = ['关注自己的优点和成就','接纳不完美的部分也是成长的一部分','与支持你的人保持联系'];
        } elseif ($totalScore >= 15) {
            $level = 'low'; $levelName = '偏低自尊'; $levelColor = '#FF9800';
            $description = '您的自尊水平偏低。您可能经常怀疑自己的价值和能力。';
            $recommendation = ['练习积极的肯定语句和自我对话','列出自己的优点和过去的成就','避免与他人做不公平的比较'];
        } else {
            $level = 'very_low'; $levelName = '低自尊'; $levelColor = '#EC4899';
            $description = '您的自尊水平较低。这可能影响了您的生活满意度和决策。请记住，自尊是可以提升的。';
            $recommendation = ['强烈建议寻求专业心理咨询的帮助','学习认知重构技巧来改变消极思维模式','从小目标开始积累成功经验','记住: 您的价值不由外界评价定义'];
        }

        $negScore = (int)$answers[2] + (int)$answers[4] + (int)$answers[7] + (int)$answers[8] + (int)$answers[9];

        return [
            'total_score' => $totalScore, 'max_score' => 40,
            'level' => $level, 'level_name' => $levelName,
            'level_color' => $levelColor, 'description' => $description,
            'recommendation' => $recommendation,
            'dimensions' => [
                ['name'=>'自我价值感','score'=>(int)$answers[0]+(int)$answers[1],'max'=>8,'desc'=>'对自己的基本价值的认可'],
                ['name'=>'能力信念','score'=>(int)$answers[3],'max'=>4,'desc'=>'相信自己能做好事情的信心'],
                ['name'=>'自我接纳','score'=>(int)$answers[5]+(int)$answers[6],'max'=>8,'desc'=>'对自己的整体满意度'],
                ['name'=>'自我否定倾向','score'=>$negScore,'max'=>20,'desc'=>'消极自我评价的程度']
            ],
            'percentage' => round(($totalScore / 40) * 100), 'has_risk' => false
        ];
    }

    // ========== 数据库操作（通用） ==========
    public static function saveResultToDB($assessmentType, $result, $answers, $duration = null, $userAgent = '') {
        try {
            $db = db();
            $sessionId = session_id();
            $userIp = get_real_ip(); // 使用增强的 IP 获取函数
            $config = self::getAssessment($assessmentType);
            $assessmentName = $config ? $config['full_name'] : $assessmentType;
            $now = Database::now(); // 使用本地时间，避免 SQLite DEFAULT CURRENT_TIMESTAMP 的 UTC 时区问题

            // 尝试包含 user_agent 列（如果已添加）
            try {
                $stmt = $db->prepare("INSERT INTO test_records
                    (session_id, user_ip, total_score, severity_level, severity_description, recommendation, answers, test_duration, assessment_type, user_agent, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $sessionId, $userIp, $result['total_score'], $result['level_name'],
                    $result['description'], json_encode($result['recommendation'], JSON_UNESCAPED_UNICODE),
                    json_encode($answers, JSON_UNESCAPED_UNICODE), $duration, $assessmentType, $userAgent, $now
                ]);
            } catch (PDOException $e) {
                // 回退：user_agent 列不存在时使用旧 SQL
                $stmt = $db->prepare("INSERT INTO test_records
                    (session_id, user_ip, total_score, severity_level, severity_description, recommendation, answers, test_duration, assessment_type, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $sessionId, $userIp, $result['total_score'], $result['level_name'],
                    $result['description'], json_encode($result['recommendation'], JSON_UNESCAPED_UNICODE),
                    json_encode($answers, JSON_UNESCAPED_UNICODE), $duration, $assessmentType, $now
                ]);
            }
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("保存测评结果失败: " . $e->getMessage());
            return false;
        }
    }

    public static function getHistory($limit = 10) {
        try {
            $db = db();
            $sessionId = session_id();
            $stmt = $db->prepare("SELECT * FROM test_records WHERE session_id = ? ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$sessionId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("获取历史记录失败: " . $e->getMessage());
            return [];
        }
    }

    public static function getTrendData() {
        try {
            $db = db();
            $sessionId = session_id();
            $stmt = $db->prepare("SELECT total_score, created_at, assessment_type FROM test_records WHERE session_id = ? ORDER BY created_at ASC");
            $stmt->execute([$sessionId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("获取趋势数据失败: " . $e->getMessage());
            return [];
        }
    }
}

class PHQ9Assessment extends AssessmentManager {}
?>
