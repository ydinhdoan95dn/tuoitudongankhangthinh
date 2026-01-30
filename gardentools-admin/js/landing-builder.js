/**
 * Landing Page Builder - JavaScript
 * Used in landing_add.php and landing_edit.php
 */

window.LandingBuilder = {
    blocks: [],
    blockCounter: 0,
    projectDataCache: {
        images: null,
        apartments: null,
        projectId: null
    },

    blockDefs: {
        hero: { name: 'Hero Banner', icon: 'fa-image' },
        content: { name: 'Nội dung', icon: 'fa-align-left' },
        gallery: { name: 'Gallery ảnh', icon: 'fa-picture-o' },
        apartments: { name: 'Danh sách căn hộ', icon: 'fa-building' },
        features: { name: 'Tiện ích nổi bật', icon: 'fa-star' },
        video: { name: 'Video', icon: 'fa-video-camera' },
        location: { name: 'Vị trí & Bản đồ', icon: 'fa-map-marker' },
        cta: { name: 'Call to Action', icon: 'fa-bullhorn' },
        contact_form: { name: 'Form liên hệ', icon: 'fa-envelope' }
    },

    // Debounce helper
    debounce: function(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    // Load project data (images, apartments)
    loadProjectData: async function(forceReload = false) {
        let projectId = document.getElementById('projectSelect').value;
        if (!projectId) return null;

        // Use cache if same project
        if (!forceReload && this.projectDataCache.projectId === projectId && this.projectDataCache.images) {
            return this.projectDataCache;
        }

        this.projectDataCache.projectId = projectId;

        try {
            let formData = new FormData();
            formData.append('url', 'api_landing');
            formData.append('act', 'get_images');
            formData.append('project_id', projectId);

            let imgRes = await fetch('action.php', { method: 'POST', body: formData });
            let imgData = await imgRes.json();
            this.projectDataCache.images = imgData.success ? imgData.images : [];
            this.projectDataCache.projectName = imgData.project_name || '';

            let aptFormData = new FormData();
            aptFormData.append('url', 'api_landing');
            aptFormData.append('act', 'get_apartments');
            aptFormData.append('project_id', projectId);

            let aptRes = await fetch('action.php', { method: 'POST', body: aptFormData });
            let aptData = await aptRes.json();
            this.projectDataCache.apartments = aptData.success ? aptData.apartments : [];

        } catch (e) {
            console.error('Error loading project data:', e);
        }

        return this.projectDataCache;
    },

    // Generate preview HTML for each block type
    generatePreview: function(type, config) {
        let projectName = document.getElementById('projectSelect').selectedOptions[0]?.text || 'Dự án';
        let cache = this.projectDataCache;

        switch (type) {
            case 'hero':
                let heroTitle = config.title || projectName;
                let heroSubtitle = config.subtitle || 'Mô tả dự án sẽ hiển thị tại đây';
                let heroHtml = `<div class="preview-hero">
                    <h4>${heroTitle}</h4>
                    <p>${heroSubtitle}</p>
                    ${config.show_cta ? '<button class="btn btn-sm btn-warning mt-2">' + (config.cta_text || 'Nhận báo giá') + '</button>' : ''}
                </div>`;
                if (cache.images && cache.images.length > 0) {
                    heroHtml += `<div class="mt-2"><small class="text-success"><i class="fa fa-check"></i> ${cache.images.length} ảnh sẽ hiển thị làm background/slider</small></div>`;
                }
                return heroHtml;

            case 'gallery':
                if (!cache.images || cache.images.length === 0) {
                    return `<div class="preview-no-data"><i class="fa fa-exclamation-triangle"></i> Chưa có ảnh - Vui lòng chọn dự án có upload ảnh</div>`;
                }
                let maxImg = parseInt(config.max_items) || 9;
                let imgs = cache.images.slice(0, Math.min(6, maxImg));
                return `<div class="preview-images">
                    ${imgs.map(img => `<img src="${img.url}" alt="" onerror="this.style.display='none'">`).join('')}
                    ${cache.images.length > 6 ? `<span class="badge bg-secondary align-self-center">+${cache.images.length - 6} ảnh</span>` : ''}
                </div>
                <small class="text-muted d-block mt-1">Tổng: ${cache.images.length} ảnh - Hiển thị tối đa: ${maxImg}</small>`;

            case 'apartments':
                if (!cache.apartments || cache.apartments.length === 0) {
                    return `<div class="preview-no-data"><i class="fa fa-exclamation-triangle"></i> Chưa có căn hộ - Dự án cần có menu con và bài viết (hot=1)</div>`;
                }
                let maxApt = parseInt(config.max_items) || 6;
                let apts = cache.apartments.slice(0, Math.min(4, maxApt));
                return `<div class="preview-apartments">
                    ${apts.map(apt => `<div class="preview-apt-card">
                        ${apt.img ? `<img src="${apt.img}" alt="" onerror="this.style.display='none'">` : '<div style="height:50px;background:#eee;border-radius:4px;margin-bottom:5px"></div>'}
                        <div class="apt-name">${apt.name}</div>
                        <div class="apt-info">${config.show_area && apt.area ? apt.area + ' | ' : ''}${config.show_price && apt.price ? apt.price : ''}</div>
                    </div>`).join('')}
                </div>
                <small class="text-muted d-block mt-1">Tổng: ${cache.apartments.length} căn - Hiển thị tối đa: ${maxApt}</small>`;

            case 'content':
                return `<div class="preview-content">
                    <strong>${config.title || 'Giới thiệu dự án'}</strong>
                    <p class="mb-0 text-muted">${config.content ? config.content.substring(0, 100) + '...' : 'Nội dung sẽ được lấy từ bài viết của dự án'}</p>
                </div>`;

            case 'features':
                let features = [];
                try {
                    features = typeof config.features === 'string' ? JSON.parse(config.features || '[]') : (config.features || []);
                } catch(e) {}
                if (features.length === 0) {
                    features = [
                        { icon: 'fa fa-swimming-pool', title: 'Hồ bơi' },
                        { icon: 'fa fa-dumbbell', title: 'Gym' },
                        { icon: 'fa fa-tree', title: 'Công viên' }
                    ];
                }
                return `<div class="preview-features">
                    ${features.slice(0, 3).map(f => `<div class="preview-feature-item">
                        <i class="${f.icon || 'fa fa-star'}"></i>
                        <span>${f.title || 'Tiện ích'}</span>
                    </div>`).join('')}
                </div>`;

            case 'video':
                return `<div class="preview-video">
                    <i class="fa fa-play-circle fa-2x mb-2"></i>
                    <div>${config.video_url ? 'Video: ' + config.video_url.substring(0, 30) + '...' : 'Chưa có URL video'}</div>
                </div>`;

            case 'location':
                return `<div class="preview-location">
                    <i class="fa fa-map-marker fa-2x mb-2 text-danger"></i>
                    <div><strong>${config.title || 'Vị trí dự án'}</strong></div>
                    <small>${config.address || 'Địa chỉ sẽ được hiển thị tại đây'}</small>
                    ${config.map_embed ? '<div class="text-success mt-1"><small><i class="fa fa-check"></i> Đã có Google Maps</small></div>' : '<div class="text-warning mt-1"><small><i class="fa fa-warning"></i> Chưa có Google Maps embed</small></div>'}
                </div>`;

            case 'cta':
                return `<div class="preview-cta">
                    <strong>${config.title || 'Đăng ký ngay'}</strong>
                    <p class="mb-2" style="font-size:0.8rem">${config.subtitle || 'Nhận ưu đãi đặc biệt'}</p>
                    <button class="btn btn-sm btn-light">${config.button_text || 'Nhận báo giá'}</button>
                </div>`;

            case 'contact_form':
                return `<div class="preview-form">
                    <strong class="d-block mb-2">${config.title || 'Đăng ký nhận thông tin'}</strong>
                    <input type="text" placeholder="Họ và tên *" disabled>
                    ${config.show_phone !== false ? '<input type="text" placeholder="Số điện thoại *" disabled>' : ''}
                    ${config.show_email !== false ? '<input type="text" placeholder="Email" disabled>' : ''}
                    <button class="btn btn-sm btn-primary w-100">${config.button_text || 'Gửi yêu cầu'}</button>
                </div>`;

            default:
                return `<div class="text-muted text-center p-2"><i class="fa fa-cube"></i> ${type}</div>`;
        }
    },

    // Get config from form
    getBlockConfigFromForm: function(blockEl, type) {
        let config = {};
        blockEl.querySelectorAll('[data-config]').forEach(input => {
            let key = input.dataset.config;
            config[key] = input.type === 'checkbox' ? input.checked : input.value;
        });
        return config;
    },

    // Get default config
    getDefaultConfig: function(type) {
        let projectName = document.getElementById('projectSelect').selectedOptions[0]?.text || '';

        let defaults = {
            hero: {
                layout: 'fullscreen',
                title: projectName || 'Tên dự án',
                subtitle: 'Mô tả ngắn về dự án',
                show_cta: true,
                cta_text: 'Nhận báo giá',
                cta_action: 'scroll_to_form'
            },
            content: { title: 'Giới thiệu dự án', content: '' },
            gallery: { title: 'Hình ảnh dự án', layout: 'grid_3', max_items: 9 },
            apartments: { title: 'Căn hộ nổi bật', layout: 'grid_3', max_items: 6, show_price: true, show_area: true },
            features: { title: 'Tiện ích nổi bật', layout: 'grid_3', features: [] },
            video: { title: 'Video dự án', video_url: '', video_type: 'youtube' },
            location: { title: 'Vị trí dự án', address: '', map_embed: '' },
            cta: { title: 'Đăng ký ngay hôm nay', subtitle: 'Nhận ưu đãi đặc biệt', button_text: 'Nhận báo giá', button_action: 'scroll_to_form' },
            contact_form: { title: 'Đăng ký nhận thông tin', subtitle: 'Để lại thông tin, chúng tôi sẽ liên hệ trong 24h', button_text: 'Gửi yêu cầu', show_phone: true, show_email: true, show_message: true }
        };
        return defaults[type] || {};
    },

    // Add block
    addBlock: async function(type, existingConfig = null) {
        let self = this;

        // Check if project selected
        let projectId = document.getElementById('projectSelect').value;
        if (!projectId) {
            alert('Vui lòng chọn dự án trước khi thêm block!');
            return;
        }

        this.blockCounter++;
        let id = type + '_' + this.blockCounter;
        let def = this.blockDefs[type] || { name: type, icon: 'fa-cube' };
        let configTemplate = document.getElementById('config_' + type);
        let configHtml = configTemplate ? configTemplate.innerHTML : '<p class="text-muted">Không có cấu hình</p>';

        let html = `
            <div class="active-block expanded" data-id="${id}" data-type="${type}">
                <div class="active-block-header">
                    <div>
                        <i class="fa fa-bars handle me-2"></i>
                        <i class="fa ${def.icon} me-2"></i>
                        <strong>${def.name}</strong>
                    </div>
                    <div class="active-block-actions">
                        <button type="button" class="btn btn-sm btn-outline-info btn-refresh" title="Làm mới preview">
                            <i class="fa fa-refresh"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="active-block-body">
                    ${configHtml}
                    <div class="block-preview" data-preview-for="${id}">
                        <div class="block-preview-loading">
                            <i class="fa fa-spinner fa-spin"></i> Đang tải dữ liệu...
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('emptyBlocksMessage').style.display = 'none';
        document.getElementById('activeBlocks').insertAdjacentHTML('beforeend', html);

        let newBlock = document.querySelector(`[data-id="${id}"]`);
        let defaultConfig = existingConfig || this.getDefaultConfig(type);

        // Apply config values
        Object.keys(defaultConfig).forEach(key => {
            let input = newBlock.querySelector(`[data-config="${key}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = defaultConfig[key];
                } else if (typeof defaultConfig[key] === 'object') {
                    input.value = JSON.stringify(defaultConfig[key]);
                } else {
                    input.value = defaultConfig[key];
                }
            }
        });

        // Load data and generate preview
        await this.loadProjectData();
        let previewEl = newBlock.querySelector('.block-preview');
        previewEl.innerHTML = this.generatePreview(type, defaultConfig);

        // Bind config change events
        newBlock.querySelectorAll('[data-config]').forEach(input => {
            input.addEventListener('change', function() {
                let config = self.getBlockConfigFromForm(newBlock, type);
                previewEl.innerHTML = self.generatePreview(type, config);
            });
            input.addEventListener('input', self.debounce(function() {
                let config = self.getBlockConfigFromForm(newBlock, type);
                previewEl.innerHTML = self.generatePreview(type, config);
            }, 300));
        });

        // Refresh button
        newBlock.querySelector('.btn-refresh').addEventListener('click', async function() {
            previewEl.innerHTML = '<div class="block-preview-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';
            await self.loadProjectData(true);
            let config = self.getBlockConfigFromForm(newBlock, type);
            previewEl.innerHTML = self.generatePreview(type, config);
        });

        // Toggle button
        newBlock.querySelector('.btn-toggle').addEventListener('click', function() {
            newBlock.classList.toggle('expanded');
            let icon = this.querySelector('i');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });

        // Remove button
        newBlock.querySelector('.btn-remove').addEventListener('click', function() {
            if (confirm('Xóa block này?')) {
                newBlock.remove();
                self.updateBlocksOrder();
                self.checkEmptyBlocks();
            }
        });

        this.blocks.push({
            id: id,
            type: type,
            config: defaultConfig
        });
        this.updateConfigInput();
    },

    updateBlocksOrder: function() {
        let self = this;
        let newOrder = [];
        document.querySelectorAll('#activeBlocks .active-block').forEach(el => {
            let id = el.dataset.id;
            let block = self.blocks.find(b => b.id === id);
            if (block) {
                block.config = self.getBlockConfigFromForm(el, block.type);
                newOrder.push(block);
            }
        });
        this.blocks = newOrder;
        this.updateConfigInput();
    },

    checkEmptyBlocks: function() {
        let hasBlocks = document.querySelectorAll('#activeBlocks .active-block').length > 0;
        document.getElementById('emptyBlocksMessage').style.display = hasBlocks ? 'none' : 'block';
    },

    updateConfigInput: function() {
        let config = {
            meta: {
                title: document.querySelector('[name="meta_title"]')?.value || '',
                description: document.querySelector('[name="meta_description"]')?.value || ''
            },
            style: {
                primary_color: document.getElementById('primaryColor')?.value || '#0066b3',
                accent_color: document.getElementById('accentColor')?.value || '#f5821f'
            },
            blocks: this.blocks.map(b => ({ id: b.id, type: b.type, config: b.config }))
        };
        document.getElementById('configInput').value = JSON.stringify(config);
    },

    // Refresh all block previews
    refreshAllPreviews: async function() {
        let self = this;

        // Show loading
        document.querySelectorAll('.block-preview').forEach(el => {
            el.innerHTML = '<div class="block-preview-loading"><i class="fa fa-spinner fa-spin"></i> Đang tải dữ liệu dự án...</div>';
        });

        await this.loadProjectData(true);

        document.querySelectorAll('#activeBlocks .active-block').forEach(blockEl => {
            let type = blockEl.dataset.type;
            let config = self.getBlockConfigFromForm(blockEl, type);
            let previewEl = blockEl.querySelector('.block-preview');
            if (previewEl) {
                previewEl.innerHTML = self.generatePreview(type, config);
            }
        });
    },

    // Initialize
    init: function() {
        let self = this;

        // Auto generate slug from name
        let nameInput = document.getElementById('lpName');
        if (nameInput) {
            nameInput.addEventListener('input', function() {
                let slug = this.value
                    .toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[đĐ]/g, 'd')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                document.getElementById('lpSlug').value = slug;
            });
        }

        // Color pickers sync
        let primaryColor = document.getElementById('primaryColor');
        let primaryColorText = document.getElementById('primaryColorText');
        let accentColor = document.getElementById('accentColor');
        let accentColorText = document.getElementById('accentColorText');

        if (primaryColor && primaryColorText) {
            primaryColor.addEventListener('input', () => primaryColorText.value = primaryColor.value);
            primaryColorText.addEventListener('input', () => primaryColor.value = primaryColorText.value);
        }
        if (accentColor && accentColorText) {
            accentColor.addEventListener('input', () => accentColorText.value = accentColor.value);
            accentColorText.addEventListener('input', () => accentColor.value = accentColorText.value);
        }

        // Project change
        let projectSelect = document.getElementById('projectSelect');
        if (projectSelect) {
            projectSelect.addEventListener('change', () => self.refreshAllPreviews());
        }

        // Sortable
        new Sortable(document.getElementById('availableBlocks'), {
            group: { name: 'blocks', pull: 'clone', put: false },
            sort: false,
            animation: 150
        });

        new Sortable(document.getElementById('activeBlocks'), {
            group: 'blocks',
            animation: 150,
            handle: '.handle',
            onAdd: function(evt) {
                let type = evt.item.dataset.type;
                if (type) {
                    evt.item.remove();
                    self.addBlock(type);
                }
            },
            onSort: () => self.updateBlocksOrder()
        });

        // Expose addBlock globally
        window.addBlock = (type, config) => self.addBlock(type, config);
    }
};
