<?php
@session_start();

// System
define('TTH_SYSTEM', true);
$_SESSION["language"] = (!empty($_SESSION["lang_admin"]) && isset($_SESSION["lang_admin"])) ? $_SESSION["lang_admin"] : 'vi';

require_once('..' . DIRECTORY_SEPARATOR . 'define.php');
include_once(_A_FUNCTIONS . DS . "Function.php");

// Tutorial Builder Config
include_once(__DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'tutorial_config.php');

try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch(DatabaseConnException $e) {
    echo $e->getMessage();
}

include_once(_F_INCLUDES . DS . "_tth_constants.php");
require_once(ROOT_DIR . DS . ADMIN_DIR . DS . '_check_login.php');

if (!$login_true) {
    header('Location: login.php');
    exit;
}

// Lấy trang khởi đầu từ query string
$startPage = isset($_GET['page']) ? $_GET['page'] : 'index.php?tth=home';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial Builder - DXMT Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ========================================
           HEADER - Control Panel
           ======================================== */
        .tb-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 1000;
        }

        .tb-header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .tb-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
        }

        .tb-logo i {
            font-size: 24px;
        }

        .tb-logo-text {
            font-size: 18px;
            font-weight: 600;
        }

        .tb-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tb-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tb-btn-exit {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .tb-btn-exit:hover {
            background: rgba(255,255,255,0.25);
        }

        /* ========================================
           RECORDING PANEL
           ======================================== */
        .tb-record-panel {
            display: none;
            padding: 15px 20px;
            background: rgba(0,0,0,0.15);
        }

        .tb-record-panel.active {
            display: block;
        }

        .tb-record-row {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .tb-record-status {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
        }

        .tb-record-dot {
            width: 12px;
            height: 12px;
            background: #ff4757;
            border-radius: 50%;
            animation: tb-pulse 1s infinite;
        }

        .tb-record-dot.paused {
            background: #ffa502;
            animation: none;
        }

        @keyframes tb-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.9); }
        }

        .tb-record-name {
            font-weight: 600;
            font-size: 15px;
        }

        .tb-record-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: rgba(255,255,255,0.8);
            font-size: 13px;
        }

        .tb-record-info span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .tb-record-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .tb-btn-record {
            padding: 8px 14px;
            font-size: 12px;
        }

        .tb-btn-primary {
            background: #2ed573;
            color: #fff;
        }

        .tb-btn-primary:hover {
            background: #26c066;
        }

        .tb-btn-warning {
            background: #ffa502;
            color: #fff;
        }

        .tb-btn-warning:hover {
            background: #e69500;
        }

        .tb-btn-danger {
            background: #ff4757;
            color: #fff;
        }

        .tb-btn-danger:hover {
            background: #e63e4d;
        }

        .tb-btn-info {
            background: #3742fa;
            color: #fff;
        }

        .tb-btn-info:hover {
            background: #2e38e0;
        }

        .tb-btn-secondary {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }

        .tb-btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Paused state */
        .tb-record-panel.paused {
            background: rgba(255, 165, 2, 0.2);
        }

        .tb-record-panel.paused .tb-paused-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ffa502;
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .tb-paused-label {
            display: none;
        }

        /* ========================================
           STEPS PREVIEW BAR
           ======================================== */
        .tb-steps-bar {
            display: none;
            padding: 10px 20px;
            background: rgba(0,0,0,0.2);
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .tb-steps-bar.active {
            display: block;
        }

        .tb-steps-list {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 5px;
        }

        .tb-step-item {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            color: #fff;
            font-size: 12px;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tb-step-item:hover {
            background: rgba(255,255,255,0.2);
        }

        .tb-step-item .step-num {
            width: 20px;
            height: 20px;
            background: #2ed573;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 11px;
        }

        .tb-step-item .step-remove {
            width: 16px;
            height: 16px;
            background: rgba(255,71,87,0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .tb-step-item:hover .step-remove {
            opacity: 1;
        }

        .tb-step-item .step-remove:hover {
            background: #ff4757;
        }

        /* ========================================
           URL BAR
           ======================================== */
        .tb-url-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 20px;
            background: rgba(0,0,0,0.3);
        }

        .tb-url-bar i {
            color: rgba(255,255,255,0.5);
        }

        .tb-url-display {
            flex: 1;
            background: rgba(255,255,255,0.1);
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            color: #fff;
            font-size: 12px;
            font-family: monospace;
        }

        .tb-url-refresh {
            background: none;
            border: none;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
        }

        .tb-url-refresh:hover {
            color: #fff;
        }

        /* ========================================
           IFRAME CONTAINER
           ======================================== */
        .tb-iframe-container {
            flex: 1;
            position: relative;
            background: #fff;
        }

        .tb-iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .tb-iframe-overlay {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: transparent;
            cursor: crosshair;
            z-index: 100;
        }

        .tb-iframe-overlay.active {
            display: block;
        }

        /* ========================================
           CREATE MODAL
           ======================================== */
        .tb-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .tb-modal-overlay.active {
            display: flex;
        }

        .tb-modal {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.3s;
        }

        .tb-modal-overlay.active .tb-modal {
            transform: scale(1);
            opacity: 1;
        }

        .tb-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
        }

        .tb-modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .tb-modal-close {
            width: 32px;
            height: 32px;
            border: none;
            background: #f5f5f5;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            transition: all 0.2s;
        }

        .tb-modal-close:hover {
            background: #ff4757;
            color: #fff;
        }

        .tb-modal-body {
            padding: 20px;
        }

        .tb-form-group {
            margin-bottom: 15px;
        }

        .tb-form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #333;
        }

        .tb-form-group input,
        .tb-form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .tb-form-group input:focus,
        .tb-form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .tb-modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 16px 20px;
            border-top: 1px solid #eee;
        }

        .tb-modal-footer .tb-btn {
            padding: 10px 20px;
        }

        .tb-btn-cancel {
            background: #f5f5f5;
            color: #666;
        }

        .tb-btn-cancel:hover {
            background: #e5e5e5;
        }

        /* ========================================
           STEP INPUT PANEL (floating)
           ======================================== */
        .tb-step-input {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 16px 20px;
            width: 90%;
            max-width: 500px;
            z-index: 1500;
        }

        .tb-step-input.active {
            display: block;
        }

        .tb-step-input-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .tb-step-input-title {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }

        .tb-step-input-selector {
            font-size: 11px;
            color: #666;
            background: #f5f5f5;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .tb-step-input textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            resize: none;
            height: 80px;
        }

        .tb-step-input textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .tb-step-input-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            justify-content: flex-end;
        }

        /* ========================================
           TOAST
           ======================================== */
        .tb-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background: #333;
            color: #fff;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s;
            z-index: 3000;
        }

        .tb-toast.active {
            transform: translateY(0);
            opacity: 1;
        }

        .tb-toast.success {
            background: #2ed573;
        }

        .tb-toast.error {
            background: #ff4757;
        }

        .tb-toast.info {
            background: #3742fa;
        }

        /* ========================================
           HIGHLIGHT IN IFRAME
           ======================================== */
        .tb-highlight-info {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(102, 126, 234, 0.95);
            color: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            text-align: center;
            z-index: 1500;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .tb-highlight-info.active {
            display: block;
        }

        .tb-highlight-info i {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }

        .tb-highlight-info p {
            margin: 0;
            font-size: 14px;
        }

        /* ========================================
           TUTORIAL LIST DROPDOWN
           ======================================== */
        .tb-dropdown {
            position: relative;
        }

        .tb-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 280px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-top: 8px;
            z-index: 1100;
            max-height: 400px;
            overflow-y: auto;
        }

        .tb-dropdown-menu.active {
            display: block;
        }

        .tb-dropdown-header {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }

        .tb-dropdown-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            border-bottom: 1px solid #f5f5f5;
            cursor: pointer;
            transition: background 0.2s;
        }

        .tb-dropdown-item:hover {
            background: #f8f9fa;
        }

        .tb-dropdown-item:last-child {
            border-bottom: none;
        }

        .tb-dropdown-item-info {
            flex: 1;
        }

        .tb-dropdown-item-name {
            font-size: 13px;
            font-weight: 500;
            color: #333;
        }

        .tb-dropdown-item-meta {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        .tb-dropdown-item-actions {
            display: flex;
            gap: 5px;
        }

        .tb-dropdown-item-actions button {
            width: 28px;
            height: 28px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s;
        }

        .tb-dropdown-item-actions .btn-play {
            background: #e3f2fd;
            color: #1976d2;
        }

        .tb-dropdown-item-actions .btn-play:hover {
            background: #1976d2;
            color: #fff;
        }

        .tb-dropdown-item-actions .btn-edit {
            background: #fff3e0;
            color: #f57c00;
        }

        .tb-dropdown-item-actions .btn-edit:hover {
            background: #f57c00;
            color: #fff;
        }

        .tb-dropdown-item-actions .btn-delete {
            background: #ffebee;
            color: #d32f2f;
        }

        .tb-dropdown-item-actions .btn-delete:hover {
            background: #d32f2f;
            color: #fff;
        }

        .tb-dropdown-empty {
            padding: 30px 20px;
            text-align: center;
            color: #999;
        }

        .tb-dropdown-empty i {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
            opacity: 0.5;
        }

        .tb-dropdown-footer {
            padding: 10px 16px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .tb-dropdown-footer button {
            flex: 1;
            padding: 8px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="tb-header">
        <div class="tb-header-top">
            <div class="tb-logo">
                <i class="fa fa-graduation-cap"></i>
                <span class="tb-logo-text">Tutorial Builder</span>
            </div>
            <div class="tb-header-actions">
                <button class="tb-btn tb-btn-primary" id="btnNewTutorial">
                    <i class="fa fa-plus"></i> Tạo mới
                </button>
                <div class="tb-dropdown">
                    <button class="tb-btn tb-btn-secondary" id="btnListTutorial">
                        <i class="fa fa-list"></i> Danh sách
                    </button>
                    <div class="tb-dropdown-menu" id="tutorialDropdown"></div>
                </div>
                <button class="tb-btn tb-btn-exit" id="btnExit">
                    <i class="fa fa-sign-out"></i> Thoát
                </button>
            </div>
        </div>

        <!-- Recording Panel -->
        <div class="tb-record-panel" id="recordPanel">
            <div class="tb-record-row">
                <div class="tb-record-status">
                    <span class="tb-record-dot" id="recordDot"></span>
                    <span class="tb-record-name" id="recordName">Đang ghi...</span>
                    <span class="tb-paused-label"><i class="fa fa-pause"></i> TẠM DỪNG</span>
                </div>
                <div class="tb-record-info">
                    <span><i class="fa fa-list-ol"></i> <span id="stepCount">0</span> bước</span>
                    <span><i class="fa fa-file-o"></i> <span id="currentPage">-</span></span>
                </div>
                <div class="tb-record-actions">
                    <button class="tb-btn tb-btn-record tb-btn-warning" id="btnPause">
                        <i class="fa fa-pause"></i> Tạm dừng
                    </button>
                    <button class="tb-btn tb-btn-record tb-btn-danger" id="btnCancel">
                        <i class="fa fa-times"></i> Hủy
                    </button>
                    <button class="tb-btn tb-btn-record tb-btn-primary" id="btnFinish">
                        <i class="fa fa-check"></i> Hoàn tất
                    </button>
                </div>
            </div>
        </div>

        <!-- Steps Preview Bar -->
        <div class="tb-steps-bar" id="stepsBar">
            <div class="tb-steps-list" id="stepsList"></div>
        </div>
    </header>

    <!-- URL Bar -->
    <div class="tb-url-bar">
        <i class="fa fa-globe"></i>
        <input type="text" class="tb-url-display" id="urlDisplay" readonly>
        <button class="tb-url-refresh" id="btnRefresh" title="Refresh">
            <i class="fa fa-refresh"></i>
        </button>
    </div>

    <!-- Iframe Container -->
    <div class="tb-iframe-container">
        <iframe src="<?php echo htmlspecialchars($startPage); ?>" class="tb-iframe" id="adminIframe"></iframe>
        <div class="tb-iframe-overlay" id="iframeOverlay"></div>
    </div>

    <!-- Create Modal -->
    <div class="tb-modal-overlay" id="createModal">
        <div class="tb-modal">
            <div class="tb-modal-header">
                <h3>Tạo hướng dẫn mới</h3>
                <button class="tb-modal-close" data-close="createModal">&times;</button>
            </div>
            <div class="tb-modal-body">
                <div class="tb-form-group">
                    <label>Tên hướng dẫn:</label>
                    <input type="text" id="tutorialName" placeholder="VD: Hướng dẫn thêm sản phẩm mới">
                </div>
            </div>
            <div class="tb-modal-footer">
                <button class="tb-btn tb-btn-cancel" data-close="createModal">Hủy</button>
                <button class="tb-btn tb-btn-primary" id="btnStartRecord">
                    <i class="fa fa-circle"></i> Bắt đầu ghi
                </button>
            </div>
        </div>
    </div>

    <!-- Step Input Panel -->
    <div class="tb-step-input" id="stepInput">
        <div class="tb-step-input-header">
            <span class="tb-step-input-title">Thêm bước mới</span>
            <span class="tb-step-input-selector" id="selectedSelector">-</span>
        </div>
        <textarea id="stepDescription" placeholder="Nhập mô tả cho bước này..."></textarea>
        <div class="tb-step-input-actions">
            <button class="tb-btn tb-btn-cancel" id="btnCancelStep">Hủy</button>
            <button class="tb-btn tb-btn-primary" id="btnSaveStep">
                <i class="fa fa-plus"></i> Thêm bước
            </button>
        </div>
    </div>

    <!-- Highlight Info -->
    <div class="tb-highlight-info" id="highlightInfo">
        <i class="fa fa-hand-pointer-o"></i>
        <p>Click vào phần tử trong trang admin<br>để thêm bước hướng dẫn</p>
    </div>

    <!-- Toast -->
    <div class="tb-toast" id="toast"></div>

    <script>
    (function() {
        'use strict';

        const STORAGE_KEY = 'admin_tutorials';
        const API_URL = 'ajax_tutorial.php';

        // State
        let tutorials = loadTutorials();
        let dbReady = false;
        let isRecording = false;
        let isPaused = false;
        let currentTutorial = null;
        let currentSteps = [];
        let selectedElement = null;
        let selectedSelector = '';

        // Elements
        const iframe = document.getElementById('adminIframe');
        const overlay = document.getElementById('iframeOverlay');
        const recordPanel = document.getElementById('recordPanel');
        const stepsBar = document.getElementById('stepsBar');
        const stepsList = document.getElementById('stepsList');
        const urlDisplay = document.getElementById('urlDisplay');
        const createModal = document.getElementById('createModal');
        const stepInput = document.getElementById('stepInput');
        const highlightInfo = document.getElementById('highlightInfo');

        // ========================================
        // INITIALIZATION
        // ========================================
        init();

        async function init() {
            // Kiểm tra database
            await initDatabase();
            bindEvents();
            updateUrlDisplay();
        }

        /**
         * Kiểm tra và khởi tạo kết nối database
         */
        async function initDatabase() {
            try {
                const response = await fetch(API_URL + '?action=check_table', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const result = await response.json();
                console.log('[Tutorial Builder] initDatabase response:', result);

                if (result.success) {
                    dbReady = true;
                    console.log('[Tutorial Builder] Database ready');
                    // Load từ database
                    await loadTutorialsFromDB();
                } else {
                    console.error('[Tutorial Builder] Database init failed:', result.message);
                }
            } catch (e) {
                console.error('[Tutorial Builder] Database init error:', e);
            }
        }

        /**
         * Load tutorials từ database
         */
        async function loadTutorialsFromDB() {
            if (!dbReady) return;

            try {
                const response = await fetch(API_URL + '?action=list', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const result = await response.json();

                if (result.success) {
                    tutorials = result.data || [];
                    // Sync vào localStorage
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(tutorials));
                    console.log('[Tutorial Builder] Loaded', tutorials.length, 'tutorials from DB');
                }
            } catch (e) {
                console.error('[Tutorial Builder] Load from DB error:', e);
            }
        }

        /**
         * Lưu tutorial vào database
         */
        async function saveTutorialToDB(tutorial) {
            console.log('[Tutorial Builder] saveTutorialToDB called, dbReady =', dbReady);

            if (!dbReady) {
                console.warn('[Tutorial Builder] Database not ready, saving to localStorage only');
                return { success: false };
            }

            try {
                const formData = new FormData();
                formData.append('tutorial_id', tutorial.id);
                formData.append('name', tutorial.name);
                formData.append('steps', JSON.stringify(tutorial.steps));

                console.log('[Tutorial Builder] Sending to API:', {
                    tutorial_id: tutorial.id,
                    name: tutorial.name,
                    steps_count: tutorial.steps.length
                });

                const response = await fetch(API_URL + '?action=save', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                const result = await response.json();
                console.log('[Tutorial Builder] Save API response:', result);

                return result;
            } catch (e) {
                console.error('[Tutorial Builder] Save error:', e);
                return { success: false, message: e.message };
            }
        }

        /**
         * Xóa tutorial khỏi database
         */
        async function deleteTutorialFromDB(tutorialId) {
            if (!dbReady) return { success: false };

            try {
                const formData = new FormData();
                formData.append('tutorial_id', tutorialId);

                const response = await fetch(API_URL + '?action=delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                return await response.json();
            } catch (e) {
                console.error('[Tutorial Builder] Delete error:', e);
                return { success: false, message: e.message };
            }
        }

        function bindEvents() {
            // Header buttons
            document.getElementById('btnNewTutorial').addEventListener('click', () => openModal('createModal'));
            document.getElementById('btnListTutorial').addEventListener('click', toggleDropdown);
            document.getElementById('btnExit').addEventListener('click', exitBuilder);

            // Record panel buttons
            document.getElementById('btnPause').addEventListener('click', togglePause);
            document.getElementById('btnCancel').addEventListener('click', cancelRecording);
            document.getElementById('btnFinish').addEventListener('click', finishRecording);

            // Create modal
            document.getElementById('btnStartRecord').addEventListener('click', startRecording);
            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(btn.dataset.close));
            });

            // Enter key in tutorial name
            document.getElementById('tutorialName').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') startRecording();
            });

            // Step input
            document.getElementById('btnSaveStep').addEventListener('click', saveStep);
            document.getElementById('btnCancelStep').addEventListener('click', cancelStep);

            // URL bar
            document.getElementById('btnRefresh').addEventListener('click', () => {
                iframe.contentWindow.location.reload();
            });

            // Iframe load
            iframe.addEventListener('load', onIframeLoad);

            // Overlay click (for element selection)
            overlay.addEventListener('click', onOverlayClick);

            // Close dropdown on outside click
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.tb-dropdown')) {
                    document.getElementById('tutorialDropdown').classList.remove('active');
                }
            });
        }

        // ========================================
        // STORAGE
        // ========================================
        function loadTutorials() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch (e) {
                return [];
            }
        }

        function saveTutorials() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(tutorials));
        }

        // ========================================
        // IFRAME HANDLING
        // ========================================
        function onIframeLoad() {
            updateUrlDisplay();
            injectIframeStyles();

            if (isRecording && !isPaused) {
                // Re-inject and setup click handlers
                setupIframeClickHandler();
            }
        }

        function updateUrlDisplay() {
            try {
                const iframeUrl = iframe.contentWindow.location.href;
                const path = iframe.contentWindow.location.pathname + iframe.contentWindow.location.search;
                urlDisplay.value = path;
                document.getElementById('currentPage').textContent = path.split('?')[0].split('/').pop() || 'home';
            } catch (e) {
                urlDisplay.value = 'Không thể đọc URL';
            }
        }

        function injectIframeStyles() {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                // Remove old injected style
                const oldStyle = iframeDoc.getElementById('tb-injected-style');
                if (oldStyle) oldStyle.remove();

                // Inject highlight styles
                const style = iframeDoc.createElement('style');
                style.id = 'tb-injected-style';
                style.textContent = `
                    .tb-element-hover {
                        outline: 3px dashed #667eea !important;
                        outline-offset: 2px !important;
                        cursor: crosshair !important;
                    }
                    .tb-element-selected {
                        outline: 3px solid #2ed573 !important;
                        outline-offset: 2px !important;
                    }
                `;
                iframeDoc.head.appendChild(style);
            } catch (e) {
                console.error('Cannot inject iframe styles:', e);
            }
        }

        function setupIframeClickHandler() {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

                // First remove any existing handlers to prevent duplicates
                iframeDoc.removeEventListener('mouseover', onIframeMouseOver, true);
                iframeDoc.removeEventListener('mouseout', onIframeMouseOut, true);
                iframeDoc.removeEventListener('click', onIframeClick, true);

                // Add event listeners
                iframeDoc.addEventListener('mouseover', onIframeMouseOver, true);
                iframeDoc.addEventListener('mouseout', onIframeMouseOut, true);
                iframeDoc.addEventListener('click', onIframeClick, true);

                console.log('Tutorial Builder: Iframe handlers setup successfully');
            } catch (e) {
                console.error('Cannot setup iframe handlers:', e);
            }
        }

        function removeIframeClickHandler() {
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.removeEventListener('mouseover', onIframeMouseOver, true);
                iframeDoc.removeEventListener('mouseout', onIframeMouseOut, true);
                iframeDoc.removeEventListener('click', onIframeClick, true);

                // Remove any highlights
                iframeDoc.querySelectorAll('.tb-element-hover, .tb-element-selected').forEach(el => {
                    el.classList.remove('tb-element-hover', 'tb-element-selected');
                });
            } catch (e) {
                console.error('Cannot remove iframe handlers:', e);
            }
        }

        function onIframeMouseOver(e) {
            if (!isRecording || isPaused) return;
            e.target.classList.add('tb-element-hover');
        }

        function onIframeMouseOut(e) {
            if (!isRecording || isPaused) return;
            e.target.classList.remove('tb-element-hover');
        }

        function onIframeClick(e) {
            console.log('Tutorial Builder: Iframe click detected', { isRecording, isPaused, target: e.target });

            if (!isRecording || isPaused) {
                console.log('Tutorial Builder: Click ignored - not recording or paused');
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            // Remove previous selection
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.querySelectorAll('.tb-element-selected').forEach(el => {
                    el.classList.remove('tb-element-selected');
                });
            } catch (err) {}

            // Mark as selected
            e.target.classList.remove('tb-element-hover');
            e.target.classList.add('tb-element-selected');

            selectedElement = e.target;
            selectedSelector = generateSelector(e.target);

            console.log('Tutorial Builder: Element selected', { selector: selectedSelector });

            // Show step input panel
            document.getElementById('selectedSelector').textContent = selectedSelector;
            document.getElementById('stepDescription').value = '';
            stepInput.classList.add('active');
            highlightInfo.classList.remove('active');
            document.getElementById('stepDescription').focus();
        }

        function onOverlayClick(e) {
            // This is for when we use overlay mode (currently not used)
        }

        function generateSelector(element) {
            if (element.id) {
                return '#' + element.id;
            }

            const path = [];
            let current = element;
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

            while (current && current !== iframeDoc.body) {
                let selector = current.tagName.toLowerCase();

                if (current.id) {
                    selector = '#' + current.id;
                    path.unshift(selector);
                    break;
                } else if (current.className && typeof current.className === 'string') {
                    const classes = current.className.trim().split(/\s+/).filter(c =>
                        c && !c.startsWith('tb-') && !c.startsWith('hover') && !c.startsWith('active')
                    );
                    if (classes.length > 0) {
                        selector += '.' + classes.slice(0, 2).join('.');
                    }
                }

                const parent = current.parentElement;
                if (parent) {
                    const siblings = Array.from(parent.children).filter(c =>
                        c.tagName === current.tagName
                    );
                    if (siblings.length > 1) {
                        const index = siblings.indexOf(current) + 1;
                        selector += ':nth-child(' + index + ')';
                    }
                }

                path.unshift(selector);
                current = current.parentElement;
            }

            return path.join(' > ');
        }

        // ========================================
        // RECORDING
        // ========================================
        function startRecording() {
            const nameInput = document.getElementById('tutorialName');
            const name = nameInput.value.trim();

            if (!name) {
                showToast('Vui lòng nhập tên hướng dẫn!', 'error');
                nameInput.focus();
                return;
            }

            closeModal('createModal');
            nameInput.value = '';

            isRecording = true;
            isPaused = false;
            currentTutorial = {
                id: 'tutorial_' + Date.now(),
                name: name,
                createdAt: new Date().toISOString(),
                steps: []
            };
            currentSteps = [];

            // Show UI
            document.getElementById('recordName').textContent = name;
            recordPanel.classList.add('active');
            stepsBar.classList.add('active');
            highlightInfo.classList.add('active');
            updateStepsUI();

            // Setup iframe handlers
            injectIframeStyles();
            setupIframeClickHandler();

            showToast('Bắt đầu ghi! Click vào element trong trang admin.', 'success');
        }

        function togglePause() {
            isPaused = !isPaused;

            const pauseBtn = document.getElementById('btnPause');
            const recordDot = document.getElementById('recordDot');

            if (isPaused) {
                pauseBtn.innerHTML = '<i class="fa fa-play"></i> Tiếp tục';
                pauseBtn.classList.remove('tb-btn-warning');
                pauseBtn.classList.add('tb-btn-info');
                recordPanel.classList.add('paused');
                recordDot.classList.add('paused');
                highlightInfo.classList.remove('active');

                removeIframeClickHandler();

                showToast('Đã tạm dừng! Bạn có thể tương tác website bình thường.', 'info');
            } else {
                pauseBtn.innerHTML = '<i class="fa fa-pause"></i> Tạm dừng';
                pauseBtn.classList.add('tb-btn-warning');
                pauseBtn.classList.remove('tb-btn-info');
                recordPanel.classList.remove('paused');
                recordDot.classList.remove('paused');
                highlightInfo.classList.add('active');

                injectIframeStyles();
                setupIframeClickHandler();

                showToast('Tiếp tục ghi! Click vào element cần highlight.', 'success');
            }
        }

        function cancelRecording() {
            if (currentSteps.length > 0) {
                if (!confirm('Bạn có chắc muốn hủy? Các bước đã ghi sẽ bị mất!')) {
                    return;
                }
            }

            resetRecording();
            showToast('Đã hủy ghi hướng dẫn!', 'info');
        }

        async function finishRecording() {
            if (currentSteps.length === 0) {
                showToast('Chưa có bước nào được ghi!', 'error');
                return;
            }

            currentTutorial.steps = currentSteps;
            tutorials.push(currentTutorial);
            saveTutorials(); // Lưu localStorage

            // Lưu vào database
            console.log('[Tutorial Builder] Saving to database:', currentTutorial);
            const result = await saveTutorialToDB(currentTutorial);

            resetRecording();

            if (result.success) {
                showToast('Đã lưu hướng dẫn vào database!', 'success');
            } else {
                showToast('Đã lưu local, nhưng lưu DB thất bại!', 'warning');
            }
        }

        function resetRecording() {
            isRecording = false;
            isPaused = false;
            currentTutorial = null;
            currentSteps = [];
            selectedElement = null;
            selectedSelector = '';

            recordPanel.classList.remove('active', 'paused');
            stepsBar.classList.remove('active');
            stepInput.classList.remove('active');
            highlightInfo.classList.remove('active');

            // Reset pause button
            const pauseBtn = document.getElementById('btnPause');
            pauseBtn.innerHTML = '<i class="fa fa-pause"></i> Tạm dừng';
            pauseBtn.classList.add('tb-btn-warning');
            pauseBtn.classList.remove('tb-btn-info');

            removeIframeClickHandler();
        }

        // ========================================
        // STEPS
        // ========================================
        function saveStep() {
            const description = document.getElementById('stepDescription').value.trim();

            console.log('Tutorial Builder: saveStep called', { description, selectedSelector, currentStepsCount: currentSteps.length });

            if (!description) {
                showToast('Vui lòng nhập mô tả cho bước này!', 'error');
                document.getElementById('stepDescription').focus();
                return;
            }

            if (!selectedSelector) {
                showToast('Vui lòng chọn element trước!', 'error');
                return;
            }

            let pageUrl = '';
            try {
                pageUrl = iframe.contentWindow.location.pathname + iframe.contentWindow.location.search;
            } catch (e) {
                pageUrl = urlDisplay.value;
            }

            const step = {
                selector: selectedSelector,
                description: description,
                pageUrl: pageUrl,
                timestamp: Date.now()
            };

            currentSteps.push(step);
            console.log('Tutorial Builder: Step added', { step, totalSteps: currentSteps.length });

            updateStepsUI();

            // Clear selection
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.querySelectorAll('.tb-element-selected').forEach(el => {
                    el.classList.remove('tb-element-selected');
                });
            } catch (e) {}

            selectedElement = null;
            selectedSelector = '';
            stepInput.classList.remove('active');
            highlightInfo.classList.add('active');

            showToast(`Đã thêm bước ${currentSteps.length}!`, 'success');
        }

        function cancelStep() {
            // Clear selection
            try {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.querySelectorAll('.tb-element-selected').forEach(el => {
                    el.classList.remove('tb-element-selected');
                });
            } catch (e) {}

            selectedElement = null;
            selectedSelector = '';
            stepInput.classList.remove('active');
            highlightInfo.classList.add('active');
        }

        function removeStep(index) {
            currentSteps.splice(index, 1);
            updateStepsUI();
            showToast('Đã xóa bước!', 'info');
        }

        function updateStepsUI() {
            document.getElementById('stepCount').textContent = currentSteps.length;

            if (currentSteps.length === 0) {
                stepsList.innerHTML = '<span style="color: rgba(255,255,255,0.5); font-size: 12px;">Chưa có bước nào</span>';
                return;
            }

            stepsList.innerHTML = currentSteps.map((step, index) => `
                <div class="tb-step-item" title="${escapeHtml(step.description)}">
                    <span class="step-num">${index + 1}</span>
                    <span class="step-text">${escapeHtml(step.description.substring(0, 20))}${step.description.length > 20 ? '...' : ''}</span>
                    <span class="step-remove" onclick="window.tbRemoveStep(${index})">&times;</span>
                </div>
            `).join('');
        }

        // Expose removeStep globally
        window.tbRemoveStep = removeStep;

        // ========================================
        // DROPDOWN / LIST
        // ========================================
        function toggleDropdown() {
            const dropdown = document.getElementById('tutorialDropdown');
            dropdown.classList.toggle('active');

            if (dropdown.classList.contains('active')) {
                renderDropdown();
            }
        }

        function renderDropdown() {
            const dropdown = document.getElementById('tutorialDropdown');

            if (tutorials.length === 0) {
                dropdown.innerHTML = `
                    <div class="tb-dropdown-empty">
                        <i class="fa fa-folder-open-o"></i>
                        <p>Chưa có hướng dẫn nào</p>
                    </div>
                `;
                return;
            }

            dropdown.innerHTML = `
                <div class="tb-dropdown-header">Danh sách hướng dẫn (${tutorials.length})</div>
                ${tutorials.map((t, i) => `
                    <div class="tb-dropdown-item">
                        <div class="tb-dropdown-item-info">
                            <div class="tb-dropdown-item-name">${escapeHtml(t.name)}</div>
                            <div class="tb-dropdown-item-meta">${t.steps.length} bước • ${formatDate(t.createdAt)}</div>
                        </div>
                        <div class="tb-dropdown-item-actions">
                            <button class="btn-play" onclick="window.tbPlayTutorial('${t.id}')" title="Xem">
                                <i class="fa fa-play"></i>
                            </button>
                            <button class="btn-delete" onclick="window.tbDeleteTutorial('${t.id}')" title="Xóa">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
                <div class="tb-dropdown-footer">
                    <button class="tb-btn tb-btn-secondary" onclick="window.tbExportTutorials()">
                        <i class="fa fa-download"></i> Export
                    </button>
                    <button class="tb-btn tb-btn-secondary" onclick="window.tbImportTutorials()">
                        <i class="fa fa-upload"></i> Import
                    </button>
                </div>
            `;
        }

        window.tbPlayTutorial = function(id) {
            // TODO: Implement play mode
            showToast('Tính năng xem hướng dẫn sẽ được thêm sau!', 'info');
            document.getElementById('tutorialDropdown').classList.remove('active');
        };

        window.tbDeleteTutorial = async function(id) {
            const tutorial = tutorials.find(t => t.id === id);
            if (!tutorial) return;

            if (!confirm(`Bạn có chắc muốn xóa "${tutorial.name}"?`)) {
                return;
            }

            // Xóa khỏi database
            await deleteTutorialFromDB(id);

            tutorials = tutorials.filter(t => t.id !== id);
            saveTutorials();
            renderDropdown();
            showToast('Đã xóa hướng dẫn!', 'success');
        };

        window.tbExportTutorials = function() {
            if (tutorials.length === 0) {
                showToast('Chưa có hướng dẫn nào để export!', 'error');
                return;
            }

            const data = JSON.stringify(tutorials, null, 2);
            const blob = new Blob([data], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'tutorials-' + new Date().toISOString().slice(0, 10) + '.json';
            a.click();
            URL.revokeObjectURL(url);

            showToast(`Đã export ${tutorials.length} hướng dẫn!`, 'success');
        };

        window.tbImportTutorials = function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';
            input.onchange = (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = (event) => {
                    try {
                        const imported = JSON.parse(event.target.result);
                        if (!Array.isArray(imported)) throw new Error('Invalid format');

                        let count = 0;
                        imported.forEach(t => {
                            if (t.name && Array.isArray(t.steps)) {
                                t.id = 'tutorial_' + Date.now() + '_' + count;
                                tutorials.push(t);
                                count++;
                            }
                        });

                        saveTutorials();
                        renderDropdown();
                        showToast(`Đã import ${count} hướng dẫn!`, 'success');
                    } catch (err) {
                        showToast('File không hợp lệ!', 'error');
                    }
                };
                reader.readAsText(file);
            };
            input.click();
        };

        // ========================================
        // MODAL
        // ========================================
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            if (modalId === 'createModal') {
                setTimeout(() => document.getElementById('tutorialName').focus(), 100);
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // ========================================
        // EXIT
        // ========================================
        function exitBuilder() {
            if (isRecording) {
                if (!confirm('Bạn đang ghi hướng dẫn. Bạn có chắc muốn thoát?')) {
                    return;
                }
            }
            window.location.href = 'index.php';
        }

        // ========================================
        // UTILITIES
        // ========================================
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'tb-toast ' + type + ' active';

            setTimeout(() => {
                toast.classList.remove('active');
            }, 3000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('vi-VN');
            } catch (e) {
                return dateStr;
            }
        }

    })();
    </script>
</body>
</html>
