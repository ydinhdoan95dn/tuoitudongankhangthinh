<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * Tool IP Monitor - Chống click tặc Google Ads
 * Theo dõi và chặn IP spam
 */

// Biến lưu kết quả action (để hiển thị thông báo)
$actionResult = null;
$actionIp = null;

// Xử lý actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$ipId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'block' && $ipId > 0) {
    $db->table = "ip_monitor";
    $db->condition = "id = " . $ipId;
    $db->limit = "1";
    $db->order = "";
    $record = $db->select();

    if (!empty($record)) {
        $ipToBlock = $record[0]['ip'];

        $db->table = "ip_blacklist";
        $db->condition = "`ip` = '" . $db->clearText($ipToBlock) . "'";
        $db->limit = "1";
        $db->order = "";
        $existing = $db->select();

        if (empty($existing)) {
            $db->table = "ip_blacklist";
            $db->insert(array(
                'ip' => $ipToBlock,
                'reason' => 'Click spam - blocked from admin',
                'blocked_by' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0
            ));
        }

        $db->table = "ip_monitor";
        $db->condition = "`ip` = '" . $db->clearText($ipToBlock) . "'";
        $db->update(array('is_blocked' => 1));

        $actionResult = 'blocked';
        $actionIp = $ipToBlock;
    } else {
        $actionResult = 'notfound';
    }
}

if ($action === 'unblock' && $ipId > 0) {
    $db->table = "ip_monitor";
    $db->condition = "id = " . $ipId;
    $db->limit = "1";
    $db->order = "";
    $record = $db->select();

    if (!empty($record)) {
        $ipToUnblock = $record[0]['ip'];

        $db->table = "ip_blacklist";
        $db->condition = "`ip` = '" . $db->clearText($ipToUnblock) . "'";
        $db->delete();

        $db->table = "ip_monitor";
        $db->condition = "`ip` = '" . $db->clearText($ipToUnblock) . "'";
        $db->update(array('is_blocked' => 0));

        $actionResult = 'unblocked';
        $actionIp = $ipToUnblock;
    } else {
        $actionResult = 'notfound';
    }
}

// ========================================
// LẤY FILTER & PHÂN TRANG
// ========================================
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filterDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filterSource = isset($_GET['source']) ? $_GET['source'] : 'all';
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$perPage = 50; // Số record mỗi trang
$offset = ($page - 1) * $perPage;

// Build condition
$condition = "visit_date = '" . $db->clearText($filterDate) . "'";

if ($filter === 'suspicious') {
    $condition .= " AND is_suspicious = 1";
} elseif ($filter === 'blocked') {
    $condition .= " AND is_blocked = 1";
}

// Filter theo nguồn
if ($filterSource === 'google') {
    $condition .= " AND referrer LIKE '%google.%'";
} elseif ($filterSource === 'facebook') {
    $condition .= " AND (referrer LIKE '%facebook.%' OR referrer LIKE '%fb.%')";
} elseif ($filterSource === 'direct') {
    $condition .= " AND (referrer IS NULL OR referrer = '')";
} elseif ($filterSource === 'other') {
    $condition .= " AND referrer != '' AND referrer NOT LIKE '%google.%' AND referrer NOT LIKE '%facebook.%' AND referrer NOT LIKE '%fb.%'";
}

// Đếm tổng để phân trang (dùng COUNT query)
$dbConn = $db->connect();
$countQuery = "SELECT COUNT(*) as total FROM " . TTH_DATA_PREFIX . "ip_monitor WHERE " . $condition;
$countResult = mysqli_query($dbConn, $countQuery);
$totalRecords = 0;
if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalRecords = isset($countRow['total']) ? $countRow['total'] : 0;
}
$totalPages = ceil($totalRecords / $perPage);

// Lấy dữ liệu với phân trang
$db->table = "ip_monitor";
$db->condition = $condition;
$db->order = "click_count DESC, last_visit DESC";
$db->limit = $offset . ", " . $perPage;
$ipList = $db->select();

// ========================================
// THỐNG KÊ TỔNG QUAN (dùng SQL aggregate - nhanh hơn)
// ========================================
$statsQuery = "SELECT
    COUNT(*) as total_ips,
    SUM(click_count) as total_clicks,
    SUM(CASE WHEN is_suspicious = 1 THEN 1 ELSE 0 END) as suspicious_count,
    SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked_count,
    SUM(CASE WHEN referrer LIKE '%google.%' THEN 1 ELSE 0 END) as google_count,
    SUM(CASE WHEN referrer LIKE '%facebook.%' OR referrer LIKE '%fb.%' THEN 1 ELSE 0 END) as facebook_count,
    SUM(CASE WHEN referrer IS NULL OR referrer = '' THEN 1 ELSE 0 END) as direct_count
    FROM " . TTH_DATA_PREFIX . "ip_monitor
    WHERE visit_date = '" . $db->clearText($filterDate) . "'";
