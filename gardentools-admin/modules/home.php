<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}
// DEBUG MODE - comment out after fixing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// DATA COLLECTION FOR DASHBOARD
// ============================================================

$today = date('Y-m-d');
$todayStart = strtotime($today);
$todayEnd = strtotime($today . ' 23:59:59');
$date = new DateClass();

// --- LEADS DATA ---
$db->table = "contact";
$db->condition = "";
$db->order = "created_time DESC";
$db->limit = "";
$db->select();
$totalLeads = $db->RowCount;

// Leads mới (chưa xem)
$db->table = "contact";
$db->condition = "is_active = 1";
$db->select();
$newLeads = $db->RowCount;

// Leads hôm nay (created_time là int timestamp)
$db->table = "contact";
$db->condition = "created_time >= " . $todayStart . " AND created_time <= " . $todayEnd;
$db->select();
$todayLeads = $db->RowCount;

// Leads 7 ngày gần đây
$weekAgo = strtotime('-7 days');
$db->table = "contact";
$db->condition = "created_time >= " . $weekAgo;
$db->select();
$weekLeads = $db->RowCount;

// Lead by Source (page_slug)
$db->table = "contact";
$db->condition = "page_slug IS NOT NULL AND page_slug != ''";
$db->order = "";
$db->limit = "";
$leadSources = $db->select();

$sourceStats = array();
if (!empty($leadSources)) {
    foreach ($leadSources as $lead) {
        $slug = isset($lead['page_slug']) ? trim($lead['page_slug']) : '';
        if (empty($slug))
            continue;

        // Phân loại nguồn
        $sourceKey = 'other';
        $sourceName = 'Khác';

        if (strpos($slug, 'du-an') !== false || strpos($slug, 'project') !== false) {
            $sourceKey = 'project';
            $sourceName = 'Dự án';
        } elseif (strpos($slug, 'can-ho') !== false || strpos($slug, 'san-pham') !== false || strpos($slug, 'product') !== false) {
            $sourceKey = 'product';
            $sourceName = 'Sản phẩm';
        } elseif (strpos($slug, 'bai-viet') !== false || strpos($slug, 'tin-tuc') !== false || strpos($slug, 'article') !== false) {
            $sourceKey = 'article';
            $sourceName = 'Bài viết';
        } elseif (strpos($slug, 'lien-he') !== false || strpos($slug, 'contact') !== false) {
            $sourceKey = 'contact';
            $sourceName = 'Trang liên hệ';
        } elseif ($slug == '/' || $slug == '' || strpos($slug, 'home') !== false) {
            $sourceKey = 'homepage';
            $sourceName = 'Trang chủ';
        }

        if (!isset($sourceStats[$sourceKey])) {
            $sourceStats[$sourceKey] = array('name' => $sourceName, 'count' => 0);
        }
        $sourceStats[$sourceKey]['count']++;
    }
} // end if(!empty($leadSources))

// Recent leads (5 leads gần nhất)
$db->table = "contact";
$db->condition = "";
$db->order = "created_time DESC";
$db->limit = "5";
$recentLeads = $db->select();

// --- TRAFFIC DATA (Modern Analytics) ---
$todayTraffic = 0;
$weekTraffic = 0;
$onlineNow = 0;
$fraudAlertCount = 0;
$blockedIPCount = 0;

// Load Analytics
if (file_exists(_F_CLASSES . DS . 'Analytics.php')) {
    require_once(_F_CLASSES . DS . 'Analytics.php');
}

if (class_exists('Analytics')) {
    try {
        $analyticsData = Analytics::getSummary();
        $todayTraffic = isset($analyticsData['today_views']) ? $analyticsData['today_views'] : 0;

        // Week Traffic
        $trafficOverview = Analytics::getTrafficOverview(7);
        if (!empty($trafficOverview['daily_views'])) {
            foreach ($trafficOverview['daily_views'] as $d) {
                $weekTraffic += $d['views'];
            }
        }
    } catch (Exception $e) {
    }
}

// --- ONLINE USERS ---
try {
    $db->table = "online";
    $db->condition = "1=1";
    $db->select();
    $onlineNow = $db->RowCount;
} catch (Exception $e) {
}

// --- FRAUD / BLACKLIST ---
try {
    $db->table = "ip_blacklist";
    $db->condition = "";
    $db->select();
    $blockedIPCount = $db->RowCount;

    $db->table = "ip_monitor";
    $db->condition = "is_suspicious = 1";
    $db->select();
    $fraudAlertCount = $db->RowCount;
} catch (Exception $e) {
}

