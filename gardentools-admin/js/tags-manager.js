/**
 * Tags Manager JavaScript
 * Handles tags CRUD and article tag selection
 */

// Detect admin path from current location
const TAGS_ADMIN_PATH = (function() {
    const path = window.location.pathname;
    // Match path up to and including dxmt-admin/
    const match = path.match(/^(.*\/dxmt-admin\/)/);
    if (match) return match[1];
    // Fallback: match first two segments for subdomain-like paths
    const match2 = path.match(/^(\/[^\/]+\/[^\/]+\/)/);
    return match2 ? match2[1] : '/dxmt-admin/';
})();

const TagsManager = {
    apiUrl: TAGS_ADMIN_PATH + 'ajax_related_articles.php',
    currentPage: 1,
    totalPages: 1,

    /**
     * Initialize
     */
    init: function() {
        this.loadTags();
        this.bindEvents();
    },

    /**
     * Bind events
     */
    bindEvents: function() {
        // Search on enter
        $('#searchTag').on('keypress', function(e) {
            if (e.which === 13) {
                TagsManager.loadTags();
            }
        });

        // Form submit
        $('#tagForm').on('submit', function(e) {
            e.preventDefault();
            TagsManager.saveTag();
        });
    },

    /**
     * Load tags list
     */
    loadTags: function(page) {
        this.currentPage = page || 1;

        const tagType = $('#filterTagType').val();
        const search = $('#searchTag').val();

        $.ajax({
            url: this.apiUrl,
            type: 'GET',
            data: {
                action: 'list_tags',
                tag_type: tagType,
                search: search,
                page: this.currentPage,
                limit: 50
            },
            beforeSend: function() {
                $('#tagsTableBody').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Đang tải...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    TagsManager.renderTags(response.data.tags);
                    TagsManager.renderPagination(response.data.pagination);
                } else {
                    $('#tagsTableBody').html('<tr><td colspan="6" class="text-center text-danger">' + response.message + '</td></tr>');
                }
            },
            error: function() {
                $('#tagsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Lỗi kết nối server</td></tr>');
            }
        });
    },

    /**
     * Render tags table
     */
    renderTags: function(tags) {
        if (!tags || tags.length === 0) {
            $('#tagsTableBody').html('<tr><td colspan="6" class="text-center text-muted">Không có tag nào</td></tr>');
            $('#tagCount').text('');
            return;
        }

        let html = '';
        tags.forEach(function(tag) {
            const usageClass = tag.usage_count > 10 ? 'popular' : '';
            html += `
                <tr data-id="${tag.id}">
                    <td>${tag.id}</td>
                    <td class="tag-name">${TagsManager.escapeHtml(tag.name)}</td>
                    <td class="tag-slug">${TagsManager.escapeHtml(tag.slug)}</td>
                    <td><span class="tag-type-badge tag-type-${tag.tag_type}">${TagsManager.getTypeName(tag.tag_type)}</span></td>
                    <td><span class="usage-count ${usageClass}">${tag.usage_count}</span></td>
                    <td>
                        <button class="btn btn-xs btn-info btn-action" onclick="TagsManager.showEditModal(${tag.id})" title="Sửa">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-xs btn-danger btn-action" onclick="TagsManager.deleteTag(${tag.id})" title="Xóa">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        $('#tagsTableBody').html(html);
        $('#tagCount').text(tags.length + ' tags');
    },

    /**
     * Render pagination
     */
    renderPagination: function(pagination) {
        if (pagination.total_pages <= 1) {
            $('#tagsPagination').html('');
            return;
        }

        let html = '<ul class="pagination">';

        // Previous
        if (pagination.page > 1) {
            html += `<li><a href="javascript:void(0)" onclick="TagsManager.loadTags(${pagination.page - 1})">&laquo;</a></li>`;
        } else {
            html += '<li class="disabled"><span>&laquo;</span></li>';
        }

        // Page numbers
        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.total_pages, pagination.page + 2);

        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.page) {
                html += `<li class="active"><span>${i}</span></li>`;
            } else {
                html += `<li><a href="javascript:void(0)" onclick="TagsManager.loadTags(${i})">${i}</a></li>`;
            }
        }

        // Next
        if (pagination.page < pagination.total_pages) {
            html += `<li><a href="javascript:void(0)" onclick="TagsManager.loadTags(${pagination.page + 1})">&raquo;</a></li>`;
        } else {
            html += '<li class="disabled"><span>&raquo;</span></li>';
        }

        html += '</ul>';
        $('#tagsPagination').html(html);
    },

    /**
     * Show add modal
     */
    showAddModal: function() {
        $('#tagId').val('');
        $('#tagName').val('');
        $('#tagType').val('general');
        $('#tagModalTitle').text('Thêm Tag');
        $('#tagModal').modal('show');
        setTimeout(function() {
            $('#tagName').focus();
        }, 500);
    },

    /**
     * Show edit modal
     */
    showEditModal: function(id) {
        const row = $(`#tagsTableBody tr[data-id="${id}"]`);
        const name = row.find('.tag-name').text();
        const type = row.find('.tag-type-badge').attr('class').replace('tag-type-badge tag-type-', '');

        $('#tagId').val(id);
        $('#tagName').val(name);
        $('#tagType').val(type);
        $('#tagModalTitle').text('Sửa Tag');
        $('#tagModal').modal('show');
        setTimeout(function() {
            $('#tagName').focus();
        }, 500);
    },

    /**
     * Save tag (add or edit)
     */
    saveTag: function() {
        const id = $('#tagId').val();
        const name = $('#tagName').val().trim();
        const tagType = $('#tagType').val();

        if (!name) {
            alert('Vui lòng nhập tên tag');
            $('#tagName').focus();
            return;
        }

        const action = id ? 'edit_tag' : 'add_tag';

        $.ajax({
            url: this.apiUrl,
            type: 'POST',
            data: {
                action: action,
                id: id,
                name: name,
                tag_type: tagType
            },
            beforeSend: function() {
                $('#tagModal .btn-primary').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang lưu...');
            },
            success: function(response) {
                if (response.success) {
                    $('#tagModal').modal('hide');
                    TagsManager.loadTags(TagsManager.currentPage);
                    // Show success notification if available
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Lỗi kết nối server');
            },
            complete: function() {
                $('#tagModal .btn-primary').prop('disabled', false).html('<i class="fa fa-save"></i> Lưu');
            }
        });
    },

    /**
     * Delete tag
     */
    deleteTag: function(id) {
        if (!confirm('Bạn có chắc muốn xóa tag này?')) {
            return;
        }

        $.ajax({
            url: this.apiUrl,
            type: 'POST',
            data: {
                action: 'delete_tag',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    TagsManager.loadTags(TagsManager.currentPage);
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert('Lỗi kết nối server');
            }
        });
    },

    /**
     * Import tags from keywords
     */
    importFromKeywords: function() {
        $('#btnStartImport').prop('disabled', true);
        $('#importProgress').show();

        $.ajax({
            url: this.apiUrl,
            type: 'POST',
            data: {
                action: 'import_from_keywords'
            },
            success: function(response) {
                if (response.success) {
                    $('#importProgress .progress-bar').css('width', '100%').text('100%');
                    $('#importStatus').text('Hoàn thành! ' + response.message);
                    TagsManager.loadTags();
                } else {
                    $('#importStatus').html('<span class="text-danger">' + response.message + '</span>');
                }
            },
            error: function() {
                $('#importStatus').html('<span class="text-danger">Lỗi kết nối server</span>');
            },
            complete: function() {
                $('#btnStartImport').prop('disabled', false);
            }
        });
    },

    /**
     * Get tag type display name
     */
    getTypeName: function(type) {
        const types = {
            'location': 'Vị trí',
            'property_type': 'Loại BĐS',
            'feature': 'Tiện ích',
            'topic': 'Chủ đề',
            'general': 'Chung'
        };
        return types[type] || type;
    },

    /**
     * Escape HTML
     */
    escapeHtml: function(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
};

/**
 * Article Tag Selector - For use in article edit forms
 */
const ArticleTagSelector = {
    apiUrl: TAGS_ADMIN_PATH + 'ajax_related_articles.php',
    selectedTags: [],
    containerEl: null,
    inputEl: null,
    suggestionsEl: null,
    enableApiSearch: false,  // Disable API search by default (use local tags only)
    apiAvailable: false,     // Will be set to true if API is available

    /**
     * Initialize tag selector for an article
     * @param {string} containerId - Container element ID
     * @param {int} articleId - Article ID (0 for new articles)
     * @param {string|object} savedTagsOrOptions - Either saved tags string "tag1,tag2,tag3" or options object
     */
    init: function(containerId, articleId, savedTagsOrOptions) {
        this.containerEl = document.getElementById(containerId);
        if (!this.containerEl) return;

        this.articleId = articleId || 0;
        this.selectedTags = [];

        // Parse third parameter - can be string (savedTags) or object (options)
        var savedTags = '';
        if (typeof savedTagsOrOptions === 'string') {
            savedTags = savedTagsOrOptions;
            this.enableApiSearch = false;
        } else if (typeof savedTagsOrOptions === 'object' && savedTagsOrOptions !== null) {
            this.enableApiSearch = savedTagsOrOptions.enableApiSearch || false;
            savedTags = savedTagsOrOptions.savedTags || '';
        } else {
            this.enableApiSearch = false;
        }

        this.render();
        this.bindEvents();

        // Load saved tags from database (passed from PHP)
        if (savedTags && savedTags.length > 0) {
            this.loadSavedTags(savedTags);
        }

        // Check if API is available (tags tables exist) - optional, for autocomplete
        this.checkApiAvailability();
    },

    /**
     * Load saved tags from comma-separated string (from dxmt_article.article_tags)
     * @param {string} tagsString - "tag1,tag2,tag3"
     */
    loadSavedTags: function(tagsString) {
        var self = this;
        if (!tagsString || tagsString.trim() === '') return;

        var tagNames = tagsString.split(',');
        tagNames.forEach(function(name) {
            name = name.trim();
            if (name.length > 0) {
                // Add as local tag (no ID, just name)
                self.addLocalTag(name);
            }
        });
    },

    /**
     * Check if API is available
     */
    checkApiAvailability: function() {
        const self = this;
        $.ajax({
            url: this.apiUrl,
            type: 'GET',
            data: { action: 'search_tags', q: 'test', limit: 1 },
            success: function(response) {
                if (response.success) {
                    self.apiAvailable = true;
                    // Enable API search if it was requested and API is available
                    if (self.enableApiSearch === 'auto') {
                        self.enableApiSearch = true;
                    }
                }
            },
            error: function() {
                self.apiAvailable = false;
                self.enableApiSearch = false;
            }
        });
    },

    /**
     * Render the tag selector HTML
     */
    render: function() {
        this.containerEl.innerHTML = `
            <div class="tag-input-container" id="tagInputContainer">
                <span class="selected-tags"></span>
                <input type="text" id="tagSearchInput" placeholder="Nhập để tìm tag...">
            </div>
            <div class="tag-suggestions" id="tagSuggestions"></div>
            <input type="hidden" name="article_tags" id="articleTagsHidden">
        `;

        this.inputEl = document.getElementById('tagSearchInput');
        this.suggestionsEl = document.getElementById('tagSuggestions');
    },

    /**
     * Bind events
     */
    bindEvents: function() {
        const self = this;

        // Focus on container click
        document.getElementById('tagInputContainer').addEventListener('click', function() {
            self.inputEl.focus();
        });

        // Search on input (only if API mode enabled)
        let searchTimeout;
        this.inputEl.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length >= 1 && self.enableApiSearch) {
                searchTimeout = setTimeout(function() {
                    self.searchTags(query);
                }, 300);
            } else {
                self.hideSuggestions();
            }
        });

        // Keyboard navigation
        this.inputEl.addEventListener('keydown', function(e) {
            // Tab, Enter, or Comma - create tag locally (like bootstrap-tagsinput)
            if (e.key === 'Tab' || e.key === 'Enter' || e.key === ',' || e.keyCode === 188) {
                const query = this.value.trim().replace(/,+$/, ''); // Remove trailing commas

                // Only process if there's input text
                if (query.length > 0) {
                    e.preventDefault();

                    // Check if there's an active suggestion from API
                    const active = self.suggestionsEl.querySelector('.suggestion-item.active');
                    if (active) {
                        active.click();
                    } else {
                        // Check if there are any suggestions shown
                        const first = self.suggestionsEl.querySelector('.suggestion-item');
                        if (first) {
                            // Select first suggestion
                            first.click();
                        } else {
                            // No API suggestions - create tag locally (offline mode like tagsinput)
                            self.addLocalTag(query);
                        }
                    }
                } else if (e.key === 'Tab') {
                    // Allow normal tab navigation if input is empty
                    return;
                }
            } else if (e.key === 'Backspace' && this.value === '') {
                // Remove last tag
                if (self.selectedTags.length > 0) {
                    self.removeTag(self.selectedTags[self.selectedTags.length - 1].id);
                }
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                self.navigateSuggestions(e.key === 'ArrowDown' ? 1 : -1);
            } else if (e.key === 'Escape') {
                self.hideSuggestions();
            }
        });

        // Hide on click outside
        document.addEventListener('click', function(e) {
            if (!self.containerEl.contains(e.target)) {
                self.hideSuggestions();
            }
        });
    },

    /**
     * Load article's existing tags
     */
    loadArticleTags: function(articleId) {
        const self = this;

        $.ajax({
            url: this.apiUrl,
            type: 'GET',
            data: {
                action: 'get_article_tags',
                article_id: articleId
            },
            success: function(response) {
                if (response.success && response.data && Array.isArray(response.data)) {
                    response.data.forEach(function(tag) {
                        self.addTag(tag, false);
                    });
                }
            },
            error: function() {
                // API not available, tags will be added locally
                console.log('[Tags] API not available, using local mode only');
            }
        });
    },

    /**
     * Search tags
     */
    searchTags: function(query) {
        const self = this;

        $.ajax({
            url: this.apiUrl,
            type: 'GET',
            data: {
                action: 'search_tags',
                q: query,
                limit: 10
            },
            success: function(response) {
                if (response.success) {
                    self.showSuggestions(response.data, query);
                }
            }
        });
    },

    /**
     * Show suggestions dropdown
     */
    showSuggestions: function(tags, query) {
        const self = this;
        let html = '';

        // Filter out already selected tags
        const availableTags = tags.filter(function(tag) {
            return !self.selectedTags.find(function(t) { return t.id == tag.id; });
        });

        if (availableTags.length > 0) {
            availableTags.forEach(function(tag) {
                html += `
                    <div class="suggestion-item" data-id="${tag.id}" data-name="${self.escapeHtml(tag.name)}" data-type="${tag.tag_type}">
                        ${self.escapeHtml(tag.name)}
                        <span class="tag-type-badge tag-type-${tag.tag_type}">${TagsManager.getTypeName(tag.tag_type)}</span>
                    </div>
                `;
            });
        } else {
            html += '<div class="no-results">Không tìm thấy tag phù hợp</div>';
        }

        // Option to create new tag
        html += `
            <div class="create-new" data-name="${this.escapeHtml(query)}">
                <i class="fa fa-plus"></i> Tạo tag mới: "${this.escapeHtml(query)}"
            </div>
        `;

        this.suggestionsEl.innerHTML = html;
        this.suggestionsEl.classList.add('show');

        // Bind click events
        this.suggestionsEl.querySelectorAll('.suggestion-item').forEach(function(el) {
            el.addEventListener('click', function() {
                self.addTag({
                    id: this.dataset.id,
                    name: this.dataset.name,
                    tag_type: this.dataset.type
                });
            });
        });

        this.suggestionsEl.querySelector('.create-new').addEventListener('click', function() {
            self.createAndAddTag(this.dataset.name);
        });
    },

    /**
     * Hide suggestions
     */
    hideSuggestions: function() {
        this.suggestionsEl.classList.remove('show');
    },

    /**
     * Navigate suggestions with keyboard
     */
    navigateSuggestions: function(direction) {
        const items = this.suggestionsEl.querySelectorAll('.suggestion-item, .create-new');
        if (items.length === 0) return;

        const current = this.suggestionsEl.querySelector('.active');
        let index = current ? Array.from(items).indexOf(current) : -1;

        if (current) current.classList.remove('active');

        index += direction;
        if (index < 0) index = items.length - 1;
        if (index >= items.length) index = 0;

        items[index].classList.add('active');
    },

    /**
     * Add a tag to selection (from API suggestion)
     */
    addTag: function(tag, clearInput = true) {
        // Check if already selected by id
        if (this.selectedTags.find(function(t) { return t.id == tag.id; })) {
            return;
        }

        this.selectedTags.push(tag);
        this.renderSelectedTags();
        this.updateHiddenInput();

        if (clearInput) {
            this.inputEl.value = '';
            this.hideSuggestions();
            this.inputEl.focus();
        }
    },

    /**
     * Add a local tag (offline, like tagsinput) - no API call
     * Used when user types and presses Tab/Enter/Comma without selecting a suggestion
     */
    addLocalTag: function(name) {
        name = name.trim();
        if (!name) return;

        // Remove commas from tag name (prevent split issues when loading)
        name = name.replace(/,/g, '');
        if (!name) return;

        // Check if already selected by name (case insensitive)
        const nameLower = name.toLowerCase();
        if (this.selectedTags.find(function(t) { return t.name.toLowerCase() === nameLower; })) {
            this.inputEl.value = '';
            this.hideSuggestions();
            return;
        }

        // Generate a temporary local ID (negative to distinguish from server IDs)
        const localId = 'local_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

        this.selectedTags.push({
            id: localId,
            name: name,
            tag_type: 'general',
            is_local: true  // Flag to indicate this is a local tag
        });

        this.renderSelectedTags();
        this.updateHiddenInput();

        this.inputEl.value = '';
        this.hideSuggestions();
        this.inputEl.focus();
    },

    /**
     * Remove a tag from selection
     */
    removeTag: function(tagId) {
        this.selectedTags = this.selectedTags.filter(function(t) { return t.id != tagId; });
        this.renderSelectedTags();
        this.updateHiddenInput();
    },

    /**
     * Create a new tag via API and add it
     */
    createAndAddTag: function(name) {
        const self = this;

        $.ajax({
            url: this.apiUrl,
            type: 'POST',
            data: {
                action: 'add_tag',
                name: name,
                tag_type: 'general'
            },
            success: function(response) {
                if (response.success) {
                    self.addTag(response.data);
                } else if (response.existing_id) {
                    // Tag already exists, add it
                    self.addTag({
                        id: response.existing_id,
                        name: name,
                        tag_type: 'general'
                    });
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                // API failed, add as local tag instead
                self.addLocalTag(name);
            }
        });
    },

    /**
     * Render selected tags
     */
    renderSelectedTags: function() {
        const self = this;
        const container = this.containerEl.querySelector('.selected-tags');

        let html = '';
        this.selectedTags.forEach(function(tag, index) {
            const tagId = tag.id;
            const isLocal = tag.is_local || String(tagId).startsWith('local_');
            html += `
                <span class="tag-item${isLocal ? ' tag-local' : ''}" data-id="${tagId}" data-index="${index}">
                    ${self.escapeHtml(tag.name)}
                    <span class="tag-remove" onclick="ArticleTagSelector.removeTagByIndex(${index})">&times;</span>
                </span>
            `;
        });

        container.innerHTML = html;
    },

    /**
     * Remove tag by index (safer for local tags with complex IDs)
     */
    removeTagByIndex: function(index) {
        if (index >= 0 && index < this.selectedTags.length) {
            this.selectedTags.splice(index, 1);
            this.renderSelectedTags();
            this.updateHiddenInput();
        }
    },

    /**
     * Update hidden input with tag data
     * Format: JSON array with both IDs (for existing tags) and names (for new tags)
     */
    updateHiddenInput: function() {
        // For saving: include both existing tag IDs and new tag names
        const data = this.selectedTags.map(function(t) {
            if (t.is_local || String(t.id).startsWith('local_')) {
                // Local tag - send name for server to create
                return { name: t.name, is_new: true };
            } else {
                // Existing tag - send ID
                return { id: t.id, name: t.name };
            }
        });
        document.getElementById('articleTagsHidden').value = JSON.stringify(data);
    },

    /**
     * Get selected tag IDs
     */
    getSelectedTagIds: function() {
        return this.selectedTags.map(function(t) { return t.id; });
    },

    /**
     * Escape HTML
     */
    escapeHtml: function(str) {
        if (!str) return '';
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#039;');
    }
};
