/**
 * TUTORIAL TEST UTILITY
 * Cong cu test va debug cho admin-tutorial.js
 *
 * CACH SU DUNG:
 * 1. Mo browser console (F12)
 * 2. Goi cac lenh test tu TutorialTest object
 *
 * VD: TutorialTest.checkState()
 *     TutorialTest.createTestTutorial()
 *     TutorialTest.runFullTest()
 */

window.TutorialTest = {

    // ========================================
    // 1. KIEM TRA TRANG THAI HIEN TAI
    // ========================================

    /**
     * Kiem tra toan bo state cua tutorial
     */
    checkState: function() {
        console.log('========== TUTORIAL STATE CHECK ==========');

        // Check localStorage tutorials
        const tutorials = localStorage.getItem('admin_tutorials');
        console.log('\n[1] LocalStorage - admin_tutorials:');
        if (tutorials) {
            const parsed = JSON.parse(tutorials);
            console.log('   So luong tutorials:', Object.keys(parsed).length);
            Object.keys(parsed).forEach(key => {
                console.log('   -', key, ':', parsed[key].steps.length, 'buoc');
            });
        } else {
            console.log('   (Khong co tutorial nao)');
        }

        // Check sessionStorage playing state
        const playingState = sessionStorage.getItem('tutorial_playing');
        console.log('\n[2] SessionStorage - tutorial_playing:');
        if (playingState) {
            const state = JSON.parse(playingState);
            console.log('   Tutorial ID:', state.tutorialId);
            console.log('   Step Index:', state.stepIndex);
            console.log('   URL:', state.url);
            console.log('   Timestamp:', new Date(state.timestamp).toLocaleString());
        } else {
            console.log('   (Khong co)');
        }

        // Check cookie backup
        const cookieState = this.getCookie('tutorial_playing_cookie');
        console.log('\n[3] Cookie - tutorial_playing_cookie:');
        if (cookieState) {
            try {
                const state = JSON.parse(decodeURIComponent(cookieState));
                console.log('   Tutorial ID:', state.tutorialId);
                console.log('   Step Index:', state.stepIndex);
            } catch(e) {
                console.log('   Raw value:', cookieState);
            }
        } else {
            console.log('   (Khong co)');
        }

        // Check TutorialBuilder instance
        console.log('\n[4] TutorialBuilder Instance:');
        if (window.tutorialBuilder) {
            console.log('   isPlaying:', window.tutorialBuilder.isPlaying);
            console.log('   isRecording:', window.tutorialBuilder.isRecording);
            console.log('   currentStepIndex:', window.tutorialBuilder.currentStepIndex);
            console.log('   currentTutorial:', window.tutorialBuilder.currentTutorial?.name || '(none)');
        } else {
            console.log('   (Chua khoi tao)');
        }

        // Check DOM elements
        console.log('\n[5] DOM Elements:');
        console.log('   Clickable Area:', !!document.getElementById('tutorialClickableArea'));
        console.log('   Play Popup:', !!document.getElementById('tutorialPlayPopup'));
        console.log('   Record Panel:', !!document.getElementById('tutorialRecordPanel'));

        console.log('\n==========================================');
        return {
            tutorials: tutorials ? JSON.parse(tutorials) : null,
            playingState: playingState ? JSON.parse(playingState) : null,
            cookieState: cookieState,
            instance: window.tutorialBuilder
        };
    },

    /**
     * Lay cookie theo ten
     */
    getCookie: function(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    },

    // ========================================
    // 2. TAO TEST TUTORIAL
    // ========================================

    /**
     * Tao 1 tutorial test don gian (1 buoc highlight)
     */
    createSimpleTest: function() {
        const testTutorial = {
            id: 'test_simple_' + Date.now(),
            name: 'Test Simple - Highlight Only',
            description: 'Test highlight 1 element',
            steps: [
                {
                    selector: '.tutorial-btn', // Button tutorial
                    description: 'Day la nut Tutorial. Click vao day de bat dau.',
                    url: window.location.href,
                    actionType: 'highlight'
                }
            ],
            createdAt: new Date().toISOString()
        };

        this.saveTutorial(testTutorial);
        console.log('[TEST] Da tao tutorial:', testTutorial.name);
        console.log('[TEST] ID:', testTutorial.id);
        return testTutorial;
    },

    /**
     * Tao tutorial test voi click action
     */
    createClickTest: function() {
        const testTutorial = {
            id: 'test_click_' + Date.now(),
            name: 'Test Click Action',
            description: 'Test click vao element',
            steps: [
                {
                    selector: '.tutorial-btn',
                    description: 'Buoc 1: Click vao nut Tutorial nay',
                    url: window.location.href,
                    actionType: 'click'
                },
                {
                    selector: '.tutorial-dropdown-item[data-action="list"]',
                    description: 'Buoc 2: Dropdown da mo, click vao Danh sach',
                    url: window.location.href,
                    actionType: 'highlight'
                }
            ],
            createdAt: new Date().toISOString()
        };

        this.saveTutorial(testTutorial);
        console.log('[TEST] Da tao tutorial:', testTutorial.name);
        return testTutorial;
    },

    /**
     * Tao tutorial test cross-page (quan trong nhat)
     */
    createCrossPageTest: function(targetUrl) {
        if (!targetUrl) {
            console.error('[TEST] Can truyen targetUrl. VD: TutorialTest.createCrossPageTest("home.php")');
            return null;
        }

        const currentUrl = window.location.href;
        const baseUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/') + 1);
        const fullTargetUrl = targetUrl.startsWith('http') ? targetUrl : baseUrl + targetUrl;

        const testTutorial = {
            id: 'test_crosspage_' + Date.now(),
            name: 'Test Cross-Page Navigation',
            description: 'Test chuyen trang va tiep tuc tutorial',
            steps: [
                {
                    selector: '.tutorial-btn',
                    description: 'Buoc 1: (Trang hien tai) Highlight nut Tutorial',
                    url: currentUrl,
                    actionType: 'highlight'
                },
                {
                    selector: 'a[href*="' + targetUrl.split('/').pop() + '"]',
                    description: 'Buoc 2: Click vao link de chuyen trang',
                    url: currentUrl,
                    actionType: 'navigate'
                },
                {
                    selector: '.tutorial-btn',
                    description: 'Buoc 3: (Trang moi) Da chuyen sang trang moi. Highlight nut Tutorial',
                    url: fullTargetUrl,
                    actionType: 'highlight'
                }
            ],
            createdAt: new Date().toISOString()
        };

        this.saveTutorial(testTutorial);
        console.log('[TEST] Da tao tutorial cross-page');
        console.log('[TEST] Tu:', currentUrl);
        console.log('[TEST] Den:', fullTargetUrl);
        return testTutorial;
    },

    /**
     * Luu tutorial vao localStorage
     */
    saveTutorial: function(tutorial) {
        let tutorials = localStorage.getItem('admin_tutorials');
        tutorials = tutorials ? JSON.parse(tutorials) : {};
        tutorials[tutorial.id] = tutorial;
        localStorage.setItem('admin_tutorials', JSON.stringify(tutorials));
    },

    // ========================================
    // 3. TEST FUNCTIONS
    // ========================================

    /**
     * Test URL matching
     */
    testUrlMatch: function(url1, url2) {
        if (!url1 || !url2) {
            console.log('Usage: TutorialTest.testUrlMatch("url1", "url2")');
            return;
        }

        // Replicate urlsMatch logic
        const normalize = (url) => {
            try {
                const u = new URL(url, window.location.origin);
                return u.pathname.replace(/\/+$/, '').toLowerCase();
            } catch(e) {
                return url.replace(/\/+$/, '').toLowerCase();
            }
        };

        const n1 = normalize(url1);
        const n2 = normalize(url2);
        const match = n1 === n2;

        console.log('[URL Match Test]');
        console.log('   URL 1:', url1);
        console.log('   URL 2:', url2);
        console.log('   Normalized 1:', n1);
        console.log('   Normalized 2:', n2);
        console.log('   Match:', match ? 'YES' : 'NO');

        return match;
    },

    /**
     * Test tim element theo selector
     */
    testFindElement: function(selector) {
        console.log('[Find Element Test]');
        console.log('   Selector:', selector);

        const element = document.querySelector(selector);
        if (element) {
            console.log('   Found:', element);
            console.log('   Tag:', element.tagName);
            console.log('   Classes:', element.className);
            console.log('   Visible:', element.offsetParent !== null);
            console.log('   Position:', element.getBoundingClientRect());

            // Highlight tam thoi
            const oldBorder = element.style.border;
            element.style.border = '3px solid red';
            setTimeout(() => { element.style.border = oldBorder; }, 2000);
            console.log('   (Element duoc highlight 2 giay)');
        } else {
            console.log('   NOT FOUND!');

            // Try to find similar
            const parts = selector.split(/[.#\[\]]/);
            const mainPart = parts.filter(p => p.length > 2)[0];
            if (mainPart) {
                const similar = document.querySelectorAll('[class*="' + mainPart + '"], [id*="' + mainPart + '"]');
                if (similar.length > 0) {
                    console.log('   Similar elements found:', similar.length);
                    similar.forEach((el, i) => {
                        console.log('     ' + i + ':', el.tagName, el.className || el.id);
                    });
                }
            }
        }

        return element;
    },

    /**
     * Test play tutorial by ID
     */
    testPlay: function(tutorialId) {
        if (!tutorialId) {
            // List available tutorials
            const tutorials = JSON.parse(localStorage.getItem('admin_tutorials') || '{}');
            console.log('Available tutorials:');
            Object.keys(tutorials).forEach(id => {
                console.log('  -', id, ':', tutorials[id].name);
            });
            console.log('\nUsage: TutorialTest.testPlay("tutorial_id")');
            return;
        }

        if (window.tutorialBuilder) {
            console.log('[TEST] Starting tutorial:', tutorialId);
            window.tutorialBuilder.startPlay(tutorialId);
        } else {
            console.error('[TEST] TutorialBuilder not initialized!');
        }
    },

    // ========================================
    // 4. DEBUG HELPERS
    // ========================================

    /**
     * Clear all tutorial data
     */
    clearAll: function() {
        localStorage.removeItem('admin_tutorials');
        sessionStorage.removeItem('tutorial_playing');
        document.cookie = 'tutorial_playing_cookie=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        console.log('[TEST] Da xoa tat ca du lieu tutorial');
    },

    /**
     * Clear only playing state
     */
    clearPlayingState: function() {
        sessionStorage.removeItem('tutorial_playing');
        document.cookie = 'tutorial_playing_cookie=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        console.log('[TEST] Da xoa playing state');
    },

    /**
     * Force continue tutorial (skip URL check)
     */
    forceContinue: function() {
        const state = sessionStorage.getItem('tutorial_playing');
        if (!state) {
            console.error('[TEST] Khong co playing state');
            return;
        }

        const parsed = JSON.parse(state);
        console.log('[TEST] Force continuing tutorial:', parsed.tutorialId, 'at step:', parsed.stepIndex);

        if (window.tutorialBuilder) {
            window.tutorialBuilder.continueTutorial(parsed.tutorialId, parsed.stepIndex);
        }
    },

    /**
     * Bat/tat verbose logging
     */
    enableVerbose: function(enable = true) {
        window.TUTORIAL_VERBOSE = enable;
        console.log('[TEST] Verbose logging:', enable ? 'ON' : 'OFF');
        console.log('[TEST] Refresh page de ap dung');
    },

    /**
     * In huong dan su dung
     */
    help: function() {
        console.log(`
========== TUTORIAL TEST UTILITY ==========

KIEM TRA TRANG THAI:
  TutorialTest.checkState()     - Xem toan bo state

TAO TUTORIAL TEST:
  TutorialTest.createSimpleTest()              - Tao test 1 buoc highlight
  TutorialTest.createClickTest()               - Tao test click action
  TutorialTest.createCrossPageTest("url")      - Tao test chuyen trang

TEST:
  TutorialTest.testUrlMatch(url1, url2)  - Test URL matching
  TutorialTest.testFindElement(selector) - Test tim element
  TutorialTest.testPlay(id)              - Chay tutorial (list neu khong co id)

DEBUG:
  TutorialTest.clearAll()          - Xoa tat ca data
  TutorialTest.clearPlayingState() - Xoa chi playing state
  TutorialTest.forceContinue()     - Force tiep tuc tutorial
  TutorialTest.enableVerbose()     - Bat verbose logging

VD SU DUNG:
  1. TutorialTest.createSimpleTest()
  2. TutorialTest.testPlay()  // Xem list
  3. TutorialTest.testPlay("test_simple_xxx")
  4. TutorialTest.checkState()

===========================================
        `);
    }
};

// Auto help on load
console.log('[Tutorial Test] Loaded! Goi TutorialTest.help() de xem huong dan');
