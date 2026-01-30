<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
?>
<!-- Menu path -->
<div class="row">
    <ol class="breadcrumb">
        <li>
            <a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
        </li>
        <li>
            <a href="?<?=TTH_PATH?>=article_manager"><i class="fa fa-edit"></i> Quản lý nội dung</a>
        </li>
        <li>
            <i class="fa fa-tags"></i> Quản lý Tags
        </li>
    </ol>
</div>
<!-- /.row -->

<!-- CSS -->
<link rel="stylesheet" href="./css/tags-manager.css?v=<?=time()?>">

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-tags fa-fw"></i> Quản lý Tags
                <div class="pull-right">
                    <button type="button" class="btn btn-success btn-sm" onclick="TagsManager.showAddModal()">
                        <i class="fa fa-plus"></i> Thêm Tag
                    </button>
                </div>
            </div>
            <div class="panel-body">
                <!-- Filter -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <select id="filterTagType" class="form-control" onchange="TagsManager.loadTags()">
                            <option value="">Tất cả loại tag</option>
                            <option value="location">Vị trí</option>
                            <option value="property_type">Loại BĐS</option>
                            <option value="feature">Tiện ích</option>
                            <option value="topic">Chủ đề</option>
                            <option value="general">Chung</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="searchTag" class="form-control" placeholder="Tìm kiếm tag...">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" onclick="TagsManager.loadTags()">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <span id="tagCount" class="text-muted"></span>
                    </div>
                </div>

                <!-- Tags Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="tagsTable">
                        <thead>
                            <tr>
                                <th width="50">ID</th>
                                <th>Tên Tag</th>
                                <th width="120">Slug</th>
                                <th width="100">Loại</th>
                                <th width="80">Số bài</th>
                                <th width="120">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tagsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <i class="fa fa-spinner fa-spin"></i> Đang tải...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="text-center" id="tagsPagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="tagModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="tagModalTitle">Thêm Tag</h4>
            </div>
            <div class="modal-body">
                <form id="tagForm">
                    <input type="hidden" id="tagId" name="id">
                    <div class="form-group">
                        <label for="tagName">Tên Tag <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tagName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="tagType">Loại Tag</label>
                        <select class="form-control" id="tagType" name="tag_type">
                            <option value="general">Chung</option>
                            <option value="location">Vị trí</option>
                            <option value="property_type">Loại BĐS</option>
                            <option value="feature">Tiện ích</option>
                            <option value="topic">Chủ đề</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="TagsManager.saveTag()">
                    <i class="fa fa-save"></i> Lưu
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Import from Keywords Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Import Tags từ Keywords</h4>
            </div>
            <div class="modal-body">
                <p>Chức năng này sẽ quét tất cả bài viết và import keywords thành tags.</p>
                <div class="alert alert-warning">
                    <i class="fa fa-warning"></i> Lưu ý: Quá trình này có thể mất vài phút nếu có nhiều bài viết.
                </div>
                <div id="importProgress" style="display:none;">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%">
                            0%
                        </div>
                    </div>
                    <p class="text-center" id="importStatus">Đang xử lý...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btnStartImport" onclick="TagsManager.importFromKeywords()">
                    <i class="fa fa-download"></i> Bắt đầu Import
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="./js/tags-manager.js?v=<?=time()?>"></script>
<script>
$(document).ready(function() {
    TagsManager.init();
});
</script>