$statsResult = mysqli_query($dbConn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);
if (!$stats)
    $stats = array();

$totalIPs = isset($stats['total_ips']) ? $stats['total_ips'] : 0;
$totalClicks = isset($stats['total_clicks']) ? $stats['total_clicks'] : 0;
$suspiciousCount = isset($stats['suspicious_count']) ? $stats['suspicious_count'] : 0;
$blockedCount = isset($stats['blocked_count']) ? $stats['blocked_count'] : 0;
$googleCount = isset($stats['google_count']) ? $stats['google_count'] : 0;
$facebookCount = isset($stats['facebook_count']) ? $stats['facebook_count'] : 0;
$directCount = isset($stats['direct_count']) ? $stats['direct_count'] : 0;
$otherCount = $totalIPs - $googleCount - $facebookCount - $directCount;

// Lấy danh sách blacklist
$db->table = "ip_blacklist";
$db->condition = "1";
$db->order = "blocked_at DESC";
$db->limit = "20";
$blacklist = $db->select();

// Build URL cho phân trang
$baseUrl = "?" . TTH_PATH . "=tool_ipdie&date=" . $filterDate . "&filter=" . $filter . "&source=" . $filterSource;
?>

<!-- Menu path -->
<div class="row">
    <ol class="breadcrumb">
        <li><a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li><i class="fa fa-shield"></i> Chống click tặc</li>
    </ol>
</div>

<?php dashboardCoreAdmin(); ?>

<?php if ($actionResult === 'blocked' && $actionIp): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-check-circle"></i> Đã chặn IP: <strong><?= htmlspecialchars($actionIp) ?></strong>
    </div>
<?php elseif ($actionResult === 'unblocked' && $actionIp): ?>
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-unlock"></i> Đã bỏ chặn IP: <strong><?= htmlspecialchars($actionIp) ?></strong>
    </div>
<?php elseif ($actionResult === 'notfound'): ?>
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fa fa-exclamation-triangle"></i> Không tìm thấy IP với ID này
    </div>
<?php endif; ?>

