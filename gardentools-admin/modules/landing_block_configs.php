<?php
/**
 * Block Configuration Templates
 * Dùng trong landing_add.php và landing_edit.php
 */
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
?>

<!-- Hero Block Config -->
<template id="config_hero">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Layout</label>
            <select class="form-select form-select-sm" data-config="layout">
                <option value="fullscreen">Full màn hình</option>
                <option value="medium">Trung bình (70vh)</option>
                <option value="short">Ngắn (400px)</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nguồn ảnh</label>
            <select class="form-select form-select-sm" data-config="image_source">
                <option value="project">Tự động từ dự án</option>
                <option value="custom">Chọn ảnh thủ công</option>
            </select>
        </div>
        <div class="col-12 hero-image-picker" style="display:none">
            <label class="form-label">Chọn ảnh nền</label>
            <button type="button" class="btn btn-sm btn-outline-primary btn-pick-images" data-target="hero">
                <i class="fa fa-image me-1"></i> Chọn ảnh từ dự án
            </button>
            <input type="hidden" data-config="selected_images" value="">
            <div class="selected-images-preview mt-2"></div>
        </div>
        <div class="col-12">
            <label class="form-label">Tiêu đề</label>
            <input type="text" class="form-control form-control-sm" data-config="title" placeholder="Để trống sẽ lấy từ dự án">
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả ngắn</label>
            <input type="text" class="form-control form-control-sm" data-config="subtitle" placeholder="Để trống sẽ lấy từ dự án">
        </div>
        <div class="col-md-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" data-config="show_cta" checked>
                <label class="form-check-label">Hiện nút CTA</label>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Text nút</label>
            <input type="text" class="form-control form-control-sm" data-config="cta_text" value="Nhận báo giá">
        </div>
        <div class="col-md-4">
            <label class="form-label">Action</label>
            <select class="form-select form-select-sm" data-config="cta_action">
                <option value="scroll_to_form">Cuộn đến form</option>
            </select>
        </div>
    </div>
</template>

<!-- Content Block Config -->
<template id="config_content">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề section</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Giới thiệu dự án">
        </div>
        <div class="col-12">
            <label class="form-label">Nguồn nội dung</label>
            <select class="form-select form-select-sm" data-config="content_source">
                <option value="project">Tự động từ dự án (bài viết chính)</option>
                <option value="custom">Nhập nội dung thủ công</option>
            </select>
        </div>
        <div class="col-12 content-editor-wrapper" style="display:none">
            <label class="form-label">Nội dung</label>
            <div class="content-editor-container">
                <div class="content-editor-toolbar">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="bold" title="In đậm"><i class="fa fa-bold"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="italic" title="In nghiêng"><i class="fa fa-italic"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="underline" title="Gạch chân"><i class="fa fa-underline"></i></button>
                    <span class="toolbar-separator"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="insertUnorderedList" title="Danh sách"><i class="fa fa-list-ul"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="insertOrderedList" title="Danh sách số"><i class="fa fa-list-ol"></i></button>
                    <span class="toolbar-separator"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="justifyLeft" title="Căn trái"><i class="fa fa-align-left"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="justifyCenter" title="Căn giữa"><i class="fa fa-align-center"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="justifyRight" title="Căn phải"><i class="fa fa-align-right"></i></button>
                    <span class="toolbar-separator"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-html" title="Chỉnh HTML"><i class="fa fa-code"></i></button>
                </div>
                <div class="content-editor" contenteditable="true" data-config="content" placeholder="Nhập nội dung hoặc để trống sẽ lấy từ bài viết dự án..."></div>
                <textarea class="content-html-editor form-control form-control-sm" data-config="content_html" style="display:none" rows="6" placeholder="<p>Nhập HTML tại đây...</p>"></textarea>
            </div>
            <small class="text-muted">Sử dụng toolbar để định dạng hoặc click <i class="fa fa-code"></i> để chỉnh HTML trực tiếp.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Căn chỉnh text</label>
            <select class="form-select form-select-sm" data-config="text_align">
                <option value="left">Trái</option>
                <option value="center">Giữa</option>
                <option value="justify">Justify</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Màu nền</label>
            <input type="color" class="form-control form-control-sm form-control-color" data-config="bg_color" value="#ffffff">
        </div>
    </div>
</template>

<!-- Gallery Block Config -->
<template id="config_gallery">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề section</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Hình ảnh dự án">
        </div>
        <div class="col-md-6">
            <label class="form-label">Nguồn ảnh</label>
            <select class="form-select form-select-sm" data-config="image_source">
                <option value="project">Tự động từ dự án (tất cả ảnh)</option>
                <option value="custom">Chọn ảnh thủ công</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Layout</label>
            <select class="form-select form-select-sm" data-config="layout">
                <option value="grid_3">3 cột</option>
                <option value="grid_2">2 cột</option>
                <option value="grid_4">4 cột</option>
                <option value="masonry">Masonry</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Số ảnh tối đa</label>
            <input type="number" class="form-control form-control-sm" data-config="max_items" value="9" min="1" max="20">
        </div>
        <div class="col-12 gallery-image-picker" style="display:none">
            <label class="form-label">Chọn ảnh cho gallery</label>
            <button type="button" class="btn btn-sm btn-outline-primary btn-pick-images" data-target="gallery">
                <i class="fa fa-images me-1"></i> Chọn ảnh từ dự án
            </button>
            <input type="hidden" data-config="selected_images" value="">
            <div class="selected-images-preview mt-2"></div>
            <small class="text-muted d-block mt-1">Click vào ảnh để chọn/bỏ chọn. Ảnh được chọn sẽ có viền xanh.</small>
        </div>
    </div>
</template>