// --- CONVERSION RATE ---
$conversionRate = ($weekTraffic > 0) ? round(($weekLeads / $weekTraffic) * 100, 2) : 0;

// --- ARTICLES & PROJECTS ---
$totalArticles = function_exists('getTotalArticle') ? getTotalArticle() : 0;
$totalHits = function_exists('getTotalHits') ? getTotalHits() : 0;
?>

<!-- Menu path -->
<div class="row">
    <ol class="breadcrumb">
        <li>
            <i class="fa fa-home"></i> Trang chủ - Dashboard
        </li>
    </ol>
</div>
<!-- /.row -->

<?php // dashboardCoreAdmin(); // Trang home không cần kiểm tra quyền ?>

<!-- Dashboard Styles -->
<style>
    .dash-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 20px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .dash-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .dash-card-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dash-card-header h4 {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }

    .dash-card-body {
        padding: 20px;
    }

    .dash-card-footer {
        padding: 12px 20px;
        background: #f8f9fa;
        border-top: 1px solid #eee;
        text-align: right;
    }

    /* Stat Cards */
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .stat-card.leads {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card.traffic {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-card.online {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .stat-card.conversion {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(30deg);
    }

    .stat-card-icon {
        font-size: 40px;
        opacity: 0.3;
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .stat-card-value {
        font-size: 36px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-card-label {
        font-size: 13px;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-card-sub {
        font-size: 12px;
        margin-top: 10px;
        opacity: 0.8;
    }

    .stat-card-sub strong {
        color: #fff;
    }

    /* Alert Badge */
    .alert-badge {
        display: inline-flex;
        align-items: center;
        background: #dc3545;
        color: #fff;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .alert-badge.warning {
        background: #ffc107;
        color: #333;
    }

    .alert-badge.success {
        background: #28a745;
    }

    .alert-badge.info {
        background: #17a2b8;
    }

    /* Lead Item */
    .lead-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .lead-item:last-child {
        border-bottom: none;
    }

    .lead-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
        margin-right: 12px;
    }

    .lead-info {
        flex: 1;
    }

    .lead-name {
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .lead-phone {
        color: #666;
        font-size: 12px;
        font-family: monospace;
    }

    .lead-time {
        color: #999;
        font-size: 11px;
    }

    .lead-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .lead-status.new {
        background: #fff3cd;
        color: #856404;
    }

    .lead-status.viewed {
        background: #d4edda;
        color: #155724;
    }

    /* IP Alert Item */
    .ip-alert-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .ip-alert-item:last-child {
        border-bottom: none;
    }

    .ip-address {
        font-family: monospace;
        font-weight: 600;
        color: #dc3545;
    }

    .ip-clicks {
        margin-left: auto;
        background: #ffeaea;
        color: #dc3545;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Source Chart Legend */
    .source-legend {
        display: flex;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .source-legend-item {
        display: flex;
        align-items: center;
        margin-right: 15px;
        margin-bottom: 8px;
        font-size: 12px;
    }

    .source-legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        margin-right: 6px;
    }

    /* Button styles */
    .btn-detail {
        background: transparent;
        border: 1px solid #ddd;
        color: #666;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        transition: all 0.2s;
    }

    .btn-detail:hover {
        background: #667eea;
        border-color: #667eea;
        color: #fff;
    }

    /* Quick Actions */
    .quick-action {
        display: flex;
        align-items: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .quick-action:hover {
        background: #667eea;
        color: #fff;
    }

    .quick-action:hover .quick-action-icon {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .quick-action-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
        color: #667eea;
    }

    .quick-action-text {
        font-weight: 500;
        font-size: 13px;
    }

    /* Mobile responsive for stat cards */
    @media (max-width: 767px) {
        .stat-card {
            padding: 12px 15px;
            margin-bottom: 10px;
        }

        .stat-card-value {
            font-size: 24px;
        }

        .stat-card-label {
            font-size: 11px;
        }

        .stat-card-sub {
            font-size: 10px;
            margin-top: 5px;
        }

        .stat-card-icon {
            font-size: 28px;
            right: 10px;
            top: 10px;
        }
    }
</style>

<!-- ROW 1: Summary Stats -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
        <div class="stat-card leads">
            <i class="fa fa-users stat-card-icon"></i>
            <div class="stat-card-value"><?= number_format($newLeads) ?></div>
            <div class="stat-card-label">Lead chưa xem</div>
            <div class="stat-card-sub">
                +<?= $todayLeads ?> hôm nay | <?= $totalLeads ?> tổng
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
        <div class="stat-card traffic">
            <i class="fa fa-eye stat-card-icon"></i>
            <div class="stat-card-value"><?= number_format($todayTraffic) ?></div>
            <div class="stat-card-label">Lượt xem hôm nay</div>
            <div class="stat-card-sub">
                <?= number_format($weekTraffic) ?> lượt / 7 ngày
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
        <div class="stat-card online">
            <i class="fa fa-circle stat-card-icon"></i>
            <div class="stat-card-value"><?= number_format($onlineNow) ?></div>
            <div class="stat-card-label">Đang online</div>
            <div class="stat-card-sub">
                <?= number_format($totalHits) ?> tổng lượt xem
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
        <div class="stat-card conversion">
            <i class="fa fa-line-chart stat-card-icon"></i>
            <div class="stat-card-value"><?= $conversionRate ?>%</div>
            <div class="stat-card-label">Tỷ lệ chuyển đổi</div>
            <div class="stat-card-sub">
                <?= $weekLeads ?> lead / <?= number_format($weekTraffic) ?> view
            </div>
        </div>
    </div>
</div>

<!-- ROW 2: Traffic Chart + Lead Sources -->
<div class="row">
    <!-- Traffic Chart -->
    <div class="col-lg-12">
        <div class="dash-card">
            <div class="dash-card-header">
                <h4><i class="fa fa-area-chart"></i> Lưu lượng truy cập 7 ngày</h4>
                <span class="alert-badge info"><?= count($trafficChartLabels) ?> ngày dữ liệu</span>
            </div>
            <div class="dash-card-body">
                <?php if (empty($trafficChartLabels)): ?>
                    <p class="text-muted text-center" style="padding: 50px 0;">
                        <i class="fa fa-info-circle fa-2x"></i><br><br>
                        Chưa có dữ liệu thống kê tuần này.
                    </p>
                <?php else: ?>
                    <canvas id="trafficChart" height="100"></canvas>
                <?php endif; ?>
            </div>
            <div class="dash-card-footer">
                <a href="?<?= TTH_PATH ?>=tool_analytics" class="btn btn-detail">
                    <i class="fa fa-bar-chart"></i> Xem chi tiết thống kê
                </a>
            </div>
        </div>
    </div>

    <!-- Lead by Source -->

</div>

<!-- ROW 3: Recent Leads + Fraud Alert + Quick Actions -->
<div class="row">
    <div class="col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <h4><i class="fa fa-pie-chart"></i> Lead theo nguồn</h4>
                <span class="alert-badge success"><?= count($leadSources) ?> lead</span>
            </div>
            <div class="dash-card-body">
                <?php if (empty($sourceStats)): ?>
                    <p class="text-muted text-center" style="padding: 30px 0;">
                        Chưa có dữ liệu nguồn lead.
                    </p>
                <?php else: ?>
                    <canvas id="sourceChart" height="180"></canvas>
                    <div class="source-legend">
                        <?php
                        $colors = array(
                            'project' => '#667eea',
                            'product' => '#f5576c',
                            'article' => '#f5576c',
                            'contact' => '#ffc107',
                            'homepage' => '#17a2b8',
                            'other' => '#6c757d'
                        );
                        foreach ($sourceStats as $key => $stat): ?>
                            <div class="source-legend-item">
                                <div class="source-legend-color"
                                    style="background: <?= isset($colors[$key]) ? $colors[$key] : '#6c757d' ?>"></div>
                                <?= $stat['name'] ?>: <?= $stat['count'] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="dash-card-footer">
                <a href="?<?= TTH_PATH ?>=contact_list" class="btn btn-detail">
                    <i class="fa fa-list"></i> Xem tất cả lead
                </a>
            </div>
        </div>
    </div>
    <!-- Recent Leads -->
    <div class="col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <h4><i class="fa fa-users"></i> Lead gần đây</h4>
                <?php if ($newLeads > 0): ?>
                    <span class="alert-badge"><?= $newLeads ?> chưa xem</span>
                <?php endif; ?>
            </div>
            <div class="dash-card-body">
                <?php if (empty($recentLeads)): ?>
                    <p class="text-muted text-center" style="padding: 30px 0;">
                        Chưa có lead nào.
                    </p>
                <?php else: ?>
                    <?php foreach ($recentLeads as $lead):
                        $initials = mb_substr($lead['name'], 0, 1, 'UTF-8');
                        ?>
                        <div class="lead-item">
                            <div class="lead-avatar"><?= strtoupper($initials) ?></div>
                            <div class="lead-info">
                                <div class="lead-name"><?= stripslashes($lead['name']) ?></div>
                                <div class="lead-phone"><?= stripslashes($lead['phone']) ?></div>
                                <div class="lead-time"><?= $date->vnDateTime($lead['created_time']) ?></div>
                            </div>
                            <span class="lead-status <?= ($lead['is_active'] + 0 == 1) ? 'new' : 'viewed' ?>">
                                <?= ($lead['is_active'] + 0 == 1) ? 'Mới' : 'Đã xem' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="dash-card-footer">
                <a href="?<?= TTH_PATH ?>=contact_list" class="btn btn-detail">
                    <i class="fa fa-envelope"></i> Quản lý Lead
                </a>
            </div>
        </div>
    </div>

    <!-- Fraud Alert -->
    <div class="col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <h4><i class="fa fa-shield"></i> Cảnh báo Click Fraud</h4>
                <?php if ($fraudAlertCount > 0): ?>
                    <span class="alert-badge"><?= $fraudAlertCount ?> IP nghi ngờ</span>
                <?php else: ?>
                    <span class="alert-badge success">An toàn</span>
                <?php endif; ?>
            </div>
            <div class="dash-card-body">
                <?php if (empty($suspiciousIPs)): ?>
                    <p class="text-muted text-center" style="padding: 30px 0;">
                        <i class="fa fa-check-circle fa-2x text-success"></i><br><br>
                        Không phát hiện IP nghi ngờ.<br>
                        <small>Các IP click >50 lần sẽ được cảnh báo</small>
                    </p>
                <?php else: ?>
                    <?php foreach ($suspiciousIPs as $ip): ?>
                        <div class="ip-alert-item">
                            <div>
                                <div class="ip-address"><?= $ip['ip'] ?></div>
                                <div style="font-size:11px;color:#999;">
                                    <?php if (!empty($ip['last_visit'])): ?>
                                        Lần cuối: <?= date('d/m/Y H:i', strtotime($ip['last_visit'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="ip-clicks"><?= $ip['click_count'] ?> clicks</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($blockedIPCount > 0): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                        <small class="text-muted">
                            <i class="fa fa-ban"></i> Đã chặn: <strong><?= $blockedIPCount ?></strong> IP
                        </small>
                    </div>
                <?php endif; ?>
            </div>
            <div class="dash-card-footer">
                <a href="?<?= TTH_PATH ?>=tool_ipdie" class="btn btn-detail">
                    <i class="fa fa-ban"></i> Quản lý IP cấm
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <!-- <div class="col-lg-3">
        <div class="dash-card">
            <div class="dash-card-header">
                <h4><i class="fa fa-bolt"></i> Thao tác nhanh</h4>
            </div>
            <div class="dash-card-body">
                <div class="quick-action" onclick="Forward('?<?= TTH_PATH ?>=article_add');">
                    <div class="quick-action-icon"><i class="fa fa-plus"></i></div>
                    <div class="quick-action-text">Thêm bài viết</div>
                </div>
                <div class="quick-action" onclick="Forward('?<?= TTH_PATH ?>=project_add');">
                    <div class="quick-action-icon"><i class="fa fa-building"></i></div>
                    <div class="quick-action-text">Thêm dự án</div>
                </div>
                <div class="quick-action" onclick="Forward('?<?= TTH_PATH ?>=product_add');">
                    <div class="quick-action-icon"><i class="fa fa-home"></i></div>
                    <div class="quick-action-text">Thêm sản phẩm</div>
                </div>
                <div class="quick-action" onclick="Forward('?<?= TTH_PATH ?>=config_general');">
                    <div class="quick-action-icon"><i class="fa fa-cog"></i></div>
                    <div class="quick-action-text">Cấu hình chung</div>
                </div>
                <div class="quick-action" onclick="Forward('?<?= TTH_PATH ?>=backup_data');">
                    <div class="quick-action-icon"><i class="fa fa-database"></i></div>
                    <div class="quick-action-text">Sao lưu dữ liệu</div>
                </div>
            </div>
        </div>
    </div> -->
</div>

<!-- ROW 4: Monthly Traffic Chart (giữ lại từ cũ) -->
<div class="row">
    <div class="col-lg-12">
        <div class="dash-card">
            <div class="dash-card-header">
                <h4><i class="fa fa-bar-chart-o"></i> Biểu đồ thống kê truy cập tháng</h4>
                <?php
                $t_month = strtotime(date('Y-m', time()));
                $monthStatic = isset($_GET['month']) ? $_GET['month'] : date('Y-m', $t_month);
                ?>
                <select onchange="return onChangeForward()" id="monthStatistic" class="form-control"
                    style="width: auto; display: inline-block;">
                    <?php for ($i = 0; $i <= 10; $i++): ?>
                        <option value="<?= date('Y-m', strtotime('-' . $i . ' month', $t_month)) ?>"
                            <?= ($monthStatic == date('Y-m', strtotime('-' . $i . ' month', $t_month))) ? "selected" : "" ?>>
                            <?= date('m/Y', strtotime('-' . $i . ' month', $t_month)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="dash-card-body">
                <script type="text/javascript">
                    var domain = location.hostname;
                    $(function () {
                        $.getJSON('<?= ADMIN_DIR ?>/data_charts_visitor.php?month=<?= $monthStatic ?>&callback=?', function (csv) {
                            $('#container').highcharts({
                                data: { csv: csv },
                                title: { text: 'Thống kê truy cập website ' + domain },
                                subtitle: { text: '(theo tháng)' },
                                xAxis: {
                                    tickInterval: 5 * 24 * 3600 * 1000,
                                    tickWidth: 0,
                                    gridLineWidth: 1,
                                    labels: { align: 'left', x: 3, y: -3 }
                                },
                                yAxis: [{
                                    title: { text: null },
                                    labels: { align: 'left', x: 3, y: 16, format: '{value:.,0f}' },
                                    showFirstLabel: false
                                }, {
                                    linkedTo: 0,
                                    gridLineWidth: 0,
                                    opposite: true,
                                    title: { text: null },
                                    labels: { align: 'right', x: -3, y: 16, format: '{value:.,0f}' },
                                    showFirstLabel: false
                                }],
                                legend: { align: 'left', verticalAlign: 'top', y: 20, floating: true, borderWidth: 0 },
                                tooltip: { shared: true, crosshairs: true },
                                plotOptions: {
                                    series: {
                                        cursor: 'pointer',
                                        marker: { lineWidth: 1 }
                                    }
                                },
                                series: [{
                                    name: 'Tất cả truy cập',
                                    lineWidth: 4,
                                    marker: { radius: 4 }
                                }]
                            });
                        });
                    });
                </script>
                <script type="text/javascript" src="./js/highcharts/highcharts.js"></script>
                <script src="./js/highcharts/modules/data.js"></script>
                <script src="./js/highcharts/modules/exporting.js"></script>
                <script type="text/javascript" src="./js/highcharts/themes/tth-v2.js" charset="utf-8"></script>
                <div id="container" style="height: 350px; margin: 0 auto;"></div>
            </div>
            <div class="dash-card-footer">
                <a href="?<?= TTH_PATH ?>=tool_analytics" class="btn btn-detail">
                    <i class="fa fa-bar-chart"></i> Xem phân tích chi tiết
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (getConstant('google_calendar') != ''): ?>
    <!-- Google Calendar -->
    <div class="row">
        <div class="col-lg-12">
            <div class="dash-card">
                <div class="dash-card-header">
                    <h4><i class="fa fa-calendar"></i> Lịch Google</h4>
                </div>
                <div class="dash-card-body">
                    <iframe
                        src="https://www.google.com/calendar/embed?src=<?= getConstant('google_calendar') ?>&ctz=Asia/Saigon"
                        style="border: 0" width="100%" height="500" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Chart.js for Dashboard Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (!empty($trafficChartLabels)): ?>
            // Traffic Line Chart
            new Chart(document.getElementById('trafficChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($trafficChartLabels) ?>,
                    datasets: [{
                        label: 'Lượt xem',
                        data: <?= json_encode($trafficChartData) ?>,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        <?php endif; ?>

        <?php if (!empty($sourceStats)): ?>
            // Source Pie Chart
            new Chart(document.getElementById('sourceChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: [<?php echo implode(',', array_map(function ($s) {
                        return "'" . $s['name'] . "'";
                    }, $sourceStats)); ?>],
                    datasets: [{
                        data: [<?php echo implode(',', array_map(function ($s) {
                            return $s['count'];
                        }, $sourceStats)); ?>],
                        backgroundColor: [
                            <?php
                            $chartColors = array('#667eea', '#f5576c', '#43e97b', '#ffc107', '#17a2b8', '#6c757d');
                            $i = 0;
                            foreach ($sourceStats as $key => $stat) {
                                echo "'" . $chartColors[$i % count($chartColors)] . "'";
                                if ($i < count($sourceStats) - 1)
                                    echo ",";
                                $i++;
                            }
                            ?>
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } }
                }
            });
        <?php endif; ?>
    });

    function onChangeForward() {
        var url = '?month=' + document.getElementById("monthStatistic").value;
        return Forward(url);
    }
</script>