<!-- Filter -->
<div class="row" style="margin-bottom: 15px;">
    <div class="col-lg-12">
        <form method="get" class="form-inline">
            <input type="hidden" name="<?= TTH_PATH ?>" value="tool_ipdie">

            <div class="form-group">
                <label>Ngày:</label>
                <input type="date" name="date" value="<?= $filterDate ?>" class="form-control input-sm"
                    onchange="this.form.submit()">
            </div>

            <!-- Filter trạng thái -->
            <div class="btn-group" style="margin-left: 15px;">
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&source=<?= $filterSource ?>&filter=all"
                    class="btn btn-sm <?= $filter == 'all' ? 'btn-primary' : 'btn-default' ?>">Tất cả</a>
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&source=<?= $filterSource ?>&filter=suspicious"
                    class="btn btn-sm <?= $filter == 'suspicious' ? 'btn-warning' : 'btn-default' ?>">
                    <i class="fa fa-exclamation-triangle"></i> Nghi ngờ (<?= $suspiciousCount ?>)
                </a>
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&source=<?= $filterSource ?>&filter=blocked"
                    class="btn btn-sm <?= $filter == 'blocked' ? 'btn-danger' : 'btn-default' ?>">
                    <i class="fa fa-ban"></i> Đã chặn (<?= $blockedCount ?>)
                </a>
            </div>

            <!-- Filter nguồn -->
            <div class="btn-group" style="margin-left: 15px;">
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&filter=<?= $filter ?>&source=all"
                    class="btn btn-sm <?= $filterSource == 'all' ? 'btn-info' : 'btn-default' ?>">
                    <i class="fa fa-globe"></i> Tất cả nguồn
                </a>
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&filter=<?= $filter ?>&source=google"
                    class="btn btn-sm <?= $filterSource == 'google' ? 'btn-info' : 'btn-default' ?>">
                    <i class="fa fa-google"></i> Google (<?= $googleCount ?>)
                </a>
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&filter=<?= $filter ?>&source=facebook"
                    class="btn btn-sm <?= $filterSource == 'facebook' ? 'btn-info' : 'btn-default' ?>">
                    <i class="fa fa-facebook"></i> Facebook (<?= $facebookCount ?>)
                </a>
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&filter=<?= $filter ?>&source=direct"
                    class="btn btn-sm <?= $filterSource == 'direct' ? 'btn-info' : 'btn-default' ?>">
                    <i class="fa fa-bookmark"></i> Direct (<?= $directCount ?>)
                </a>
                <a href="?<?= TTH_PATH ?>=tool_ipdie&date=<?= $filterDate ?>&filter=<?= $filter ?>&source=other"
                    class="btn btn-sm <?= $filterSource == 'other' ? 'btn-info' : 'btn-default' ?>">
                    <i class="fa fa-link"></i> Khác (<?= $otherCount ?>)
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-users fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($totalIPs) ?></div>
                        <div>Tổng IP</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-mouse-pointer fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($totalClicks) ?></div>
                        <div>Tổng click</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-exclamation-triangle fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($suspiciousCount) ?></div>
                        <div>Nghi ngờ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-red">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-ban fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($blockedCount) ?></div>
                        <div>Đã chặn</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- IP List - Full width -->
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-list fa-fw"></i> Danh sách IP - <?= date('d/m/Y', strtotime($filterDate)) ?>
                <span class="badge"><?= number_format($totalRecords) ?> IP</span>
                <?php if ($totalPages > 1): ?>
                    <span class="pull-right">Trang <?= $page ?>/<?= $totalPages ?></span>
                <?php endif; ?>
            </div>
            <div class="panel-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover table-condensed"
                        style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th style="width: 28%;">IP / User Agent</th>
                                <th class="text-center" style="width: 8%;">Click</th>
                                <th style="width: 12%;">Thời gian</th>
                                <th style="width: 22%;">Nguồn</th>
                                <th class="text-center" style="width: 12%;">Trạng thái</th>
                                <th class="text-center" style="width: 18%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ipList)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted" style="padding: 30px;">Không có dữ liệu
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ipList as $row):
                                    $rowClass = '';
                                    if ($row['is_blocked'])
                                        $rowClass = 'danger';
                                    elseif ($row['is_suspicious'])
                                        $rowClass = 'warning';

                                    // Parse referrer
                                    $refDomain = '';
                                    $refIcon = 'fa-bookmark';
                                    $refLabel = 'Direct';
                                    if (!empty($row['referrer'])) {
                                        $parsed = parse_url($row['referrer']);
                                        $refDomain = isset($parsed['host']) ? $parsed['host'] : '';
                                        if (strpos($refDomain, 'google') !== false) {
                                            $refIcon = 'fa-google';
                                            $refLabel = 'Google';
                                        } elseif (strpos($refDomain, 'facebook') !== false || strpos($refDomain, 'fb.') !== false) {
                                            $refIcon = 'fa-facebook';
                                            $refLabel = 'Facebook';
                                        } else {
                                            $refIcon = 'fa-link';
                                            $refLabel = $refDomain;
                                        }
                                    }
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($row['ip']) ?></strong>
                                            <br><small class="text-muted"
                                                title="<?= htmlspecialchars($row['user_agent']) ?>"><?php echo htmlspecialchars(substr(isset($row['user_agent']) ? $row['user_agent'] : '', 0, 45)) ?>...</small>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="label <?= $row['click_count'] > 50 ? 'label-danger' : ($row['click_count'] > 20 ? 'label-warning' : 'label-default') ?>">
                                                <?= $row['click_count'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <?= date('H:i', strtotime($row['first_visit'])) ?> -
                                                <?= date('H:i', strtotime($row['last_visit'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <i class="fa <?= $refIcon ?>"></i>
                                            <small><?= htmlspecialchars(substr($refLabel, 0, 20)) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['is_blocked']): ?>
                                                <span class="label label-danger"><i class="fa fa-ban"></i> Chặn</span>
                                            <?php elseif ($row['is_suspicious']): ?>
                                                <span class="label label-warning"><i class="fa fa-exclamation"></i> Nghi ngờ</span>
                                            <?php else: ?>
                                                <span class="label label-success"><i class="fa fa-check"></i> OK</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['is_blocked']): ?>
                                                <a href="?<?= TTH_PATH ?>=tool_ipdie&action=unblock&id=<?= $row['id'] ?>&date=<?= $filterDate ?>&source=<?= $filterSource ?>&filter=<?= $filter ?>"
                                                    class="btn btn-xs btn-success"
                                                    onclick="return confirm('Bỏ chặn IP <?= htmlspecialchars($row['ip'], ENT_QUOTES) ?>?')">
                                                    <i class="fa fa-unlock"></i> Bỏ chặn
                                                </a>
                                            <?php else: ?>
                                                <a href="?<?= TTH_PATH ?>=tool_ipdie&action=block&id=<?= $row['id'] ?>&date=<?= $filterDate ?>&source=<?= $filterSource ?>&filter=<?= $filter ?>"
                                                    class="btn btn-xs btn-danger"
                                                    onclick="return confirm('Chặn IP <?= htmlspecialchars($row['ip'], ENT_QUOTES) ?>?')">
                                                    <i class="fa fa-ban"></i> Chặn
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phân trang -->
                <?php if ($totalPages > 1): ?>
                    <div style="padding: 10px; border-top: 1px solid #ddd;">
                        <ul class="pagination pagination-sm" style="margin: 0;">
                            <?php if ($page > 1): ?>
                                <li><a href="<?= $baseUrl ?>&p=1">&laquo; Đầu</a></li>
                                <li><a href="<?= $baseUrl ?>&p=<?= $page - 1 ?>">&lsaquo; Trước</a></li>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                <li class="<?= $i == $page ? 'active' : '' ?>">
                                    <a href="<?= $baseUrl ?>&p=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li><a href="<?= $baseUrl ?>&p=<?= $page + 1 ?>">Sau &rsaquo;</a></li>
                                <li><a href="<?= $baseUrl ?>&p=<?= $totalPages ?>">Cuối &raquo;</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Blacklist - Col 6 -->
    <div class="col-lg-6">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <i class="fa fa-ban fa-fw"></i> IP đã chặn (<?= count($blacklist) ?>)
            </div>
            <div class="panel-body" style="max-height: 280px; overflow-y: auto; padding: 10px;">
                <?php if (empty($blacklist)): ?>
                    <p class="text-muted text-center">Chưa có IP nào bị chặn</p>
                <?php else: ?>
                    <table class="table table-condensed table-striped" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Lý do</th>
                                <th class="text-right">Ngày chặn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blacklist as $bl): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($bl['ip']) ?></code></td>
                                    <td><small
                                            class="text-muted"><?php echo htmlspecialchars(substr(isset($bl['reason']) ? $bl['reason'] : '', 0, 35)) ?></small>
                                    </td>
                                    <td class="text-right"><small><?= date('d/m/Y H:i', strtotime($bl['blocked_at'])) ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Thống kê & Hướng dẫn - Col 6 -->
    <div class="col-lg-6">
        <div class="row">
            <div class="col-md-6">
                <!-- Thống kê nguồn -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <i class="fa fa-pie-chart fa-fw"></i> Thống kê nguồn
                    </div>
                    <div class="panel-body" style="padding: 10px;">
                        <table class="table table-condensed" style="margin-bottom: 0;">
                            <tr>
                                <td><i class="fa fa-google text-danger"></i> Google</td>
                                <td class="text-right"><strong><?= number_format($googleCount) ?></strong></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-facebook text-primary"></i> Facebook</td>
                                <td class="text-right"><strong><?= number_format($facebookCount) ?></strong></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-bookmark text-success"></i> Direct</td>
                                <td class="text-right"><strong><?= number_format($directCount) ?></strong></td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-link text-warning"></i> Khác</td>
                                <td class="text-right"><strong><?= number_format($otherCount) ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Hướng dẫn -->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <i class="fa fa-info-circle fa-fw"></i> Hướng dẫn
                    </div>
                    <div class="panel-body" style="padding: 10px; font-size: 12px;">
                        <ul style="padding-left: 15px; margin-bottom: 0;    line-height: 30px;">
                            <li><span class="label label-warning">Nghi ngờ</span> &gt;50 click/ngày</li>
                            <li><span class="label label-danger">Đã chặn</span> trong blacklist</li>
                            <li><i class="fa fa-google"></i> IP từ Google Ads</li>
                            <li><i class="fa fa-facebook"></i> IP từ Facebook</li>
                        </ul>
                        <hr style="margin: 8px 0;">
                        <p class="text-muted" style="margin-bottom: 0;">
                            <strong>Mẹo:</strong> Lọc <strong>Google</strong> để tìm click tặc.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .panel-heading .huge {
        font-size: 24px;
        font-weight: bold;
    }

    .panel-primary .panel-heading {
        background-color: #337ab7;
        color: #fff;
    }

    .panel-green .panel-heading {
        background-color: #5cb85c;
        color: #fff;
    }

    .panel-yellow .panel-heading {
        background-color: #f0ad4e;
        color: #fff;
    }

    .panel-red .panel-heading {
        background-color: #d9534f;
        color: #fff;
    }

    .panel-primary .panel-heading i,
    .panel-green .panel-heading i,
    .panel-yellow .panel-heading i,
    .panel-red .panel-heading i {
        opacity: 0.5;
    }

    tr.warning {
        background-color: #fcf8e3 !important;
    }

    tr.danger {
        background-color: #f2dede !important;
    }

    .table-condensed>tbody>tr>td {
        padding: 5px 8px;
        vertical-align: middle;
    }

    .pagination {
        display: inline-flex;
    }
</style>