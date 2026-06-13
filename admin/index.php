<?php
/**
 * 管理后台主页面
 */
require_once '../config.php';

// 检查是否登录
if (!isset($_SESSION[ADMIN_SESSION_KEY]) || $_SESSION[ADMIN_SESSION_KEY] !== true) {
    header('Location: login.php');
    exit;
}

$adminId = $_SESSION['admin_id'] ?? 0;
$adminName = $_SESSION['admin_username'] ?? '';
$displayName = $_SESSION['admin_display_name'] ?? $adminName;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - 心理健康测评中心</title>
    <style>
        :root { --primary: #4f46e5; --primary-light: #818cf8; --bg: #f8fafc; --card: #ffffff; --border: #e2e8f0; --text: #1e293b; --text-muted: #94a3b8; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; --radius: 12px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg); color: var(--text); line-height: 1.6; }

        /* ===== Navbar ===== */
        .navbar { background: var(--card); border-bottom: 1px solid var(--border); padding: 0 24px; height: 64px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--text); font-weight: 700; font-size: 17px; }
        .nav-brand .logo-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; }
        .nav-right { display: flex; align-items: center; gap: 16px; }
        .nav-user { display: flex; align-items: center; gap: 10px; font-size: 14px; color: var(--text-muted); }
        .nav-user-avatar { width: 34px; height: 34px; background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }

        /* Buttons */
        .btn { padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-family: inherit; }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--text-muted); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-pwd { background: linear-gradient(135deg, #059669, #0891b2); color: white; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all 0.25s; font-family: inherit; }
        .btn-pwd:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(5,150,105,0.35); }

        /* Main */
        .main { max-width: 1400px; margin: 0 auto; padding: 28px 24px; }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .stat-card { background: var(--card); border-radius: var(--radius); padding: 24px; border: 1px solid var(--border); position: relative; overflow: hidden; transition: all 0.25s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        .stat-card::before { content:''; position:absolute; top:0;left:0;width:4px;height:100%; border-radius:4px 0 0 4px; }
        .stat-card:nth-child(1)::before { background:#4f46e5; }
        .stat-card:nth-child(2)::before { background:#10b981; }
        .stat-card:nth-child(3)::before { background:#f59e0b; }
        .stat-card:nth-child(4)::before { background:#ef4444; }
        .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 32px; font-weight: 700; color: var(--text); line-height: 1.2; font-family: 'JetBrains Mono', monospace; }
        .stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }
        .stat-trend-up { color: var(--success); }
        .stat-trend-down { color: var(--danger); }

        /* Content Area */
        .content-area { display: grid; grid-template-columns: 1fr 340px; gap: 24px; }
        @media(max-width:1024px){ .content-area { grid-template-columns: 1fr; } }

        /* Table Section */
        .card { background: var(--card); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
        .card-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead { background: #f8fafc; }
        th { padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
        td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbff; }
        .score-badge { display:inline-block;padding:4px 10px;border-radius:6px;font-weight:600;font-size:12px;font-family:'JetBrains Mono',monospace; }
        .score-low { background:#ecfdf5;color:#059669; }
        .score-mid { background:#fffbeb;color:#d97706; }
        .score-high { background:#fef2f2;color:#dc2626; }
        .type-tag { display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;background:#f1f5f9;color:#475569;font-weight:500; }
        .time-cell { color:var(--text-muted);font-size:12px;white-space:nowrap;font-family:'JetBrains Mono',monospace; }
        .action-btns { display:flex; gap:6px; }

        /* Sidebar Charts */
        .sidebar-section { margin-bottom: 20px; }
        .chart-placeholder { height:200px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:13px;border:1px dashed var(--border); }

        /* Detail Modal */
        .modal-overlay { position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:999;animation:fadeIn 0.2s ease; }
        .modal-overlay.active { display:flex; }
        @keyframes fadeIn { from{opacity:0;} to{opacity:1;} }
        .modal-box { background:white;border-radius:16px;width:90%;max-width:680px;max-height:85vh;overflow-y:auto;padding:32px;position:relative;animation:slideIn 0.3s ease;box-shadow:0 25px 60px rgba(0,0,0,0.2); }
        @keyframes slideIn { from{transform:translateY(20px);opacity:0;} to{transform:translateY(0);opacity:1;} }
        .modal-close { position:absolute;top:16px;right:16px;width:36px;height:36px;border:none;background:#f1f5f9;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--text-muted);transition:all 0.2s; }
        .modal-close:hover { background:#e2e8f0; color: var(--danger); }
        .detail-grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:20px; }
        .detail-item { background:#f8fafc;border-radius:10px;padding:16px; }
        .detail-label { font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px; }
        .detail-value { font-size:16px;font-weight:600; }

        /* Password Modal */
        .pwd-modal-overlay { position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);display:none;align-items:center;justify-content:center;z-index:1000;animation:fadeIn 0.2s ease; }
        .pwd-modal-overlay.active { display:flex; }
        .pwd-modal-box { background:#fff;border-radius:18px;width:92%;max-width:480px;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,0.25);animation:slideIn 0.3s ease; }
        .pwd-modal-header { background:linear-gradient(135deg,#059669,#0891b2);padding:26px 28px;text-align:center;color:#fff; }
        .pwd-modal-header h3 { font-size:19px;font-weight:700;margin-bottom:4px; }
        .pwd-modal-header p { font-size:13px;opacity:0.85; }
        .pwd-modal-body { padding: 28px; }
        .pwd-field { margin-bottom: 18px; }
        .pwd-field label { display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:6px; }
        .pwd-input-wrap { position:relative;display:flex;align-items:center; }
        .pwd-input { width:100%;padding:13px 44px 13px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;transition:border-color 0.2s;font-family:inherit; }
        .pwd-input:focus { border-color:#059669;box-shadow:0 0 0 3px rgba(5,150,105,0.1); }
        .pwd-toggle { position:absolute;right:10px;background:none;border:none;cursor:pointer;color:#94a3b8;padding:6px;font-size:16px;transition:color 0.2s; }
        .pwd-toggle:hover { color:#475569; }
        .pwd-strength { display:flex;gap:4px;margin-top:8px;height:4px; }
        .strength-bar { flex:1;border-radius:2px;background:#e2e8f0;transition:background 0.3s;overflow:hidden; }
        .strength-bar.active-1 { background:#ef4444; }
        .strength-bar.active-2 { background:#f97316; }
        .strength-bar.active-3 { background:#eab308; }
        .strength-bar.active-4 { background:#22c55e; }
        .strength-bar.active-5 { background:#10b981; }
        .pwd-hints { display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:12px; }
        .hint { display:flex;align-items:center;gap:6px;font-size:11px;color:#64748b;padding:4px 0;transition:color 0.2s; }
        .hint.ok { color:#10b981; }
        .hint::before { content:"○";flex-shrink:0;font-size:13px; }
        .hint.ok::before { content:"●"; }
        .field-error { font-size:12px;color:#ef4444;margin-top:4px;display:none; }
        .field-error.show { display:block; }
        .btn-pwd-submit { width:100%;padding:14px;background:linear-gradient(135deg,#059669,#0891b2);color:white;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;transition:all 0.25s;font-family:inherit;letter-spacing:0.3px; }
        .btn-pwd-submit:hover { transform:translateY(-1px);box-shadow:0 6px 20px rgba(5,150,105,0.35); }
        .toast-container { position:fixed;top:20px;right:20px;z-index:9999; }
        .toast { padding:14px 22px;border-radius:10px;font-size:14px;font-weight:500;margin-bottom:10px;animation:toastIn 0.3s ease,toastOut 0.3s 2.7s forwards;min-width:260px;box-shadow:0 8px 30px rgba(0,0,0,0.15); }
        @keyframes toastIn { from{transform:translateX(100%);opacity:0;} to{transform:translateX(0);opacity:1;} }
        @keyframes toastOut { from{transform:translateX(0);opacity:1;} to{transform:translateX(100%);opacity:0;} }
        .toast.success { background:#ecfdf5;color:#065f46;border-left:4px solid #10b981; }
        .toast.error { background:#fef2f2;color:#991b1b;border-left:4px solid #ef4444; }

        /* Loading spinner */
        .loading-spinner { display:inline-block;width:18px;height:18px;border:2px solid #e2e8f0;border-top-color:var(--primary);border-radius:50%;animation:spin 0.6s linear infinite;vertical-align:middle;margin-right:6px; }
        @keyframes spin { to{transform:rotate(360deg)} }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="../index.php" class="nav-brand">
            <div class="logo-icon">M</div>
            <span>心理健康测评中心</span>
        </a>
        <div class="nav-right">
            <div class="nav-user">
                <div class="nav-user-avatar"><?php echo mb_substr($displayName, 0, 1); ?></div>
                <span><?php echo htmlspecialchars($displayName); ?></span>
            </div>
            <button class="btn-pwd" onclick="openPwdModal()">修改密码</button>
            <a href="?action=logout" class="btn btn-outline btn-sm">退出</a>
        </div>
    </nav>

    <main class="main">
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">今日测评</div>
                <div class="stat-value" id="todayCount">--</div>
                <div class="stat-sub">过去24小时内</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">总测评次数</div>
                <div class="stat-value" id="totalCount">--</div>
                <div class="stat-sub">累计数据</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">平均分</div>
                <div class="stat-value" id="avgScore">--</div>
                <div class="stat-sub">最近7天均值</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">高风险占比</div>
                <div class="stat-value" id="highRiskRate">--%</div>
                <div class="stat-sub">中重度以上比例</div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        测评记录
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button onclick="loadRecords()" class="btn btn-outline btn-sm">刷新</button>
                        <button onclick="exportCSV()" class="btn btn-outline btn-sm">导出 CSV</button>
                    </div>
                </div>
                <div class="table-wrap">
                    <table id="recordsTable">
                        <thead><tr>
                            <th>ID</th><th>类型</th><th>得分</th><th>等级</th><th>用时</th><th>提交时间</th><th>操作</th>
                        </tr></thead>
                        <tbody id="recordsBody"><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">加载中...</td></tr></tbody>
                    </table>
                </div>
            </div>

            <!-- Sidebar -->
            <aside>
                <div class="card sidebar-section">
                    <div class="card-header"><div class="card-title">量表分布</div></div>
                    <div id="typeChart" class="chart-placeholder">加载中...</div>
                </div>
                <div class="card sidebar-section">
                    <div class="card-header"><div class="card-title">等级分布</div></div>
                    <div id="levelChart" class="chart-placeholder">加载中...</div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            <h2 style="margin-bottom:20px;">记录详情</h2>
            <div id="detailContent"></div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div class="pwd-modal-overlay" id="pwdModalOverlay">
        <div class="pwd-modal-box">
            <div class="pwd-modal-header">
                <h3>修改密码</h3>
                <p>为了您的账户安全，请定期更换密码</p>
            </div>
            <div class="pwd-modal-body">
                <div class="pwd-field">
                    <label>当前密码</label>
                    <div class="pwd-input-wrap">
                        <input type="password" id="currentPassword" class="pwd-input" placeholder="请输入当前密码">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('currentPassword')">👁</button>
                    </div>
                    <div class="field-error" id="err-current"></div>
                </div>
                <div class="pwd-field">
                    <label>新密码</label>
                    <div class="pwd-input-wrap">
                        <input type="password" id="newPassword" class="pwd-input" placeholder="至少8位，含大小写+数字+特殊字符" oninput="checkPwdStrength(this.value)">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('newPassword')">👁</button>
                    </div>
                    <div class="pwd-strength" id="pwdStrengthBar">
                        <div class="strength-bar"></div><div class="strength-bar"></div><div class="strength-bar"></div><div class="strength-bar"></div><div class="strength-bar"></div>
                    </div>
                    <div class="pwd-hints">
                        <div class="hint" id="h-len">至少8位</div>
                        <div class="hint" id="h-lower">小写字母</div>
                        <div class="hint" id="h-upper">大写字母</div>
                        <div class="hint" id="h-num">数字</div>
                        <div class="hint" id="h-special">特殊字符</div>
                        <div class="hint" id="h-notsame">不同于当前密码</div>
                    </div>
                    <div class="field-error" id="err-new"></div>
                </div>
                <div class="pwd-field">
                    <label>确认新密码</label>
                    <div class="pwd-input-wrap">
                        <input type="password" id="confirmPassword" class="pwd-input" placeholder="再次输入新密码">
                        <button type="button" class="pwd-toggle" onclick="togglePwd('confirmPassword')">👁</button>
                    </div>
                    <div class="field-error" id="err-confirm"></div>
                </div>
                <button class="btn-pwd-submit" onclick="submitPasswordChange()" id="pwdSubmitBtn">确认修改</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        const API_URL = 'api/admin.php';

        // ========== Page Load ==========
        document.addEventListener('DOMContentLoaded', () => {
            loadStats();
            loadRecords();
        });

        // ========== Load Stats ==========
        async function loadStats() {
            try {
                const res = await fetch(API_URL + '?action=get_stats');
                const json = await res.json();
                if (json.success && json.data) {
                    document.getElementById('todayCount').textContent = json.data.today_count;
                    document.getElementById('totalCount').textContent = json.data.total_count;
                    
                    const trend = json.data.trend || [];
                    if (trend.length > 0) {
                        const avg = Math.round(trend.reduce((s,t)=>s+(t.avg||0),0)/trend.length);
                        document.getElementById('avgScore').textContent = avg;
                    }
                    
                    const levels = json.data.by_level || [];
                    const highLevels = levels.filter(l => ['moderate','moderately-severe','severe'].includes(l.severity_level));
                    const totalLvl = levels.reduce((s,l)=>s+l.c,0) || 1;
                    const highRate = Math.round(highLevels.reduce((s,l)=>s+l.c,0) / totalLvl * 100);
                    document.getElementById('highRiskRate').textContent = highRate + '%';

                    renderTypeChart(json.data.by_type || []);
                    renderLevelChart(levels);
                }
            } catch(e) { console.error(e); }
        }

        function renderTypeChart(data) {
            const el = document.getElementById('typeChart');
            if (!data.length) { el.innerHTML='<p>暂无数据</p>'; return; }
            el.innerHTML = data.map(d => 
                `<div style="margin-bottom:10px;"><div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;"><span>${d.assessment_type}</span><span>${d.c}次</span></div>` +
                `<div style="height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;"><div style="width:${Math.min(100,d.c*5)}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;"></div></div></div>`
            ).join('');
        }

        function renderLevelChart(data) {
            const el = document.getElementById('levelChart');
            if (!data.length) { el.innerHTML='<p>暂无数据</p>'; return; }
            el.innerHTML = data.map(d => {
                const colors = {'minimal':'#10b981','mild':'#84cc16','mild-moderate':'#eab308','moderate':'#f59e0b','moderate_severe':'#f97316','severe':'#ef4444'};
                return `<div style="margin-bottom:10px;"><div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;"><span>${d.severity_level}</span><span>${d.c}</span></div>` +
                `<div style="height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;"><div style="width:${Math.min(100,d.c*3)}%;height:100%;background:${colors[d.severity_level]||'#94a3b8'};border-radius:3px;"></div></div></div>`;
            }).join('');
        }

        // ========== Records ==========
        async function loadRecords() {
            const tbody = document.getElementById('recordsBody');
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:40px;"><span class="loading-spinner"></span>加载中...</td></tr>';
            try {
                const res = await fetch(API_URL + '?action=get_records', {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body:JSON.stringify({page:1,page_size:20})
                });
                const json = await res.json();
                if (json.success && json.data.records) {
                    tbody.innerHTML = json.data.records.map(r => {
                        const score = r.total_score;
                        const cls = score<=9?'score-low':score<=14?'score-mid':'score-high';
                        return `<tr>
                            <td style="font-family:'JetBrains Mono',monospace;">${r.id}</td>
                            <td><span class="type-tag">${r.assessment_type}</span></td>
                            <td><span class="score-badge ${cls}">${score}</span></td>
                            <td>${r.severity_level}</td>
                            <td>${r.test_duration?r.test_duration+'s':'-'}</td>
                            <td class="time-cell">${formatTime(r.created_at)}</td>
                            <td class="action-btns">
                                <button class="btn btn-outline btn-sm" onclick="viewRecord(${r.id})">详情</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteRecord(${r.id},this)">删除</button>
                            </td>
                        </tr>`;
                    }).join('');
                }
            } catch(e) { tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:red;">加载失败</td></tr>'; }
        }

        function formatTime(ts) {
            if(!ts)return '-';
            var d = new Date(ts.replace(/-/g,'/'));
            return String(d.getMonth()+1).padStart(2,'0') + '-' +
                   String(d.getDate()).padStart(2,'0') + ' ' +
                   String(d.getHours()).padStart(2,'0') + ':' +
                   String(d.getMinutes()).padStart(2,'0');
        }

        // ========== Record Actions ==========
        async function viewRecord(id) {
            try {
                const res = await fetch(API_URL+'?action=get_record_detail&id='+id);
                const json = await res.json();
                if(json.success){
                    const r=json.data.record;
                    document.getElementById('detailContent').innerHTML=
                        '<div class="detail-grid">'+
                        '<div class="detail-item"><div class="detail-label">记录ID</div><div class="detail-value">'+r.id+'</div></div>'+
                        '<div class="detail-item"><div class="detail-label">测评类型</div><div class="detail-value">'+r.assessment_type+'</div></div>'+
                        '<div class="detail-item"><div class="detail-label">总分</div><div class="detail-value">'+r.total_score+'</div></div>'+
                        '<div class="detail-item"><div class="detail-label">严重程度</div><div class="detail-value">'+r.severity_level+'</div></div>'+
                        '<div class="detail-item"><div class="detail-label">IP地址</div><div class="detail-value">'+r.user_ip+'</div></div>'+
                        '<div class="detail-item"><div class="detail-label">用时</div><div class="detail-value">'+(r.test_duration?r.test_duration+'秒':'未知')+'</div></div>'+
                        '</div>'+
                        '<div class="detail-item" style="grid-column:1/-1;"><div class="detail-label">描述</div><div>'+htmlspecialchars(r.severity_description)+'</div></div>'+
                        '<div class="detail-item" style="grid-column:1/-1;"><div class="detail-label">建议</div><div>'+(Array.isArray(r.recommendation)?r.recommendation.map(x=>'<p style="margin:4px 0;">'+htmlspecialchars(x)+'</p>').join(''):r.recommendation)+'</div></div>';
                    document.getElementById('detailModal').classList.add('active');
                }
            }catch(e){alert('获取详情失败');}
        }
        function closeDetailModal(){document.getElementById('detailModal').classList.remove('active');}

        async function deleteRecord(id,btn){
            if(!confirm('确定要删除这条记录吗？'))return;
            btn.textContent='...';btn.disabled=true;
            try{
                const res=await fetch(API_URL,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=delete_record&id='+id});
                const json=await res.json();
                showToast(json.message,json.success?'success':'error');
                if(json.success)loadRecords();
            }catch(e){showToast('删除失败','error');}
        }

        function exportCSV(){window.location.href=API_URL+'?action=export_csv';}

        function htmlspecialchars(str){return str?String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'):'';}

        // ========== Password Modal ==========
        function openPwdModal(){document.getElementById('pwdModalOverlay').classList.add('active');clearPwdErrors();}
        function closePwdModal(){document.getElementById('pwdModalOverlay').classList.remove('active');}
        document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeDetailModal();closePwdModal();}});

        function togglePwd(id){const el=document.getElementById(id);el.type=el.type==='password'?'text':'password';}
        function clearPwdErrors(){
            document.querySelectorAll('.field-error').forEach(el=>el.classList.remove('show'));
            document.querySelectorAll('.field-error').forEach(el=>el.textContent='');
        }
        function setFieldError(fieldId,msg){const el=document.getElementById('err-'+fieldId);el.textContent=msg;el.classList.add('show');}

        function checkPwdStrength(pwd){
            const rules=[
                {id:'h-len',ok:pwd.length>=8},
                {id:'h-lower',ok:/[a-z]/.test(pwd)},
                {id:'h-upper',ok:/[A-Z]/.test(pwd)},
                {id:'h-num',ok:/[0-9]/.test(pwd)},
                {id:'h-special',ok:/[^a-zA-Z0-9]/.test(pwd)},
                {id:'h-notsame',ok:pwd!==document.getElementById('currentPassword').value}
            ];
            let strength=0;
            rules.forEach(r=>{document.getElementById(r.id).className='hint'+(r.ok?' ok':'');if(r.ok)strength++;});
            const bars=document.querySelectorAll('#pwdStrengthBar .strength-bar');
            bars.forEach((b,i)=>{
                b.className='strength-bar'+(i<strength?' active-'+strength:'');
            });
        }

        async function submitPasswordChange(){
            clearPwdErrors();
            const cur=document.getElementById('currentPassword').value;
            const nw=document.getElementById('newPassword').value;
            const conf=document.getElementById('confirmPassword').value;

            if(cur===''||nw===''||conf===''){
                setFieldError('current',cur==='':'当前密码不能为空');
                setFieldError('new',nw==='':'新密码不能为空');
                setFieldError('confirm',conf==='':'确认密码不能为空');
                return;
            }
            if(nw!==conf){setFieldError('confirm','两次输入的新密码不一致');return;}

            const btn=document.getElementById('pwdSubmitBtn');
            btn.disabled=true;btn.textContent='处理中...';

            try{
                const res=await fetch(API_URL,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
                    body:'action=change_password&current_password='+encodeURIComponent(cur)+'&new_password='+encodeURIComponent(nw)+'&confirm_password='+encodeURIComponent(conf)
                });
                const json=await res.json();
                if(json.success){
                    showToast(json.message,'success');
                    setTimeout(closePwdModal,1500);
                }else{
                    showToast(json.message||'修改失败','error');
                    if(json.data&&json.data.field_errors)setFieldError('new',json.message);
                    if(json.data&&json.data.field)setFieldError(json.data.field,json.message);
                }
            }catch(e){showToast('网络错误','error');}
            finally{btn.disabled=false;btn.textContent='确认修改';}
        }

        // ========== Toast ==========
        function showToast(msg,type){
            var c=document.createElement('div');c.className='toast '+type;c.textContent=msg;
            document.getElementById('toastContainer').appendChild(c);
            setTimeout(()=>c.remove(),3000);
        }

        // Logout handler
        <?php if(isset($_GET['action'])&&$_GET['action']==='logout'){ ?>
        (function(){
            fetch('api/admin.php?action=logout',{credentials:'same-origin'}).then(()=>{
                window.location.href='login.php';
            });
        })();
        <?php } ?>
    </script>
</body>
</html>
