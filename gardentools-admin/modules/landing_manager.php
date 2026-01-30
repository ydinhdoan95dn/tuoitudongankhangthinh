<?php
if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

/**
 * Landing Page Manager - Danh sách và quản lý Landing Pages
 */

// Xử lý actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$actionResult = null;

// Xóa landing page
if ($action === 'delete' && $id > 0) {
    $db->table = "landing_pages";
    $db->condition = "id = " . $id;
    $db->delete();
    $actionResult = 'deleted';
}

// Toggle active
if ($action === 'toggle' && $id > 0) {
    $db->table = "landing_pages";
    $db->condition = "id = " . $id;
    $db->limit = "1";
    $db->order = "";
    $page = $db->select();
    if (!empty($page)) {
        $newStatus = $page[0]['is_active'] ? 0 : 1;
        $db->table = "landing_pages";
        $db->condition = "id = " . $id;
        $db->update(array('is_active' => $newStatus));
        $actionResult = $newStatus ? 'activated' : 'deactivated';
    }
}

// Duplicate landing page
if ($action === 'duplicate' && $id > 0) {
    $db->table = "landing_pages";
    $db->condition = "id = " . $id;
    $db->limit = "1";
    $db->order = "";
    $original = $db->select();

    if (!empty($original)) {
        $data = $original[0];
        unset($data['id']);
        $data['slug'] = $data['slug'] . '-copy-' . time();
        $data['name'] = $data['name'] . ' (Copy)';
        $data['views'] = 0;
        $data['leads'] = 0;
        $data['created_time'] = time();
        $data['modified_time'] = null;

        $db->table = "landing_pages";
        $db->insert($data);
        $actionResult = 'duplicated';
    }
}

// Filters
$filterProject = isset($_GET['project']) ? intval($_GET['project']) : 0;
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$searchKeyword = trim(isset($_GET['q']) ? $_GET['q'] : '');
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build condition
$conditions = array();
if ($filterProject > 0) {
    $conditions[] = "lp.project_id = " . $filterProject;
}
if ($filterStatus === 'active') {
    $conditions[] = "lp.is_active = 1";
} elseif ($filterStatus === 'inactive') {
    $conditions[] = "lp.is_active = 0";
}
if ($searchKeyword) {
    $conditions[] = "(lp.name LIKE '%" . $db->clearText($searchKeyword) . "%' OR lp.slug LIKE '%" . $db->clearText($searchKeyword) . "%')";
}
$whereClause = !empty($conditions) ? implode(' AND ', $conditions) : '1=1';

// Count total
$countResult = $db->sql_query("SELECT COUNT(*) as total FROM `dxmt_landing_pages` lp WHERE " . $whereClause);
$total = isset($countResult[0]['total']) ? $countResult[0]['total'] : 0;
$totalPages = ceil($total / $perPage);