<!-- Apartments Block Config -->
<template id="config_apartments">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề section</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Căn hộ nổi bật">
        </div>
        <div class="col-md-4">
            <label class="form-label">Layout</label>
            <select class="form-select form-select-sm" data-config="layout">
                <option value="grid_3">Grid 3 cột</option>
                <option value="grid_2">Grid 2 cột</option>
                <option value="grid_4">Grid 4 cột</option>
                <option value="slider">Slider</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Số căn tối đa</label>
            <input type="number" class="form-control form-control-sm" data-config="max_items" value="6" min="1" max="12">
        </div>
        <div class="col-md-4">
            <div class="mt-4">
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" data-config="show_price" checked>
                    <label class="form-check-label">Hiện giá</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" data-config="show_area" checked>
                    <label class="form-check-label">Hiện diện tích</label>
                </div>
            </div>
        </div>
        <div class="col-12">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Căn hộ được lấy tự động từ các bài viết con của dự án (is_active=1, hot=1)
            </small>
        </div>
    </div>
</template>

<!-- Features Block Config -->
<template id="config_features">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề section</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Tiện ích nổi bật">
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả</label>
            <input type="text" class="form-control form-control-sm" data-config="subtitle" placeholder="Mô tả ngắn về tiện ích">
        </div>
        <div class="col-md-4">
            <label class="form-label">Layout</label>
            <select class="form-select form-select-sm" data-config="layout">
                <option value="grid_3">3 cột</option>
                <option value="grid_2">2 cột</option>
                <option value="grid_4">4 cột</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Danh sách tiện ích (JSON)</label>
            <textarea class="form-control form-control-sm" data-config="features" rows="4" placeholder='[{"icon":"fas fa-swimming-pool","title":"Hồ bơi","description":"Hồ bơi 4 mùa"},...]'></textarea>
            <small class="text-muted">Format: [{"icon":"fa class","title":"Tiêu đề","description":"Mô tả"},...]</small>
        </div>
    </div>
</template>

<!-- Video Block Config -->
<template id="config_video">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề section</label>
            <input type="text" class="form-control form-control-sm" data-config="title" placeholder="Để trống nếu không cần">
        </div>
        <div class="col-md-4">
            <label class="form-label">Loại video</label>
            <select class="form-select form-select-sm" data-config="video_type">
                <option value="youtube">YouTube</option>
                <option value="file">File video</option>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">URL Video</label>
            <input type="url" class="form-control form-control-sm" data-config="video_url" placeholder="https://youtube.com/watch?v=...">
        </div>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input type="checkbox" class="form-check-input" data-config="autoplay">
                <label class="form-check-label">Tự động phát (muted)</label>
            </div>
        </div>
    </div>
</template>

<!-- Location Block Config -->
<template id="config_location">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề section</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Vị trí dự án">
        </div>
        <div class="col-12">
            <label class="form-label">Địa chỉ</label>
            <input type="text" class="form-control form-control-sm" data-config="address" placeholder="Để trống sẽ lấy từ dự án">
        </div>
        <div class="col-12">
            <label class="form-label">Google Maps Embed URL</label>
            <input type="url" class="form-control form-control-sm" data-config="map_embed" placeholder="https://www.google.com/maps/embed?pb=...">
            <small class="text-muted">Lấy từ Google Maps > Share > Embed a map</small>
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả vị trí (HTML)</label>
            <textarea class="form-control form-control-sm" data-config="description" rows="3" placeholder="Mô tả tiện ích lân cận..."></textarea>
        </div>
    </div>
</template>

<!-- CTA Block Config -->
<template id="config_cta">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Đăng ký ngay hôm nay">
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả</label>
            <input type="text" class="form-control form-control-sm" data-config="subtitle" value="Nhận ưu đãi đặc biệt khi liên hệ sớm">
        </div>
        <div class="col-md-6">
            <label class="form-label">Text nút</label>
            <input type="text" class="form-control form-control-sm" data-config="button_text" value="Nhận báo giá">
        </div>
        <div class="col-md-6">
            <label class="form-label">Action</label>
            <select class="form-select form-select-sm" data-config="button_action">
                <option value="scroll_to_form">Cuộn đến form</option>
                <option value="tel">Gọi điện</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Số điện thoại (nếu action=tel)</label>
            <input type="tel" class="form-control form-control-sm" data-config="phone" placeholder="0901234567">
        </div>
        <div class="col-md-6">
            <label class="form-label">Màu nền</label>
            <input type="color" class="form-control form-control-sm form-control-color" data-config="bg_color">
        </div>
    </div>
</template>

<!-- Contact Form Block Config -->
<template id="config_contact_form">
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Tiêu đề</label>
            <input type="text" class="form-control form-control-sm" data-config="title" value="Đăng ký nhận thông tin">
        </div>
        <div class="col-12">
            <label class="form-label">Mô tả</label>
            <input type="text" class="form-control form-control-sm" data-config="subtitle" value="Để lại thông tin, chúng tôi sẽ liên hệ trong 24h">
        </div>
        <div class="col-md-6">
            <label class="form-label">Text nút gửi</label>
            <input type="text" class="form-control form-control-sm" data-config="button_text" value="Gửi yêu cầu">
        </div>
        <div class="col-md-6">
            <label class="form-label">Màu nền section</label>
            <input type="color" class="form-control form-control-sm form-control-color" data-config="bg_color">
        </div>
        <div class="col-12">
            <label class="form-label">Hiển thị các trường</label>
            <div class="d-flex gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" data-config="show_phone" checked>
                    <label class="form-check-label">Số điện thoại</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" data-config="show_email" checked>
                    <label class="form-check-label">Email</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" data-config="show_message" checked>
                    <label class="form-check-label">Nội dung</label>
                </div>
            </div>
        </div>
    </div>
</template>
