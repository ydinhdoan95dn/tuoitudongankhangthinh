/**
 * ADMIN TUTORIAL BUILDER v4.0
 * Cong cu tao va phat huong dan su dung admin
 *
 * FEATURES:
 * - Record Mode: Click element -> nhap mo ta -> luu buoc (ho tro nhieu buoc)
 * - Play Mode: Xem huong dan voi highlight + popup
 *   + CHAN nguoi dung tuong tac man hinh (chi cho nhan element duoc highlight hoac nut TIEP)
 *   + Luong lien tuc qua cac trang (sessionStorage + cookie backup)
 *   + Doi buoc hoan thanh truoc khi hien nut TIEP (voi action type 'click')
 *   + Ho tro LUI LAI (voi gioi han)
 * - Cross-page: Dieu huong giua cac trang voi tutorial lien tuc
 * - Manage: Danh sach, xoa, chinh sua, export/import JSON
 * - Storage: Database (AJAX API) + localStorage cache
 *
 * v4.0 - Chuyen tu localStorage sang Database
 */

(function() {
    'use strict';

    const STORAGE_KEY = 'admin_tutorials';
    const PLAYING_KEY = 'tutorial_playing';
    const COOKIE_PLAYING_KEY = 'tutorial_playing_cookie';
    // Sử dụng absolute path để đảm bảo hoạt động đúng từ mọi trang
    const API_URL = (function() {
        // Lấy base path từ script src
        const scripts = document.getElementsByTagName('script');
        for (let i = 0; i < scripts.length; i++) {
            const src = scripts[i].src;
            if (src.includes('admin-tutorial.js')) {
                // Lấy thư mục chứa js folder -> lấy parent = dxmt-admin
                const url = src.replace(/js\/admin-tutorial\.js.*$/, 'ajax_tutorial.php');
                console.log('[Tutorial] API_URL resolved to:', url);
                return url;
            }
        }
        // Fallback: relative path
        console.log('[Tutorial] API_URL fallback to: ajax_tutorial.php');
        return 'ajax_tutorial.php';
    })();

    class TutorialBuilder {
        constructor() {
            this.tutorials = [];
            this.isRecording = false;
            this.isPaused = false;
            this.isPlaying = false;
            this.currentTutorial = null;
            this.currentSteps = [];
            this.currentStepIndex = 0;
            this.selectedElement = null;
            this.currentClickHandler = null;
            this.stepCompleted = false;
            this.historyStack = [];
            this.editMode = window.TUTORIAL_EDIT_MODE !== false;
            this.isLoading = false;
            this.dbReady = false;

            this.init();
        }

        // ========================================
        // INITIALIZATION
        // ========================================
        async init() {
            // console.log('[Tutorial] ========== INIT v4.0 (Database) ==========');
            // console.log('[Tutorial] Current URL:', window.location.href);

            this.createHTML();
            this.bindEvents();

            // Kiem tra va tao bang database
            await this.initDatabase();

            // Load tutorials tu database
            await this.loadTutorialsFromDB();

            // Check if we're continuing a tutorial from another page
            this.checkContinueTutorial();
        }

        /**
         * Kiem tra va tao bang database neu chua co
         */
        async initDatabase() {
            try {
                const response = await fetch(API_URL + '?action=check_table', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const result = await response.json();
                console.log('[Tutorial] initDatabase response:', result);

                if (result.success) {
                    this.dbReady = true;
                    console.log('[Tutorial] dbReady set to TRUE');
                    if (result.created) {
                        console.log('[Tutorial] Database table created');
                        // Migrate tu localStorage neu co
                        await this.migrateFromLocalStorage();
                    } else {
                        console.log('[Tutorial] Database table ready');
                    }
                } else {
                    console.error('[Tutorial] Database init failed:', result.message);
                    // Fallback to localStorage
                    this.tutorials = this.loadFromLocalStorage();
                }
            } catch (e) {
                console.error('[Tutorial] Database init error:', e);
                this.tutorials = this.loadFromLocalStorage();
            }
        }

        /**
         * Migrate du lieu tu localStorage sang database (tu dong)
         */
        async migrateFromLocalStorage() {
            const localData = this.loadFromLocalStorage();
            if (localData.length === 0) return;

            // console.log('[Tutorial] Migrating', localData.length, 'tutorials from localStorage');

            for (const tutorial of localData) {
                await this.saveTutorialToDB(tutorial);
            }

            // Xoa localStorage sau khi migrate thanh cong
            localStorage.removeItem(STORAGE_KEY);
            // console.log('[Tutorial] Migration complete');
        }

        /**
         * Migrate thu cong tu localStorage sang database (goi tu UI)
         */
        async manualMigrate() {
            const localData = this.loadFromLocalStorage();

            if (localData.length === 0) {
                this.showToast('Không có dữ liệu trong localStorage để migrate!', 'info');
                return { success: false, message: 'No data' };
            }

            // Hien confirm
            if (!confirm(`Tìm thấy ${localData.length} tutorial trong localStorage.\nBạn có muốn migrate vào database không?`)) {
                return { success: false, message: 'Cancelled' };
            }

            this.showToast('Đang migrate dữ liệu...', 'info');

            let migrated = 0;
            let errors = 0;

            for (const tutorial of localData) {
                try {
                    const result = await this.saveTutorialToDB(tutorial);
                    if (result.success) {
                        migrated++;
                    } else {
                        errors++;
                    }
                } catch (e) {
                    errors++;
                }
            }

            if (migrated > 0) {
                // Xoa localStorage sau khi migrate thanh cong
                localStorage.removeItem(STORAGE_KEY);

                // Reload tu database
                await this.loadTutorialsFromDB();
                this.renderTutorialsList();

                this.showToast(`Đã migrate ${migrated} tutorial vào database!`, 'success');
            }

            if (errors > 0) {
                this.showToast(`Có ${errors} tutorial bị lỗi khi migrate!`, 'error');
            }

            return { success: true, migrated, errors };
        }

        /**
         * Kiem tra co du lieu trong localStorage khong (de hien nut migrate)
         */
        hasLocalStorageData() {
            const localData = this.loadFromLocalStorage();
            return localData.length > 0;
        }

        /**
         * Kiem tra va hien thi migrate alert neu co du lieu trong localStorage
         */
        checkAndShowMigrateAlert() {
            const alertEl = document.getElementById('tutorialMigrateAlert');
            const countEl = document.getElementById('localStorageCount');
            if (!alertEl) return;

            const localData = this.loadFromLocalStorage();

            // Chi hien alert neu co du lieu trong localStorage va chua co trong DB
            // Kiem tra bang cach so sanh ID
            const localOnlyData = localData.filter(local => {
                return !this.tutorials.some(db => db.id === local.id);
            });

            if (localOnlyData.length > 0) {
                if (countEl) countEl.textContent = localOnlyData.length;
                alertEl.style.display = 'flex';
            } else {
                alertEl.style.display = 'none';
            }
        }

        /**
         * Load tutorials tu localStorage (fallback/cache)
         */
        loadFromLocalStorage() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
            } catch (e) {
                return [];
            }
        }

        /**
         * Load tutorials tu database
         */
        async loadTutorialsFromDB() {
            if (!this.dbReady) {
                this.tutorials = this.loadFromLocalStorage();
                return;
            }

            try {
                this.isLoading = true;
                const response = await fetch(API_URL + '?action=list', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const result = await response.json();

                if (result.success) {
                    this.tutorials = result.data || [];
                    // Cache vao localStorage
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(this.tutorials));
                    // console.log('[Tutorial] Loaded', this.tutorials.length, 'tutorials from DB');
                } else {
                    console.error('[Tutorial] Load failed:', result.message);
                    this.tutorials = this.loadFromLocalStorage();
                }
            } catch (e) {
                console.error('[Tutorial] Load error:', e);
                this.tutorials = this.loadFromLocalStorage();
            } finally {
                this.isLoading = false;
            }
        }

        /**
         * Luu tutorial vao database
         */
        async saveTutorialToDB(tutorial) {
            console.log('[Tutorial] saveTutorialToDB called, dbReady =', this.dbReady);

            if (!this.dbReady) {
                // Fallback: luu vao localStorage
                console.warn('[Tutorial] dbReady is FALSE, saving to localStorage only');
                this.saveToLocalStorage();
                return { success: true };
            }

            try {
                const formData = new FormData();
                formData.append('tutorial_id', tutorial.id);
                formData.append('name', tutorial.name);
                formData.append('steps', JSON.stringify(tutorial.steps));

                console.log('[Tutorial] Sending to API:', {
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
                console.log('[Tutorial] Save API response:', result);

                if (result.success) {
                    console.log('[Tutorial] Saved to DB:', tutorial.name);
                    // Update cache
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(this.tutorials));
                } else {
                    console.error('[Tutorial] Save to DB failed:', result.message);
                }
                return result;
            } catch (e) {
                console.error('[Tutorial] Save error:', e);
                this.saveToLocalStorage();
                return { success: false, message: e.message };
            }
        }

        /**
         * Xoa tutorial khoi database
         */
        async deleteTutorialFromDB(tutorialId) {
            if (!this.dbReady) {
                this.saveToLocalStorage();
                return { success: true };
            }

            try {
                const formData = new FormData();
                formData.append('tutorial_id', tutorialId);

                const response = await fetch(API_URL + '?action=delete', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // console.log('[Tutorial] Deleted from DB:', tutorialId);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(this.tutorials));
                }
                return result;
            } catch (e) {
                console.error('[Tutorial] Delete error:', e);
                return { success: false, message: e.message };
            }
        }

        /**
         * Luu vao localStorage (fallback)
         */
        saveToLocalStorage() {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(this.tutorials));
        }

        createHTML() {
            // Dropdown Menu - append vao button wrapper
            const btnWrapper = document.querySelector('.tutorial-btn-wrapper');
            if (btnWrapper) {
                // Chỉ hiện các mục edit khi ở edit mode
                const dropdownHTML = this.editMode ? `
                    <div class="tutorial-dropdown" id="tutorialDropdown">
                        <div class="tutorial-dropdown-item" data-action="create">
                            <i class="fa fa-plus-circle"></i> Tạo hướng dẫn mới
                        </div>
                        <div class="tutorial-dropdown-item" data-action="list">
                            <i class="fa fa-list"></i> Danh sách hướng dẫn
                        </div>
                        <div class="tutorial-dropdown-divider"></div>
                        <div class="tutorial-dropdown-item" data-action="export">
                            <i class="fa fa-download"></i> Export JSON
                        </div>
                        <div class="tutorial-dropdown-item" data-action="import">
                            <i class="fa fa-upload"></i> Import JSON
                        </div>
                    </div>
                ` : `
                    <div class="tutorial-dropdown" id="tutorialDropdown">
                        <div class="tutorial-dropdown-item" data-action="list">
                            <i class="fa fa-list"></i> Danh sách hướng dẫn
                        </div>
                    </div>
                `;
                btnWrapper.insertAdjacentHTML('beforeend', dropdownHTML);
            }

            // Cac modal va panel - append vao body
            const html = `
                <!-- Record Panel -->
                <div class="tutorial-record-panel" id="tutorialRecordPanel">
                    <div class="tutorial-record-header">
                        <div class="tutorial-record-status">
                            <span class="record-dot"></span>
                            <span>ĐANG GHI:</span>
                            <span class="tutorial-record-name" id="recordingName"></span>
                        </div>
                        <button class="tutorial-record-close" id="closeRecordPanel">&times;</button>
                    </div>
                    <div class="tutorial-record-body">
                        <div class="tutorial-record-info">
                            <div class="tutorial-record-step">
                                Bước <strong id="currentStepNum">1</strong>
                            </div>
                            <div class="tutorial-record-instruction">
                                Click vào element trên trang cần highlight
                            </div>
                            <div class="tutorial-record-url">
                                <label>Trang:</label>
                                <code id="currentPageUrl">${window.location.pathname + window.location.search}</code>
                            </div>
                            <div class="tutorial-record-selector">
                                <label>Element:</label>
                                <code id="selectedSelector">Chưa chọn</code>
                            </div>
                            <div class="tutorial-record-action-type">
                                <label>Loại thao tác:</label>
                                <div class="action-type-options">
                                    <label class="action-type-option" data-type="highlight">
                                        <input type="radio" name="actionType" value="highlight" checked>
                                        <span class="action-type-label">
                                            <i class="fa fa-eye"></i> CHỈ XEM
                                        </span>
                                        <small>Highlight để giải thích</small>
                                    </label>
                                    <label class="action-type-option" data-type="click">
                                        <input type="radio" name="actionType" value="click">
                                        <span class="action-type-label">
                                            <i class="fa fa-hand-pointer-o"></i> CLICK
                                        </span>
                                        <small>Click để thực hiện</small>
                                    </label>
                                    <label class="action-type-option" data-type="navigate">
                                        <input type="radio" name="actionType" value="navigate">
                                        <span class="action-type-label">
                                            <i class="fa fa-external-link"></i> ĐIỀU HƯỚNG
                                        </span>
                                        <small>Chuyển trang mới</small>
                                    </label>
                                </div>
                            </div>
                            <div class="tutorial-record-desc">
                                <textarea id="stepDescription" placeholder="Nhập mô tả cho bước này..."></textarea>
                            </div>
                        </div>
                        <div class="tutorial-record-actions">
                            <button class="btn-save-step" id="btnSaveStep" disabled>
                                <i class="fa fa-plus"></i> Thêm bước
                            </button>
                            <button class="btn-undo-step" id="btnUndoStep" disabled>
                                <i class="fa fa-undo"></i> Xóa bước cuối
                            </button>
                            <button class="btn-pause-record" id="btnPauseRecord">
                                <i class="fa fa-pause"></i> Tạm dừng
                            </button>
                            <button class="btn-finish-record" id="btnFinishRecord">
                                <i class="fa fa-save"></i> Hoàn tất
                            </button>
                        </div>
                    </div>
                    <div class="tutorial-steps-preview" id="stepsPreview">
                        <div class="steps-preview-title">Các bước đã ghi:</div>
                        <div class="steps-preview-list" id="stepsPreviewList"></div>
                    </div>
                </div>

                <!-- Create Modal -->
                <div class="tutorial-modal" id="tutorialCreateModal">
                    <div class="tutorial-modal-content">
                        <div class="tutorial-modal-header">
                            <h3>Tạo hướng dẫn mới</h3>
                            <button class="tutorial-modal-close" data-close="tutorialCreateModal">&times;</button>
                        </div>
                        <div class="tutorial-modal-body">
                            <div class="form-group">
                                <label>Tên hướng dẫn:</label>
                                <input type="text" id="tutorialName" placeholder="VD: Hướng dẫn thêm sản phẩm">
                            </div>
                        </div>
                        <div class="tutorial-modal-footer">
                            <button class="btn-cancel" data-close="tutorialCreateModal">Huỷ</button>
                            <button class="btn-primary" id="btnStartRecord">
                                <i class="fa fa-circle"></i> Bắt đầu ghi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- List Modal -->
                <div class="tutorial-modal" id="tutorialListModal">
                    <div class="tutorial-modal-content tutorial-modal-lg">
                        <div class="tutorial-modal-header">
                            <h3>Danh sách hướng dẫn</h3>
                            <button class="tutorial-modal-close" data-close="tutorialListModal">&times;</button>
                        </div>
                        <div class="tutorial-modal-body">
                            <!-- Search Box -->
                            <div class="tutorial-search-box">
                                <i class="fa fa-search"></i>
                                <input type="text" id="tutorialSearchInput" placeholder="Tìm kiếm hướng dẫn..." oninput="tutorialBuilder.filterTutorials(this.value)">
                                <button class="tutorial-search-clear" onclick="tutorialBuilder.clearSearch()" title="Xóa">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <div id="tutorialMigrateAlert" class="tutorial-migrate-alert" style="display:none;">
                                <i class="fa fa-exclamation-triangle"></i>
                                <span>Phát hiện <strong id="localStorageCount">0</strong> tutorial trong localStorage!</span>
                                <button class="btn-migrate" onclick="tutorialBuilder.manualMigrate()">
                                    <i class="fa fa-database"></i> Migrate vào Database
                                </button>
                            </div>
                            <div class="tutorial-list" id="tutorialList"></div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="tutorial-modal" id="tutorialEditModal">
                    <div class="tutorial-modal-content tutorial-modal-lg">
                        <div class="tutorial-modal-header">
                            <h3>Chỉnh sửa hướng dẫn</h3>
                            <button class="tutorial-modal-close" data-close="tutorialEditModal">&times;</button>
                        </div>
                        <div class="tutorial-modal-body">
                            <div class="form-group">
                                <label>Tên hướng dẫn:</label>
                                <input type="text" id="editTutorialName">
                            </div>
                            <div class="edit-steps-list" id="editStepsList"></div>
                        </div>
                        <div class="tutorial-modal-footer">
                            <button class="btn-cancel" data-close="tutorialEditModal">Huỷ</button>
                            <button class="btn-primary" id="btnSaveEdit">
                                <i class="fa fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Play Overlay - CHAN TUONG TAC -->
                <div class="tutorial-overlay" id="tutorialOverlay"></div>

                <!-- Play Popup -->
                <div class="tutorial-play-popup" id="tutorialPlayPopup">
                    <div class="tutorial-play-header">
                        <span class="tutorial-play-title" id="playTutorialName"></span>
                        <span class="tutorial-play-progress">
                            Bước <span id="playCurrentStep">1</span>/<span id="playTotalSteps">1</span>
                        </span>
                        <button class="tutorial-play-close" id="closePopup">&times;</button>
                    </div>
                    <div class="tutorial-play-body">
                        <div class="tutorial-play-content" id="playStepContent"></div>
                    </div>
                    <div class="tutorial-play-footer">
                        <button class="btn-prev" id="btnPrevStep" disabled>
                            <i class="fa fa-chevron-left"></i> Lùi lại
                        </button>
                        <button class="btn-next" id="btnNextStep">
                            Tiếp <i class="fa fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Highlight Box -->
                <div class="tutorial-highlight" id="tutorialHighlight"></div>

                <!-- Clickable Area - Chi cho click vao element duoc highlight -->
                <div class="tutorial-clickable-area" id="tutorialClickableArea"></div>

                <!-- Import Input -->
                <input type="file" id="importInput" accept=".json" style="display:none">

                <!-- Toast -->
                <div class="tutorial-toast" id="tutorialToast"></div>
            `;

            const container = document.createElement('div');
            container.id = 'tutorialContainer';
            container.innerHTML = html;
            document.body.appendChild(container);
        }

        bindEvents() {
            // Dropdown toggle
            const btn = document.getElementById('tutorialBtn');
            if (btn) {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggleDropdown();
                });
            }

            // Dropdown items
            document.querySelectorAll('.tutorial-dropdown-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const action = e.currentTarget.dataset.action;
                    this.handleDropdownAction(action);
                    this.closeDropdown();
                });
            });

            // Close dropdown on outside click
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.tutorial-btn-wrapper')) {
                    this.closeDropdown();
                }
            });

            // Modal close buttons
            document.querySelectorAll('[data-close]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const modalId = e.currentTarget.dataset.close;
                    this.closeModal(modalId);
                });
            });

            // Start record button
            document.getElementById('btnStartRecord')?.addEventListener('click', () => {
                this.startRecording();
            });

            // Record panel buttons
            document.getElementById('btnSaveStep')?.addEventListener('click', () => this.saveStep());
            document.getElementById('btnUndoStep')?.addEventListener('click', () => this.undoStep());
            document.getElementById('btnPauseRecord')?.addEventListener('click', () => this.togglePause());
            document.getElementById('btnFinishRecord')?.addEventListener('click', () => this.finishRecording());
            document.getElementById('closeRecordPanel')?.addEventListener('click', () => this.cancelRecording());

            // Play mode buttons
            document.getElementById('btnPrevStep')?.addEventListener('click', () => this.prevStep());
            document.getElementById('btnNextStep')?.addEventListener('click', () => this.nextStep());
            document.getElementById('closePopup')?.addEventListener('click', () => this.stopPlaying());

            // Edit save button
            document.getElementById('btnSaveEdit')?.addEventListener('click', () => this.saveEdit());

            // Import input
            document.getElementById('importInput')?.addEventListener('change', (e) => this.handleImport(e));

            // Description textarea
            document.getElementById('stepDescription')?.addEventListener('input', () => {
                this.updateSaveButtonState();
            });

            // Enter key in tutorial name input
            document.getElementById('tutorialName')?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.startRecording();
                }
            });

            // Clickable area click handler
            document.getElementById('tutorialClickableArea')?.addEventListener('click', (e) => {
                this.handleClickableAreaClick(e);
            });
        }

        // ========================================
        // COOKIE HELPERS (backup cho cross-page)
        // ========================================
        setCookie(name, value, minutes = 10) {
            const expires = new Date(Date.now() + minutes * 60 * 1000).toUTCString();
            document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
        }

        getCookie(name) {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? decodeURIComponent(match[2]) : null;
        }

        deleteCookie(name) {
            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/`;
        }

        // ========================================
        // DROPDOWN
        // ========================================
        toggleDropdown() {
            const dropdown = document.getElementById('tutorialDropdown');
            dropdown?.classList.toggle('active');
        }

        closeDropdown() {
            const dropdown = document.getElementById('tutorialDropdown');
            dropdown?.classList.remove('active');
        }

        handleDropdownAction(action) {
            switch (action) {
                case 'create':
                    this.openModal('tutorialCreateModal');
                    document.getElementById('tutorialName')?.focus();
                    break;
                case 'list':
                    this.renderTutorialsList();
                    this.openModal('tutorialListModal');
                    break;
                case 'export':
                    this.exportTutorials();
                    break;
                case 'import':
                    document.getElementById('importInput')?.click();
                    break;
            }
        }

        // ========================================
        // MODAL HELPERS
        // ========================================
        openModal(modalId) {
            document.getElementById(modalId)?.classList.add('active');
        }

        closeModal(modalId) {
            document.getElementById(modalId)?.classList.remove('active');
        }

        // ========================================
        // RECORDING MODE
        // ========================================
        startRecording() {
            const nameInput = document.getElementById('tutorialName');
            const name = nameInput?.value.trim();

            if (!name) {
                this.showToast('Vui lòng nhập tên hướng dẫn!', 'error');
                nameInput?.focus();
                return;
            }

            this.closeModal('tutorialCreateModal');
            nameInput.value = '';

            this.isRecording = true;
            this.currentTutorial = {
                id: 'tutorial_' + Date.now(),
                name: name,
                createdAt: new Date().toISOString(),
                steps: []
            };
            this.currentSteps = [];
            this.selectedElement = null;

            // Show record panel
            document.getElementById('recordingName').textContent = name;
            document.getElementById('currentStepNum').textContent = '1';
            document.getElementById('selectedSelector').textContent = 'Chưa chọn';
            document.getElementById('stepDescription').value = '';
            document.getElementById('stepsPreviewList').innerHTML = '';
            document.getElementById('tutorialRecordPanel')?.classList.add('active');
            document.body.classList.add('tutorial-record-mode');

            this.updateRecordButtons();

            // Add click listener for element selection
            this.elementClickHandler = (e) => this.handleElementClick(e);
            this.elementHoverHandler = (e) => this.handleElementHover(e);
            this.elementHoverOutHandler = (e) => this.handleElementHoverOut(e);

            setTimeout(() => {
                document.addEventListener('click', this.elementClickHandler, true);
                document.addEventListener('mouseover', this.elementHoverHandler, true);
                document.addEventListener('mouseout', this.elementHoverOutHandler, true);
            }, 100);

            this.showToast('Bắt đầu ghi! Click vào element cần highlight.', 'success');
        }

        handleElementClick(e) {
            // Ignore clicks on tutorial UI
            if (e.target.closest('#tutorialContainer') ||
                e.target.closest('.tutorial-record-panel') ||
                e.target.closest('.tutorial-btn-wrapper') ||
                e.target.closest('.tutorial-highlight')) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            this.selectedElement = e.target;
            const selector = this.generateSelector(e.target);

            document.getElementById('selectedSelector').textContent = selector;
            document.getElementById('currentPageUrl').textContent = window.location.pathname + window.location.search;

            // Highlight selected element
            this.highlightElement(e.target, 'selected');

            this.updateSaveButtonState();

            // Focus description
            document.getElementById('stepDescription')?.focus();
        }

        handleElementHover(e) {
            if (!this.isRecording || this.isPaused) return;
            if (e.target.closest('#tutorialContainer') ||
                e.target.closest('.tutorial-record-panel') ||
                e.target.closest('.tutorial-btn-wrapper')) {
                return;
            }

            this.highlightElement(e.target, 'hover');
        }

        handleElementHoverOut(e) {
            if (!this.isRecording || this.isPaused) return;
            if (this.selectedElement) {
                this.highlightElement(this.selectedElement, 'selected');
            } else {
                this.hideHighlight();
            }
        }

        generateSelector(element) {
            // Try ID first
            if (element.id) {
                return '#' + element.id;
            }

            // Build path
            const path = [];
            let current = element;

            while (current && current !== document.body) {
                let selector = current.tagName.toLowerCase();

                if (current.id) {
                    selector = '#' + current.id;
                    path.unshift(selector);
                    break;
                } else if (current.className && typeof current.className === 'string') {
                    const classes = current.className.trim().split(/\s+/).filter(c =>
                        c && !c.startsWith('tutorial-') && !c.startsWith('hover') && !c.startsWith('active')
                    );
                    if (classes.length > 0) {
                        selector += '.' + classes.slice(0, 2).join('.');
                    }
                }

                // Add nth-child if needed
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

        highlightElement(element, type = 'hover') {
            const highlight = document.getElementById('tutorialHighlight');
            if (!highlight || !element) return;

            const rect = element.getBoundingClientRect();
            highlight.style.top = (rect.top + window.scrollY - 4) + 'px';
            highlight.style.left = (rect.left + window.scrollX - 4) + 'px';
            highlight.style.width = (rect.width + 8) + 'px';
            highlight.style.height = (rect.height + 8) + 'px';
            highlight.className = 'tutorial-highlight ' + type;
            highlight.style.display = 'block';
        }

        hideHighlight() {
            const highlight = document.getElementById('tutorialHighlight');
            if (highlight) {
                highlight.style.display = 'none';
            }
        }

        updateSaveButtonState() {
            const btn = document.getElementById('btnSaveStep');
            const desc = document.getElementById('stepDescription')?.value.trim();
            const selector = document.getElementById('selectedSelector')?.textContent;

            if (btn) {
                btn.disabled = !desc || !selector || selector === 'Chưa chọn';
            }
        }

        updateRecordButtons() {
            const undoBtn = document.getElementById('btnUndoStep');
            if (undoBtn) {
                undoBtn.disabled = this.currentSteps.length === 0;
            }
        }

        saveStep() {
            const selector = document.getElementById('selectedSelector')?.textContent;
            const description = document.getElementById('stepDescription')?.value.trim();
            const pageUrl = document.getElementById('currentPageUrl')?.textContent;
            const actionType = document.querySelector('input[name="actionType"]:checked')?.value || 'highlight';

            if (!selector || selector === 'Chưa chọn' || !description) {
                this.showToast('Vui lòng chọn element và nhập mô tả!', 'error');
                return;
            }

            const step = {
                selector: selector,
                description: description,
                pageUrl: pageUrl || (window.location.pathname + window.location.search),
                actionType: actionType,
                timestamp: Date.now()
            };

            this.currentSteps.push(step);
            this.renderStepsPreview();

            // Reset for next step
            this.selectedElement = null;
            document.getElementById('selectedSelector').textContent = 'Chưa chọn';
            document.getElementById('stepDescription').value = '';
            document.getElementById('currentStepNum').textContent = this.currentSteps.length + 1;
            // Reset action type ve mac dinh (highlight)
            const defaultActionType = document.querySelector('input[name="actionType"][value="highlight"]');
            if (defaultActionType) defaultActionType.checked = true;
            this.hideHighlight();
            this.updateSaveButtonState();
            this.updateRecordButtons();

            this.showToast(`Đã lưu bước ${this.currentSteps.length}!`, 'success');
        }

        undoStep() {
            if (this.currentSteps.length > 0) {
                this.currentSteps.pop();
                this.renderStepsPreview();
                document.getElementById('currentStepNum').textContent = this.currentSteps.length + 1;
                this.updateRecordButtons();
                this.showToast('Đã xóa bước cuối!', 'success');
            }
        }

        renderStepsPreview() {
            const list = document.getElementById('stepsPreviewList');
            if (!list) return;

            if (this.currentSteps.length === 0) {
                list.innerHTML = '<div class="no-steps">Chưa có bước nào</div>';
                return;
            }

            const actionTypeLabels = {
                'highlight': { icon: 'fa-eye', text: 'Xem', class: 'type-highlight' },
                'click': { icon: 'fa-hand-pointer-o', text: 'Click', class: 'type-click' },
                'navigate': { icon: 'fa-external-link', text: 'Điều hướng', class: 'type-navigate' }
            };

            list.innerHTML = this.currentSteps.map((step, index) => {
                const typeInfo = actionTypeLabels[step.actionType] || actionTypeLabels['highlight'];
                return `
                <div class="step-preview-item">
                    <div class="step-preview-num">${index + 1}</div>
                    <div class="step-preview-content">
                        <div class="step-preview-desc">${this.escapeHtml(step.description)}</div>
                        <div class="step-preview-meta">
                            <span class="step-action-type ${typeInfo.class}"><i class="fa ${typeInfo.icon}"></i> ${typeInfo.text}</span>
                            <code>${this.escapeHtml(step.selector.substring(0, 40))}${step.selector.length > 40 ? '...' : ''}</code>
                        </div>
                    </div>
                </div>
            `}).join('');
        }

        async finishRecording() {
            console.log('[Tutorial] finishRecording called');
            console.log('[Tutorial] currentSteps.length =', this.currentSteps.length);

            if (this.currentSteps.length === 0) {
                this.showToast('Chưa có bước nào được ghi!', 'error');
                return;
            }

            // Remove event listeners
            document.removeEventListener('click', this.elementClickHandler, true);
            document.removeEventListener('mouseover', this.elementHoverHandler, true);
            document.removeEventListener('mouseout', this.elementHoverOutHandler, true);

            // Save tutorial
            this.currentTutorial.steps = this.currentSteps;
            this.tutorials.push(this.currentTutorial);

            console.log('[Tutorial] Calling saveTutorialToDB with:', this.currentTutorial);

            // Luu vao database
            const result = await this.saveTutorialToDB(this.currentTutorial);
            console.log('[Tutorial] saveTutorialToDB result:', result);

            // Reset state
            this.isRecording = false;
            this.isPaused = false;
            this.currentTutorial = null;
            this.currentSteps = [];
            this.selectedElement = null;

            // Hide panel
            document.getElementById('tutorialRecordPanel')?.classList.remove('active');
            document.body.classList.remove('tutorial-record-mode');
            document.body.classList.remove('tutorial-paused-mode');
            this.hideHighlight();

            if (result.success) {
                this.showToast('Đã lưu hướng dẫn thành công!', 'success');
            } else {
                this.showToast('Lưu vào DB thất bại, đã lưu local!', 'info');
            }
        }

        cancelRecording() {
            if (this.currentSteps.length > 0) {
                if (!confirm('Bạn có chắc muốn huỷ? Các bước đã ghi sẽ bị mất!')) {
                    return;
                }
            }

            // Remove event listeners
            document.removeEventListener('click', this.elementClickHandler, true);
            document.removeEventListener('mouseover', this.elementHoverHandler, true);
            document.removeEventListener('mouseout', this.elementHoverOutHandler, true);

            // Reset state
            this.isRecording = false;
            this.isPaused = false;
            this.currentTutorial = null;
            this.currentSteps = [];
            this.selectedElement = null;

            // Hide panel
            document.getElementById('tutorialRecordPanel')?.classList.remove('active');
            document.body.classList.remove('tutorial-record-mode');
            document.body.classList.remove('tutorial-paused-mode');
            this.hideHighlight();

            this.showToast('Đã huỷ ghi hướng dẫn!', 'info');
        }

        togglePause() {
            if (!this.isRecording) return;

            this.isPaused = !this.isPaused;

            const pauseBtn = document.getElementById('btnPauseRecord');
            const recordPanel = document.getElementById('tutorialRecordPanel');
            const recordDot = document.querySelector('.tutorial-record-status .record-dot');

            if (this.isPaused) {
                // Tam dung - bo event listeners
                document.removeEventListener('click', this.elementClickHandler, true);
                document.removeEventListener('mouseover', this.elementHoverHandler, true);
                document.removeEventListener('mouseout', this.elementHoverOutHandler, true);

                // Cap nhat UI
                pauseBtn.innerHTML = '<i class="fa fa-play"></i> Tiếp tục';
                pauseBtn.classList.add('paused');
                recordPanel?.classList.add('paused');
                recordDot?.classList.add('paused');
                document.body.classList.remove('tutorial-record-mode');
                document.body.classList.add('tutorial-paused-mode');
                this.hideHighlight();

                this.showToast('Đã tạm dừng! Bạn có thể tương tác website bình thường.', 'info');
            } else {
                // Tiep tuc ghi - them lai event listeners
                setTimeout(() => {
                    document.addEventListener('click', this.elementClickHandler, true);
                    document.addEventListener('mouseover', this.elementHoverHandler, true);
                    document.addEventListener('mouseout', this.elementHoverOutHandler, true);
                }, 100);

                // Cap nhat UI
                pauseBtn.innerHTML = '<i class="fa fa-pause"></i> Tạm dừng';
                pauseBtn.classList.remove('paused');
                recordPanel?.classList.remove('paused');
                recordDot?.classList.remove('paused');
                document.body.classList.add('tutorial-record-mode');
                document.body.classList.remove('tutorial-paused-mode');

                this.showToast('Tiếp tục ghi! Click vào element cần highlight.', 'success');
            }
        }

        // ========================================
        // LIST & MANAGEMENT
        // ========================================
        async renderTutorialsList() {
            const list = document.getElementById('tutorialList');
            if (!list) return;

            // Kiem tra va hien thi migrate alert
            this.checkAndShowMigrateAlert();

            // Refresh tu DB
            await this.loadTutorialsFromDB();

            if (this.tutorials.length === 0) {
                // Nếu không có tutorial và đang ở edit mode thì hiện nút tạo mới
                if (this.editMode) {
                    list.innerHTML = `
                        <div class="tutorial-empty">
                            <i class="fa fa-folder-open-o fa-3x"></i>
                            <p>Chưa có hướng dẫn nào</p>
                            <button class="btn-primary" onclick="tutorialBuilder.closeModal('tutorialListModal'); tutorialBuilder.openModal('tutorialCreateModal');">
                                <i class="fa fa-plus"></i> Tạo hướng dẫn đầu tiên
                            </button>
                        </div>
                    `;
                } else {
                    list.innerHTML = `
                        <div class="tutorial-empty">
                            <i class="fa fa-folder-open-o fa-3x"></i>
                            <p>Chưa có hướng dẫn nào</p>
                        </div>
                    `;
                }
                return;
            }

            list.innerHTML = this.tutorials.map((tutorial, index) => `
                <div class="tutorial-list-item" data-id="${tutorial.id}">
                    <div class="tutorial-list-info">
                        <div class="tutorial-list-name">${this.escapeHtml(tutorial.name)}</div>
                        <div class="tutorial-list-meta">
                            <span><i class="fa fa-list-ol"></i> ${tutorial.steps.length} bước</span>
                            <span><i class="fa fa-clock-o"></i> ${this.formatDate(tutorial.createdAt)}</span>
                            <span class="tutorial-db-badge"><i class="fa fa-database"></i> DB</span>
                        </div>
                    </div>
                    <div class="tutorial-list-actions">
                        <button class="btn-play" onclick="tutorialBuilder.playTutorial('${tutorial.id}')" title="Xem">
                            <i class="fa fa-play"></i>
                        </button>
                        ${this.editMode ? `
                        <button class="btn-edit" onclick="tutorialBuilder.editTutorial('${tutorial.id}')" title="Sửa">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn-delete" onclick="tutorialBuilder.deleteTutorial('${tutorial.id}')" title="Xóa">
                            <i class="fa fa-trash"></i>
                        </button>` : ''}
                    </div>
                </div>
            `).join('');
        }

        async deleteTutorial(id) {
            const tutorial = this.tutorials.find(t => t.id === id);
            if (!tutorial) return;

            if (!confirm(`Bạn có chắc muốn xóa hướng dẫn "${tutorial.name}"?`)) {
                return;
            }

            // Xoa khoi database
            await this.deleteTutorialFromDB(id);

            this.tutorials = this.tutorials.filter(t => t.id !== id);
            this.saveToLocalStorage();
            this.renderTutorialsList();
            this.showToast('Đã xóa hướng dẫn!', 'success');
        }

        editTutorial(id) {
            const tutorial = this.tutorials.find(t => t.id === id);
            if (!tutorial) return;

            this.editingTutorialId = id;

            document.getElementById('editTutorialName').value = tutorial.name;

            const stepsList = document.getElementById('editStepsList');
            stepsList.innerHTML = tutorial.steps.map((step, index) => `
                <div class="edit-step-item" data-index="${index}">
                    <div class="edit-step-header">
                        <span class="edit-step-num">Bước ${index + 1}</span>
                        <button class="btn-remove-step" onclick="tutorialBuilder.removeEditStep(${index})">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div class="edit-step-body">
                        <div class="form-group">
                            <label>Mô tả:</label>
                            <textarea class="edit-step-desc" data-index="${index}">${this.escapeHtml(step.description)}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Selector:</label>
                            <input type="text" class="edit-step-selector" data-index="${index}" value="${this.escapeHtml(step.selector)}">
                        </div>
                        <div class="form-group">
                            <label>Trang:</label>
                            <input type="text" class="edit-step-url" data-index="${index}" value="${this.escapeHtml(step.pageUrl || '')}">
                        </div>
                        <div class="form-group">
                            <label>Loại thao tác:</label>
                            <select class="edit-step-action-type" data-index="${index}" data-selected="${step.actionType || 'highlight'}" onchange="this.dataset.selected=this.value">
                                <option value="highlight" ${(step.actionType || 'highlight') === 'highlight' ? 'selected' : ''}>Chỉ xem (highlight)</option>
                                <option value="click" ${step.actionType === 'click' ? 'selected' : ''}>Click thực hiện</option>
                                <option value="navigate" ${step.actionType === 'navigate' ? 'selected' : ''}>Điều hướng trang</option>
                            </select>
                        </div>
                    </div>
                </div>
            `).join('');

            this.closeModal('tutorialListModal');
            this.openModal('tutorialEditModal');
        }

        removeEditStep(index) {
            const item = document.querySelector(`.edit-step-item[data-index="${index}"]`);
            if (item) {
                item.remove();
                // Re-number remaining steps
                document.querySelectorAll('.edit-step-item').forEach((el, i) => {
                    el.dataset.index = i;
                    el.querySelector('.edit-step-num').textContent = `Bước ${i + 1}`;
                    el.querySelector('.edit-step-desc').dataset.index = i;
                    el.querySelector('.edit-step-selector').dataset.index = i;
                    el.querySelector('.edit-step-url').dataset.index = i;
                    el.querySelector('.edit-step-action-type').dataset.index = i;
                    el.querySelector('.btn-remove-step').setAttribute('onclick', `tutorialBuilder.removeEditStep(${i})`);
                });
            }
        }

        async saveEdit() {
            const tutorial = this.tutorials.find(t => t.id === this.editingTutorialId);
            if (!tutorial) return;

            const name = document.getElementById('editTutorialName').value.trim();
            if (!name) {
                this.showToast('Vui lòng nhập tên hướng dẫn!', 'error');
                return;
            }

            const steps = [];
            document.querySelectorAll('.edit-step-item').forEach((item) => {
                const desc = item.querySelector('.edit-step-desc').value.trim();
                const selector = item.querySelector('.edit-step-selector').value.trim();
                const url = item.querySelector('.edit-step-url').value.trim();
                const actionType = item.querySelector('.edit-step-action-type').value || 'highlight';

                if (desc && selector) {
                    steps.push({
                        description: desc,
                        selector: selector,
                        pageUrl: url,
                        actionType: actionType,
                        timestamp: Date.now()
                    });
                }
            });

            if (steps.length === 0) {
                this.showToast('Cần ít nhất 1 bước!', 'error');
                return;
            }

            tutorial.name = name;
            tutorial.steps = steps;

            // Luu vao database
            await this.saveTutorialToDB(tutorial);

            this.closeModal('tutorialEditModal');
            this.showToast('Đã lưu thay đổi!', 'success');
        }

        // ========================================
        // PLAY MODE - HOAN CHINH
        // ========================================
        playTutorial(id) {
            const tutorial = this.tutorials.find(t => t.id === id);
            if (!tutorial || tutorial.steps.length === 0) {
                this.showToast('Hướng dẫn không hợp lệ!', 'error');
                return;
            }

            this.closeModal('tutorialListModal');

            this.isPlaying = true;
            this.currentTutorial = tutorial;
            this.currentStepIndex = 0;
            this.stepCompleted = false;
            this.historyStack = []; // Reset history

            // Check if first step is on current page
            const firstStep = tutorial.steps[0];
            const currentUrl = window.location.pathname + window.location.search;

            if (firstStep.pageUrl && !this.urlsMatch(firstStep.pageUrl, currentUrl)) {
                // Save state and navigate
                this.savePlayingState(id, 0);
                window.location.href = this.normalizeUrl(firstStep.pageUrl);
                return;
            }

            this.showPlayUI();
            this.showStep(0);
        }

        checkContinueTutorial() {
            try {
                const state = this.getPlayingState();

                if (state) {
                    // console.log('[Tutorial] Continuing tutorial from state:', state);

                    const tutorial = this.tutorials.find(t => t.id === state.tutorialId);
                    if (tutorial) {
                        this.isPlaying = true;
                        this.currentTutorial = tutorial;
                        this.currentStepIndex = state.stepIndex;
                        this.stepCompleted = false;
                        this.historyStack = state.history || [];

                        // Delay de dam bao trang load xong
                        setTimeout(() => {
                            this.showPlayUI();
                            this.showStep(state.stepIndex);

                            // Clear state sau khi hien thi thanh cong
                            setTimeout(() => {
                                this.clearPlayingState();
                            }, 1000);
                        }, 500);
                    } else {
                        this.clearPlayingState();
                    }
                }
            } catch (e) {
                console.error('Error checking continue tutorial:', e);
                this.clearPlayingState();
            }
        }

        savePlayingState(tutorialId, stepIndex) {
            const state = JSON.stringify({
                tutorialId: tutorialId,
                stepIndex: stepIndex,
                history: this.historyStack,
                timestamp: Date.now()
            });

            // Luu vao ca sessionStorage va cookie lam backup
            try {
                sessionStorage.setItem(PLAYING_KEY, state);
                this.setCookie(COOKIE_PLAYING_KEY, state, 10); // 10 phut
                // console.log('[Tutorial] Saved playing state:', state);
            } catch (e) {
                console.error('[Tutorial] Error saving state:', e);
            }
        }

        getPlayingState() {
            let state = null;

            // Thu lay tu sessionStorage truoc
            try {
                const sessionData = sessionStorage.getItem(PLAYING_KEY);
                if (sessionData) {
                    state = JSON.parse(sessionData);
                }
            } catch (e) {}

            // Neu khong co, thu lay tu cookie backup
            if (!state) {
                try {
                    const cookieData = this.getCookie(COOKIE_PLAYING_KEY);
                    if (cookieData) {
                        state = JSON.parse(cookieData);

                        // Kiem tra timestamp - chi dung neu trong vong 10 phut
                        if (state.timestamp && (Date.now() - state.timestamp) > 10 * 60 * 1000) {
                            this.deleteCookie(COOKIE_PLAYING_KEY);
                            return null;
                        }
                    }
                } catch (e) {}
            }

            return state;
        }

        clearPlayingState() {
            try {
                sessionStorage.removeItem(PLAYING_KEY);
                this.deleteCookie(COOKIE_PLAYING_KEY);
            } catch (e) {}
        }

        showPlayUI() {
            document.getElementById('playTutorialName').textContent = this.currentTutorial.name;
            document.getElementById('playTotalSteps').textContent = this.currentTutorial.steps.length;

            // QUAN TRONG: Bat che do CHAN TUONG TAC
            document.body.classList.add('tutorial-playing-mode');
            document.getElementById('tutorialOverlay')?.classList.add('active');
            document.getElementById('tutorialPlayPopup')?.classList.add('active');
        }

        showStep(index) {
            const steps = this.currentTutorial.steps;
            if (index < 0 || index >= steps.length) return;

            const step = steps[index];
            const currentUrl = window.location.pathname + window.location.search;

            // Check if step is on different page
            if (step.pageUrl && !this.urlsMatch(step.pageUrl, currentUrl)) {
                // Luu history truoc khi chuyen trang
                this.historyStack.push({
                    stepIndex: this.currentStepIndex,
                    pageUrl: currentUrl
                });

                this.savePlayingState(this.currentTutorial.id, index);
                window.location.href = this.normalizeUrl(step.pageUrl);
                return;
            }

            this.currentStepIndex = index;
            this.stepCompleted = false;

            const actionType = step.actionType || 'highlight';

            // Action type info
            const actionTypeInfo = {
                'highlight': { icon: 'fa-eye', text: 'Xem xong nhấn "Tiếp" để tiếp tục', waitForAction: false },
                'click': { icon: 'fa-hand-pointer-o', text: 'CLICK vào vùng highlight để thực hiện', waitForAction: true },
                'navigate': { icon: 'fa-external-link', text: 'CLICK vào link để chuyển trang', waitForAction: true }
            };

            const info = actionTypeInfo[actionType] || actionTypeInfo['highlight'];

            // Update UI
            document.getElementById('playCurrentStep').textContent = index + 1;
            document.getElementById('playStepContent').innerHTML = `
                <div class="step-description">${this.escapeHtml(step.description)}</div>
                <div class="step-action-hint">
                    <i class="fa ${info.icon}"></i> ${info.text}
                </div>
            `;

            // Update buttons
            const prevBtn = document.getElementById('btnPrevStep');
            const nextBtn = document.getElementById('btnNextStep');

            // Nut LUI LAI - chi bat khi co the lui
            prevBtn.disabled = index === 0 && this.historyStack.length === 0;

            // Nut TIEP - an neu can doi action
            if (info.waitForAction) {
                nextBtn.style.display = 'none';
                nextBtn.disabled = true;
            } else {
                nextBtn.style.display = '';
                nextBtn.disabled = false;
            }

            if (index === steps.length - 1 && !info.waitForAction) {
                nextBtn.innerHTML = '<i class="fa fa-check"></i> Hoàn tất';
            } else {
                nextBtn.innerHTML = 'Tiếp <i class="fa fa-chevron-right"></i>';
            }

            // Tim va highlight element
            this.findAndHighlightElement(step.selector, 0, actionType);
        }

        findAndHighlightElement(selector, retryCount = 0, actionType = 'highlight') {
            const MAX_RETRIES = 5;
            const RETRY_DELAY = 300;

            let element = document.querySelector(selector);

            if (element) {
                this.prepareAndHighlight(element, selector, actionType);
                return;
            }

            if (retryCount < MAX_RETRIES) {
                setTimeout(() => {
                    this.findAndHighlightElement(selector, retryCount + 1, actionType);
                }, RETRY_DELAY);
                return;
            }

            // Fallback: try to expand menus
            this.tryExpandMenus(selector, actionType);
        }

        tryExpandMenus(selector, actionType = 'highlight') {
            const menuSelectors = [
                '#side-menu > li',
                '.sidebar-nav > li',
                '.nav-sidebar > li',
                '.metisMenu > li',
                '[data-toggle="collapse"]'
            ];

            for (const menuSel of menuSelectors) {
                const menuItems = document.querySelectorAll(menuSel);
                for (const item of menuItems) {
                    const link = item.querySelector('a') || item;
                    if (link && !item.classList.contains('active')) {
                        link.click();
                    }
                }
            }

            setTimeout(() => {
                const element = document.querySelector(selector);
                if (element) {
                    this.prepareAndHighlight(element, selector, actionType);
                } else {
                    this.hideHighlight();
                    this.hideClickableArea();
                    this.showToast('Không tìm thấy element: ' + selector.substring(0, 50), 'error');
                }
            }, 500);
        }

        prepareAndHighlight(element, selector, actionType = 'highlight') {
            // Xoa click handler cu
            if (this.currentClickHandler) {
                document.removeEventListener('click', this.currentClickHandler, true);
                this.currentClickHandler = null;
            }

            const doHighlight = () => {
                this.highlightElement(element, 'playing');
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Neu la click/navigate, tao clickable area
                if (actionType === 'click' || actionType === 'navigate') {
                    this.showClickableArea(element, actionType);
                } else {
                    this.hideClickableArea();
                }

                // Tinh toan vi tri popup SAU KHI scroll xong
                setTimeout(() => {
                    this.positionPopup(element);
                }, 400);
            };

            // Kiem tra neu can click de mo menu
            const isMenuToggle = element.matches('a[data-toggle], li.has-submenu > a, .nav-item > a, #side-menu > li > a');
            const hasSubmenu = element.closest('li')?.querySelector('ul.nav-second-level, ul.sub-menu, .collapse');

            if (isMenuToggle && hasSubmenu) {
                element.click();
                setTimeout(doHighlight, 300);
            } else {
                doHighlight();
            }
        }

        showClickableArea(element, actionType) {
            const clickableArea = document.getElementById('tutorialClickableArea');
            if (!clickableArea || !element) return;

            const rect = element.getBoundingClientRect();

            clickableArea.style.top = rect.top + 'px';
            clickableArea.style.left = rect.left + 'px';
            clickableArea.style.width = rect.width + 'px';
            clickableArea.style.height = rect.height + 'px';
            clickableArea.style.display = 'block';
            clickableArea.dataset.actionType = actionType;
            clickableArea.dataset.selector = this.currentTutorial.steps[this.currentStepIndex].selector;
        }

        hideClickableArea() {
            const clickableArea = document.getElementById('tutorialClickableArea');
            if (clickableArea) {
                clickableArea.style.display = 'none';
            }
        }

        positionPopup(highlightedElement) {
            const popup = document.getElementById('tutorialPlayPopup');
            if (!popup || !highlightedElement) return;

            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const popupRect = popup.getBoundingClientRect();
            const popupWidth = popupRect.width || 400;
            const popupHeight = popupRect.height || 200;
            const elemRect = highlightedElement.getBoundingClientRect();

            // === CONSTANTS ===
            // Vùng highlight mở rộng: highlightElement() thêm padding 4px, border 3px, box-shadow 8px
            const TOTAL_EXPAND = 15;  // 4 + 3 + 8 = 15px mỗi bên
            const GAP = 12;           // Khoảng cách giữa highlight và popup
            const EDGE_MARGIN = 10;   // Khoảng cách tối thiểu từ popup đến biên viewport

            // === TÍNH VÙNG HIGHLIGHT THỰC TẾ (bao gồm hiệu ứng) ===
            const hl = {
                top: elemRect.top - TOTAL_EXPAND,
                bottom: elemRect.bottom + TOTAL_EXPAND,
                left: elemRect.left - TOTAL_EXPAND,
                right: elemRect.right + TOTAL_EXPAND,
                centerX: elemRect.left + elemRect.width / 2,
                centerY: elemRect.top + elemRect.height / 2
            };

            // === TÍNH KHÔNG GIAN KHẢ DỤNG MỖI HƯỚNG ===
            const available = {
                top: hl.top - EDGE_MARGIN,                    // Từ biên trên đến highlight
                bottom: viewportHeight - hl.bottom - EDGE_MARGIN, // Từ highlight đến biên dưới
                left: hl.left - EDGE_MARGIN,                  // Từ biên trái đến highlight
                right: viewportWidth - hl.right - EDGE_MARGIN // Từ highlight đến biên phải
            };

            // === HÀM KIỂM TRA POPUP CÓ CHỒNG LẤN HIGHLIGHT KHÔNG ===
            const isOverlapping = (pTop, pLeft) => {
                const pRight = pLeft + popupWidth;
                const pBottom = pTop + popupHeight;
                // Không chồng lấn nếu popup hoàn toàn nằm ngoài highlight
                return !(pRight <= hl.left || pLeft >= hl.right || pBottom <= hl.top || pTop >= hl.bottom);
            };

            // === HÀM TÍNH VỊ TRÍ CHO TỪNG HƯỚNG ===
            const positions = {
                // Popup ở DƯỚI highlight
                bottom: () => {
                    if (available.bottom < popupHeight + GAP) return null;
                    const top = hl.bottom + GAP;
                    let left = hl.centerX - popupWidth / 2;
                    // Điều chỉnh nếu tràn biên
                    left = Math.max(EDGE_MARGIN, Math.min(left, viewportWidth - popupWidth - EDGE_MARGIN));
                    return { top, left, score: available.bottom };
                },
                // Popup ở TRÊN highlight
                top: () => {
                    if (available.top < popupHeight + GAP) return null;
                    const top = hl.top - popupHeight - GAP;
                    let left = hl.centerX - popupWidth / 2;
                    left = Math.max(EDGE_MARGIN, Math.min(left, viewportWidth - popupWidth - EDGE_MARGIN));
                    return { top, left, score: available.top };
                },
                // Popup ở BÊN PHẢI highlight
                right: () => {
                    if (available.right < popupWidth + GAP) return null;
                    const left = hl.right + GAP;
                    let top = hl.centerY - popupHeight / 2;
                    top = Math.max(EDGE_MARGIN, Math.min(top, viewportHeight - popupHeight - EDGE_MARGIN));
                    // Kiểm tra không chồng lấn sau điều chỉnh
                    if (isOverlapping(top, left)) return null;
                    return { top, left, score: available.right };
                },
                // Popup ở BÊN TRÁI highlight
                left: () => {
                    if (available.left < popupWidth + GAP) return null;
                    const left = hl.left - popupWidth - GAP;
                    let top = hl.centerY - popupHeight / 2;
                    top = Math.max(EDGE_MARGIN, Math.min(top, viewportHeight - popupHeight - EDGE_MARGIN));
                    if (isOverlapping(top, left)) return null;
                    return { top, left, score: available.left };
                }
            };

            // === THỬ CÁC VỊ TRÍ THEO ƯU TIÊN ===
            // Ưu tiên: bottom > top > right > left (UX tốt nhất là dưới hoặc trên)
            const priority = ['bottom', 'top', 'right', 'left'];
            let bestPos = null;
            let bestName = 'fallback';

            for (const dir of priority) {
                const pos = positions[dir]();
                if (pos && !isOverlapping(pos.top, pos.left)) {
                    bestPos = pos;
                    bestName = dir;
                    break;
                }
            }

            // === FALLBACK: ĐẶT Ở GÓC ĐỐI DIỆN ===
            if (!bestPos) {
                // Chọn góc xa nhất so với highlight
                const corners = [
                    { top: EDGE_MARGIN, left: EDGE_MARGIN, name: 'top-left' },
                    { top: EDGE_MARGIN, left: viewportWidth - popupWidth - EDGE_MARGIN, name: 'top-right' },
                    { top: viewportHeight - popupHeight - EDGE_MARGIN, left: EDGE_MARGIN, name: 'bottom-left' },
                    { top: viewportHeight - popupHeight - EDGE_MARGIN, left: viewportWidth - popupWidth - EDGE_MARGIN, name: 'bottom-right' }
                ];

                // Tính khoảng cách từ tâm popup đến tâm highlight
                let maxDist = -1;
                for (const corner of corners) {
                    const cornerCenterX = corner.left + popupWidth / 2;
                    const cornerCenterY = corner.top + popupHeight / 2;
                    const dist = Math.sqrt(
                        Math.pow(cornerCenterX - hl.centerX, 2) +
                        Math.pow(cornerCenterY - hl.centerY, 2)
                    );
                    // Ưu tiên góc không chồng lấn VÀ xa nhất
                    if (!isOverlapping(corner.top, corner.left) && dist > maxDist) {
                        maxDist = dist;
                        bestPos = corner;
                        bestName = corner.name;
                    }
                }

                // Nếu tất cả góc đều chồng lấn (highlight quá lớn), chọn góc xa nhất
                if (!bestPos) {
                    maxDist = -1;
                    for (const corner of corners) {
                        const cornerCenterX = corner.left + popupWidth / 2;
                        const cornerCenterY = corner.top + popupHeight / 2;
                        const dist = Math.sqrt(
                            Math.pow(cornerCenterX - hl.centerX, 2) +
                            Math.pow(cornerCenterY - hl.centerY, 2)
                        );
                        if (dist > maxDist) {
                            maxDist = dist;
                            bestPos = corner;
                            bestName = corner.name;
                        }
                    }
                }
            }

            // === ÁP DỤNG VỊ TRÍ ===
            popup.style.position = 'fixed';
            popup.style.top = bestPos.top + 'px';
            popup.style.left = bestPos.left + 'px';
            popup.style.right = 'auto';
            popup.style.bottom = 'auto';
            popup.style.transform = 'none';
            popup.dataset.position = bestName;
        }

        handleClickableAreaClick(e) {
            if (!this.isPlaying) return;

            const clickableArea = e.currentTarget;
            const actionType = clickableArea.dataset.actionType;
            const selector = clickableArea.dataset.selector;

            const element = document.querySelector(selector);
            if (!element) {
                this.showToast('Không tìm thấy element!', 'error');
                return;
            }

            this.hideClickableArea();

            if (actionType === 'navigate') {
                const nextIndex = this.currentStepIndex + 1;
                if (nextIndex < this.currentTutorial.steps.length) {
                    this.historyStack.push({
                        stepIndex: this.currentStepIndex,
                        pageUrl: window.location.pathname + window.location.search
                    });
                    this.savePlayingState(this.currentTutorial.id, nextIndex);
                }
                element.click();
            } else if (actionType === 'click') {
                element.click();

                setTimeout(() => {
                    this.stepCompleted = true;

                    if (this.currentStepIndex < this.currentTutorial.steps.length - 1) {
                        const nextBtn = document.getElementById('btnNextStep');
                        nextBtn.style.display = '';
                        nextBtn.disabled = false;
                        nextBtn.innerHTML = 'Tiếp <i class="fa fa-chevron-right"></i>';

                        setTimeout(() => {
                            if (this.isPlaying && this.stepCompleted) {
                                this.nextStep();
                            }
                        }, 1500);
                    } else {
                        this.stopPlaying();
                        this.showToast('Hoàn tất hướng dẫn!', 'success');
                    }
                }, 500);
            }
        }

        prevStep() {
            if (this.currentStepIndex > 0) {
                this.showStep(this.currentStepIndex - 1);
            } else if (this.historyStack.length > 0) {
                const prevState = this.historyStack.pop();
                this.savePlayingState(this.currentTutorial.id, prevState.stepIndex);
                window.location.href = this.normalizeUrl(prevState.pageUrl);
            }
        }

        nextStep() {
            if (this.currentStepIndex < this.currentTutorial.steps.length - 1) {
                this.historyStack.push({
                    stepIndex: this.currentStepIndex,
                    pageUrl: window.location.pathname + window.location.search
                });
                this.showStep(this.currentStepIndex + 1);
            } else {
                this.stopPlaying();
                this.showToast('Hoàn tất hướng dẫn!', 'success');
            }
        }

        stopPlaying() {
            this.isPlaying = false;
            this.currentTutorial = null;
            this.currentStepIndex = 0;
            this.stepCompleted = false;
            this.historyStack = [];

            if (this.currentClickHandler) {
                document.removeEventListener('click', this.currentClickHandler, true);
                this.currentClickHandler = null;
            }

            document.body.classList.remove('tutorial-playing-mode');
            document.getElementById('tutorialOverlay')?.classList.remove('active');
            document.getElementById('tutorialPlayPopup')?.classList.remove('active');
            this.hideHighlight();
            this.hideClickableArea();

            this.clearPlayingState();
        }

        // ========================================
        // EXPORT/IMPORT
        // ========================================
        exportTutorials() {
            if (this.tutorials.length === 0) {
                this.showToast('Chưa có hướng dẫn nào để export!', 'error');
                return;
            }

            const data = JSON.stringify(this.tutorials, null, 2);
            const blob = new Blob([data], { type: 'application/json' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = 'admin-tutorials-' + new Date().toISOString().slice(0, 10) + '.json';
            a.click();

            URL.revokeObjectURL(url);
            this.showToast('Đã export ' + this.tutorials.length + ' hướng dẫn!', 'success');
        }

        async handleImport(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async (event) => {
                try {
                    const imported = JSON.parse(event.target.result);

                    if (!Array.isArray(imported)) {
                        throw new Error('Invalid format');
                    }

                    let count = 0;
                    for (const tutorial of imported) {
                        if (tutorial.name && Array.isArray(tutorial.steps)) {
                            tutorial.id = 'tutorial_' + Date.now() + '_' + count;
                            this.tutorials.push(tutorial);
                            await this.saveTutorialToDB(tutorial);
                            count++;
                        }
                    }

                    this.showToast(`Đã import ${count} hướng dẫn!`, 'success');

                } catch (err) {
                    this.showToast('File không hợp lệ!', 'error');
                }
            };
            reader.readAsText(file);
            e.target.value = '';
        }

        // ========================================
        // UTILITIES
        // ========================================
        normalizeUrl(url) {
            if (!url) return window.location.href;
            if (url.startsWith('http')) return url;
            return window.location.origin + (url.startsWith('/') ? '' : '/') + url;
        }

        urlsMatch(url1, url2) {
            if (!url1 || !url2) return false;
            if (url1 === url2) return true;

            const getParams = (url) => {
                const match = url.match(/\?(.+)$/);
                if (!match) return {};
                const params = {};
                match[1].split('&').forEach(pair => {
                    const [key, value] = pair.split('=');
                    if (key) params[key] = value || '';
                });
                return params;
            };

            const getBasePath = (url) => {
                let path = url.split('?')[0];
                path = path.replace(/^https?:\/\/[^\/]+/, '');
                path = path.replace(/\/index\.php$/, '/').replace(/\/$/, '');
                return path;
            };

            const base1 = getBasePath(url1);
            const base2 = getBasePath(url2);
            const params1 = getParams(url1);
            const params2 = getParams(url2);

            const basesMatch = base1 === base2 || base1.endsWith(base2) || base2.endsWith(base1);

            const hasOl1 = 'ol' in params1;
            const hasOl2 = 'ol' in params2;

            if (hasOl1 && hasOl2) {
                return params1.ol === params2.ol && basesMatch;
            }

            if (hasOl1 !== hasOl2) {
                return false;
            }

            return basesMatch;
        }

        showToast(message, type = 'success') {
            const toast = document.getElementById('tutorialToast');
            if (!toast) return;

            toast.textContent = message;
            toast.className = 'tutorial-toast ' + type + ' active';

            setTimeout(() => {
                toast.classList.remove('active');
            }, 3000);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        formatDate(dateStr) {
            try {
                const date = new Date(dateStr);
                return date.toLocaleDateString('vi-VN');
            } catch (e) {
                return dateStr;
            }
        }

        // ========================================
        // PUBLIC API
        // ========================================
        openTutorialList() {
            this.renderTutorialsList();
            this.openModal('tutorialListModal');
            // Clear search khi mở modal
            this.clearSearch();
        }

        openCreateModal() {
            this.openModal('tutorialCreateModal');
            document.getElementById('tutorialName')?.focus();
        }

        triggerImport() {
            document.getElementById('importInput')?.click();
        }

        // ========================================
        // SEARCH FUNCTIONALITY
        // ========================================
        filterTutorials(keyword) {
            const list = document.getElementById('tutorialList');
            if (!list) return;

            const items = list.querySelectorAll('.tutorial-list-item');
            const searchTerm = keyword.toLowerCase().trim();
            let visibleCount = 0;

            items.forEach(item => {
                const nameEl = item.querySelector('.tutorial-list-name');
                if (!nameEl) return;

                const originalName = nameEl.getAttribute('data-original-name') || nameEl.textContent;
                // Lưu tên gốc nếu chưa có
                if (!nameEl.getAttribute('data-original-name')) {
                    nameEl.setAttribute('data-original-name', originalName);
                }

                const name = originalName.toLowerCase();

                if (searchTerm === '' || name.includes(searchTerm)) {
                    item.classList.remove('hidden');
                    visibleCount++;

                    // Highlight matched text
                    if (searchTerm !== '') {
                        const regex = new RegExp(`(${this.escapeRegex(searchTerm)})`, 'gi');
                        nameEl.innerHTML = originalName.replace(regex, '<mark>$1</mark>');
                    } else {
                        nameEl.textContent = originalName;
                    }
                } else {
                    item.classList.add('hidden');
                }
            });

            // Hiển thị thông báo không tìm thấy
            let noResults = list.querySelector('.tutorial-no-results');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.className = 'tutorial-no-results';
                    noResults.innerHTML = `
                        <i class="fa fa-search"></i>
                        <p>Không tìm thấy hướng dẫn nào với từ khóa "<strong>${this.escapeHtml(keyword)}</strong>"</p>
                    `;
                    list.appendChild(noResults);
                } else {
                    noResults.querySelector('strong').textContent = keyword;
                    noResults.style.display = 'block';
                }
            } else if (noResults) {
                noResults.style.display = 'none';
            }
        }

        clearSearch() {
            const input = document.getElementById('tutorialSearchInput');
            if (input) {
                input.value = '';
                this.filterTutorials('');
                input.focus();
            }
        }

        escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        /**
         * Handle import từ header (file input bên ngoài)
         */
        handleImportFromHeader(input) {
            if (!input || !input.files || !input.files[0]) return;

            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = async (event) => {
                try {
                    const imported = JSON.parse(event.target.result);

                    if (!Array.isArray(imported)) {
                        throw new Error('Invalid format');
                    }

                    let count = 0;
                    for (const tutorial of imported) {
                        if (tutorial.name && Array.isArray(tutorial.steps)) {
                            tutorial.id = 'tutorial_' + Date.now() + '_' + count;
                            this.tutorials.push(tutorial);
                            await this.saveTutorialToDB(tutorial);
                            count++;
                        }
                    }

                    this.showToast(`Đã import ${count} hướng dẫn!`, 'success');

                } catch (err) {
                    this.showToast('File không hợp lệ!', 'error');
                }
            };

            reader.readAsText(file);
            input.value = '';
        }
    }

    // ========================================
    // INITIALIZE
    // ========================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.tutorialBuilder = new TutorialBuilder();
        });
    } else {
        window.tutorialBuilder = new TutorialBuilder();
    }

})();
