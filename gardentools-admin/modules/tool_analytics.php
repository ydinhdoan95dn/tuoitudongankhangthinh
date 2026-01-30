<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

// Get date range
$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
$validDays = array(7, 30, 90, 365);
if (!in_array($days, $validDays)) {
    $days = 30;
}

// Lấy dữ liệu từ bảng online_daily
$startDate = date('Y-m-d', strtotime("-{$days} days"));
$db->table = "online_daily";
$db->condition = "`date` >= '" . $startDate . "'";
$db->order = "`date` ASC";
$db->limit = "";
$dailyData = $db->select();

// Tính toán summary
$totalViews = 0;
$todayViews = 0;
$totalDesktop = 0;
$totalMobile = 0;
$totalTablet = 0;
$totalDirect = 0;
$totalOrganic = 0;
$totalSocial = 0;
$totalReferral = 0;

$chartLabels = array();
$chartViews = array();
$today = date('Y-m-d');

foreach ($dailyData as $row) {
    $totalViews += isset($row['count']) ? $row['count'] : 0;
    $totalDesktop += isset($row['desktop']) ? $row['desktop'] : 0;
    $totalMobile += isset($row['mobile']) ? $row['mobile'] : 0;
    $totalTablet += isset($row['tablet']) ? $row['tablet'] : 0;
    $totalDirect += isset($row['direct']) ? $row['direct'] : 0;
    $totalOrganic += isset($row['organic']) ? $row['organic'] : 0;
    $totalSocial += isset($row['social']) ? $row['social'] : 0;
    $totalReferral += isset($row['referral']) ? $row['referral'] : 0;

    if ($row['date'] === $today) {
        $todayViews = isset($row['count']) ? $row['count'] : 0;
    }

    $chartLabels[] = date('d/m', strtotime($row['date']));
    $chartViews[] = intval(isset($row['count']) ? $row['count'] : 0);
}

$activeDays = count($dailyData);
$avgDaily = $activeDays > 0 ? round($totalViews / $activeDays) : 0;

// Tính phần trăm thiết bị
$totalDevices = $totalDesktop + $totalMobile + $totalTablet;
$pctDesktop = $totalDevices > 0 ? round($totalDesktop / $totalDevices * 100, 1) : 0;
$pctMobile = $totalDevices > 0 ? round($totalMobile / $totalDevices * 100, 1) : 0;
$pctTablet = $totalDevices > 0 ? round($totalTablet / $totalDevices * 100, 1) : 0;

// Tính phần trăm nguồn
$totalSources = $totalDirect + $totalOrganic + $totalSocial + $totalReferral;
$pctDirect = $totalSources > 0 ? round($totalDirect / $totalSources * 100, 1) : 0;
$pctOrganic = $totalSources > 0 ? round($totalOrganic / $totalSources * 100, 1) : 0;
$pctSocial = $totalSources > 0 ? round($totalSocial / $totalSources * 100, 1) : 0;
$pctReferral = $totalSources > 0 ? round($totalReferral / $totalSources * 100, 1) : 0;

// Đếm user đang online
$db->table = "online";
$db->condition = "1";
$db->order = "";
$db->limit = "";
$db->select();
$onlineNow = $db->RowCount;
?>
<!-- Menu path -->
<div class="row">
    <ol class="breadcrumb">
        <li><a href="<?= ADMIN_DIR ?>"><i class="fa fa-home"></i> Trang chủ</a></li>
        <li><i class="fa fa-bar-chart"></i> Thống kê truy cập</li>
    </ol>
</div>

<?php dashboardCoreAdmin(); ?>

<!-- Date Range Filter -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-lg-12">
        <div class="btn-group">
            <?php foreach ($validDays as $d): ?>
                <a href="?<?= TTH_PATH ?>=tool_analytics&days=<?= $d ?>"
                    class="btn <?= $days == $d ? 'btn-primary' : 'btn-default' ?>">
                    <?= $d ?> ngày
                </a>
            <?php endforeach; ?>
        </div>
        <span class="label label-success" style="margin-left: 15px; font-size: 14px;">
            <i class="fa fa-circle"></i> <?= $onlineNow ?> đang online
        </span>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-eye fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($totalViews) ?></div>
                        <div>Tổng lượt xem</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-calendar-o fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($todayViews) ?></div>
                        <div>Hôm nay</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-line-chart fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= number_format($avgDaily) ?></div>
                        <div>Trung bình/ngày</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-red">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3"><i class="fa fa-calendar fa-4x"></i></div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?= $activeDays ?></div>
                        <div>Ngày có dữ liệu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Traffic Chart - Full Width Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-line-chart fa-fw"></i> Biểu đồ lượt truy cập <?= $days ?> ngày qua
            </div>
            <div class="panel-body">
                <?php if (empty($chartLabels)): ?>
                    <p class="text-muted text-center" style="padding: 50px 0;">
                        <i class="fa fa-info-circle fa-2x"></i><br><br>
                        Chưa có dữ liệu thống kê.<br>
                        Hãy truy cập website để bắt đầu thu thập.
                    </p>
                <?php else: ?>
                    <canvas id="trafficChart" height="80"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Device Stats + Traffic Sources + Source Chart - 3 Columns Row -->