// Get landing pages with project info
$landingPages = $db->sql_query("SELECT lp.*, am.name as project_name
              FROM `dxmt_landing_pages` lp
              LEFT JOIN `dxmt_article_menu` am ON lp.project_id = am.article_menu_id
              WHERE " . $whereClause . "
              ORDER BY lp.created_time DESC
              LIMIT " . $offset . ", " . $perPage);

// Get projects for filter dropdown
$db->table = "article_menu";
$db->condition = "parent = 0 AND is_active = 1";
$db->order = "name ASC";
$db->limit = "";
$projects = $db->select();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-file-alt me-2"></i>Quản lý Landing Pages
                </h4>
                <a href="?<?= TTH_PATH ?>=landing_add" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tạo Landing Page
                </a>
            </div>
            <div class="card-body">
                <?php if ($actionResult === 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>Đã xóa landing page thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($actionResult === 'activated'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>Đã kích hoạt landing page!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($actionResult === 'deactivated'): ?>
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-pause-circle me-2"></i>Đã tắt landing page!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($actionResult === 'duplicated'): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-copy me-2"></i>Đã nhân bản landing page thành công!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <form method="get" class="row g-3 mb-4">
                    <input type="hidden" name="<?= TTH_PATH ?>" value="landing_manager">

                    <div class="col-md-3">
                        <label class="form-label">Dự án</label>
                        <select name="project" class="form-select">
                            <option value="">Tất cả dự án</option>
                            <?php foreach ($projects as $proj): ?>
                                <option value="<?= $proj['article_menu_id'] ?>" <?= $filterProject == $proj['article_menu_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($proj['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="active" <?= $filterStatus === 'active' ? 'selected' : '' ?>>Đang hoạt động
                            </option>
                            <option value="inactive" <?= $filterStatus === 'inactive' ? 'selected' : '' ?>>Đã tắt</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" name="q" class="form-control" placeholder="Tên hoặc slug..."
                            value="<?= htmlspecialchars($searchKeyword) ?>">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary me-2">
                            <i class="fas fa-search me-1"></i>Lọc
                        </button>
                        <a href="?<?= TTH_PATH ?>=landing_manager" class="btn btn-outline-secondary">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                    </div>
                </form>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <h3 class="mb-0 text-primary"><?= $total ?></h3>
                                <small class="text-muted">Tổng Landing Pages</small>
                            </div>
                        </div>
                    </div>
                    <?php
                    // Quick stats
                    $stats = $db->sql_query("SELECT SUM(views) as total_views, SUM(leads) as total_leads FROM `dxmt_landing_pages`");
                    $totalViews = isset($stats[0]['total_views']) ? $stats[0]['total_views'] : 0;
                    $totalLeads = isset($stats[0]['total_leads']) ? $stats[0]['total_leads'] : 0;
                    ?>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <h3 class="mb-0 text-info"><?= number_format($totalViews) ?></h3>
                                <small class="text-muted">Tổng lượt xem</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <h3 class="mb-0 text-success"><?= number_format($totalLeads) ?></h3>
                                <small class="text-muted">Tổng leads</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center py-3">
                                <h3 class="mb-0 text-warning">
                                    <?= $totalViews > 0 ? number_format(($totalLeads / $totalViews) * 100, 2) : 0 ?>%
                                </h3>
                                <small class="text-muted">Tỷ lệ chuyển đổi</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th width="50">#</th>
                                <th>Tên / Slug</th>
                                <th>Dự án</th>
                                <th width="80" class="text-center">Views</th>
                                <th width="80" class="text-center">Leads</th>
                                <th width="80" class="text-center">CVR</th>
                                <th width="100" class="text-center">Trạng thái</th>
                                <th width="120">Ngày tạo</th>
                                <th width="180" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($landingPages)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        Chưa có landing page nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($landingPages as $idx => $lp): ?>
                                    <?php
                                    $cvr = $lp['views'] > 0 ? ($lp['leads'] / $lp['views']) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td><?= $offset + $idx + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($lp['name']) ?></strong>
                                            <br>
                                            <a href="<?= HOME_URL ?>landing/<?= $lp['slug'] ?>" target="_blank"
                                                class="text-muted small">
                                                <i class="fas fa-external-link-alt me-1"></i>/landing/<?= $lp['slug'] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($lp['project_name']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($lp['project_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format($lp['views']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= number_format($lp['leads']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="<?= $cvr >= 5 ? 'text-success' : ($cvr >= 2 ? 'text-warning' : 'text-muted') ?>">
                                                <?= number_format($cvr, 2) ?>%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($lp['is_active']): ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Đã tắt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', $lp['created_time']) ?>
                                            <br>
                                            <small class="text-muted"><?= date('H:i', $lp['created_time']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="?<?= TTH_PATH ?>=landing_edit&id=<?= $lp['id'] ?>" class="btn btn-info"
                                                    title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?<?= TTH_PATH ?>=landing_manager&action=duplicate&id=<?= $lp['id'] ?>"
                                                    class="btn btn-secondary" title="Nhân bản"
                                                    onclick="return confirm('Nhân bản landing page này?')">
                                                    <i class="fas fa-copy"></i>
                                                </a>
                                                <a href="?<?= TTH_PATH ?>=landing_manager&action=toggle&id=<?= $lp['id'] ?>"
                                                    class="btn btn-<?= $lp['is_active'] ? 'warning' : 'success' ?>"
                                                    title="<?= $lp['is_active'] ? 'Tắt' : 'Bật' ?>">
                                                    <i class="fas fa-<?= $lp['is_active'] ? 'pause' : 'play' ?>"></i>
                                                </a>
                                                <a href="?<?= TTH_PATH ?>=landing_manager&action=delete&id=<?= $lp['id'] ?>"
                                                    class="btn btn-danger" title="Xóa"
                                                    onclick="return confirm('Bạn có chắc muốn xóa landing page này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?<?= TTH_PATH ?>=landing_manager&project=<?= $filterProject ?>&status=<?= $filterStatus ?>&q=<?= urlencode($searchKeyword) ?>&p=<?= $page - 1 ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?<?= TTH_PATH ?>=landing_manager&project=<?= $filterProject ?>&status=<?= $filterStatus ?>&q=<?= urlencode($searchKeyword) ?>&p=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?<?= TTH_PATH ?>=landing_manager&project=<?= $filterProject ?>&status=<?= $filterStatus ?>&q=<?= urlencode($searchKeyword) ?>&p=<?= $page + 1 ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>