<div class="row">
    <!-- Device Stats -->
    <div class="col-lg-4 col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-desktop fa-fw"></i> Thiết bị truy cập
            </div>
            <div class="panel-body">
                <?php if ($totalDevices == 0): ?>
                    <p class="text-muted text-center">Chưa có dữ liệu</p>
                <?php else: ?>
                    <canvas id="deviceChart" height="180"></canvas>
                    <div style="margin-top: 15px;">
                        <table class="table table-condensed">
                            <tr>
                                <td><i class="fa fa-desktop text-primary"></i> Desktop</td>
                                <td class="text-right"><?= number_format($totalDesktop) ?> (<?= $pctDesktop ?>%)</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-mobile text-success"></i> Mobile</td>
                                <td class="text-right"><?= number_format($totalMobile) ?> (<?= $pctMobile ?>%)</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-tablet text-warning"></i> Tablet</td>
                                <td class="text-right"><?= number_format($totalTablet) ?> (<?= $pctTablet ?>%)</td>
                            </tr>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Traffic Sources -->
    <div class="col-lg-4 col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-globe fa-fw"></i> Nguồn truy cập
            </div>
            <div class="panel-body">
                <?php if ($totalSources == 0): ?>
                    <p class="text-muted text-center">Chưa có dữ liệu</p>
                <?php else: ?>
                    <table class="table table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>Nguồn</th>
                                <th class="text-right">Lượt</th>
                                <th class="text-right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fa fa-bookmark text-info"></i> Trực tiếp</td>
                                <td class="text-right"><?= number_format($totalDirect) ?></td>
                                <td class="text-right"><?= $pctDirect ?>%</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-search text-success"></i> Tìm kiếm</td>
                                <td class="text-right"><?= number_format($totalOrganic) ?></td>
                                <td class="text-right"><?= $pctOrganic ?>%</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-share-alt text-primary"></i> Mạng xã hội</td>
                                <td class="text-right"><?= number_format($totalSocial) ?></td>
                                <td class="text-right"><?= $pctSocial ?>%</td>
                            </tr>
                            <tr>
                                <td><i class="fa fa-link text-warning"></i> Giới thiệu</td>
                                <td class="text-right"><?= number_format($totalReferral) ?></td>
                                <td class="text-right"><?= $pctReferral ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Source Chart -->
    <div class="col-lg-4 col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-pie-chart fa-fw"></i> Tỷ lệ nguồn truy cập
            </div>
            <div class="panel-body">
                <?php if ($totalSources == 0): ?>
                    <p class="text-muted text-center">Chưa có dữ liệu</p>
                <?php else: ?>
                    <canvas id="sourceChart" height="180"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($chartLabels) || $totalDevices > 0 || $totalSources > 0): ?>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($chartLabels)): ?>
                // Traffic Line Chart
                new Chart(document.getElementById('trafficChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($chartLabels) ?>,
                        datasets: [{
                            label: 'Lượt truy cập',
                            data: <?= json_encode($chartViews) ?>,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            <?php endif; ?>

            <?php if ($totalDevices > 0): ?>
                // Device Doughnut Chart
                new Chart(document.getElementById('deviceChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Desktop', 'Mobile', 'Tablet'],
                        datasets: [{
                            data: [<?= $totalDesktop ?>, <?= $totalMobile ?>, <?= $totalTablet ?>],
                            backgroundColor: ['#337ab7', '#5cb85c', '#f0ad4e']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            <?php endif; ?>

            <?php if ($totalSources > 0): ?>
                // Source Pie Chart
                new Chart(document.getElementById('sourceChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: ['Trực tiếp', 'Tìm kiếm', 'Mạng xã hội', 'Giới thiệu'],
                        datasets: [{
                            data: [<?= $totalDirect ?>, <?= $totalOrganic ?>, <?= $totalSocial ?>, <?= $totalReferral ?>],
                            backgroundColor: ['#5bc0de', '#5cb85c', '#337ab7', '#f0ad4e']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<style>
    .panel-heading .huge {
        font-size: 28px;
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
</style>