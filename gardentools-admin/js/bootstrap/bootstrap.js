if (typeof jQuery === 'undefined') { throw new Error('Bootstrap\'s JavaScript requires jQuery') }

+function ($) {
	'use strict';
	
	function transitionEnd() {
		var el = document.createElement('bootstrap')

		var transEndEventNames = {
			WebkitTransition : 'webkitTransitionEnd',
			MozTransition    : 'transitionend',
			OTransition      : 'oTransitionEnd otransitionend',
			transition       : 'transitionend'
		}

		for (var name in transEndEventNames) {
			if (el.style[name] !== undefined) {
				return { end: transEndEventNames[name] }
			}
		}

		return false // explicit for ie8 (  ._.)
	}

	$.fn.emulateTransitionEnd = function (duration) {
		var called = false
		var $el = this
		$(this).one('bsTransitionEnd', function () { called = true })
		var callback = function () { if (!called) $($el).trigger($.support.transition.end) }
		setTimeout(callback, duration)
		return this
	}

	$(function () {
		$.support.transition = transitionEnd()

		if (!$.support.transition) return

		$.event.special.bsTransitionEnd = {
			bindType: $.support.transition.end,
			delegateType: $.support.transition.end,
			handle: function (e) {
				if ($(e.target).is(this)) return e.handleObj.handler.apply(this, arguments)
			}
		}
	})

}(jQuery);

+function ($) {
	'use strict';

	// ALERT CLASS DEFINITION
	// ======================

	var dismiss = '[data-dismiss="alert"]'
	var Alert   = function (el) {
		$(el).on('click', dismiss, this.close)
	}

	Alert.VERSION = '3.2.0'

	Alert.prototype.close = function (e) {
		var $this    = $(this)
		var selector = $this.attr('data-target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
		}

		var $parent = $(selector)

		if (e) e.preventDefault()

		if (!$parent.length) {
			$parent = $this.hasClass('alert') ? $this : $this.parent()
		}

		$parent.trigger(e = $.Event('close.bs.alert'))

		if (e.isDefaultPrevented()) return

		$parent.removeClass('in')

		function removeElement() {
			// detach from parent, fire event then clean up data
			$parent.detach().trigger('closed.bs.alert').remove()
		}

		$.support.transition && $parent.hasClass('fade') ?
			$parent
				.one('bsTransitionEnd', removeElement)
				.emulateTransitionEnd(150) :
			removeElement()
	}


	// ALERT PLUGIN DEFINITION
	// =======================

	function Plugin(option) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.alert')

			if (!data) $this.data('bs.alert', (data = new Alert(this)))
			if (typeof option == 'string') data[option].call($this)
		})
	}

	var old = $.fn.alert

	$.fn.alert             = Plugin
	$.fn.alert.Constructor = Alert


	// ALERT NO CONFLICT
	// =================

	$.fn.alert.noConflict = function () {
		$.fn.alert = old
		return this
	}


	// ALERT DATA-API
	// ==============

	$(document).on('click.bs.alert.data-api', dismiss, Alert.prototype.close)

}(jQuery);


+function ($) {
	'use strict';

	var Button = function (element, options) {
		this.$element  = $(element)
		this.options   = $.extend({}, Button.DEFAULTS, options)
		this.isLoading = false
	}

	Button.VERSION  = '3.2.0'

	Button.DEFAULTS = {
		loadingText: 'loading...'
	}

	Button.prototype.setState = function (state) {
		var d    = 'disabled'
		var $el  = this.$element
		var val  = $el.is('input') ? 'val' : 'html'
		var data = $el.data()

		state = state + 'Text'

		if (data.resetText == null) $el.data('resetText', $el[val]())

		$el[val](data[state] == null ? this.options[state] : data[state])

		// push to event loop to allow forms to submit
		setTimeout($.proxy(function () {
			if (state == 'loadingText') {
				this.isLoading = true
				$el.addClass(d).attr(d, d)
			} else if (this.isLoading) {
				this.isLoading = false
				$el.removeClass(d).removeAttr(d)
			}
		}, this), 0)
	}

	Button.prototype.toggle = function () {
		var changed = true
		var $parent = this.$element.closest('[data-toggle="buttons"]')

		if ($parent.length) {
			var $input = this.$element.find('input')
			if ($input.prop('type') == 'radio') {
				if ($input.prop('checked') && this.$element.hasClass('active')) changed = false
				else $parent.find('.active').removeClass('active')
			}
			if (changed) $input.prop('checked', !this.$element.hasClass('active')).trigger('change')
		}

		if (changed) this.$element.toggleClass('active')
	}


	// BUTTON PLUGIN DEFINITION
	// ========================

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.button')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bs.button', (data = new Button(this, options)))

			if (option == 'toggle') data.toggle()
			else if (option) data.setState(option)
		})
	}

	var old = $.fn.button

	$.fn.button             = Plugin
	$.fn.button.Constructor = Button


	// BUTTON NO CONFLICT
	// ==================

	$.fn.button.noConflict = function () {
		$.fn.button = old
		return this
	}


	// BUTTON DATA-API
	// ===============

	$(document).on('click.bs.button.data-api', '[data-toggle^="button"]', function (e) {
		var $btn = $(e.target)
		if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
		Plugin.call($btn, 'toggle')
		e.preventDefault()
	})

}(jQuery);

+function ($) {
	'use strict';

	var Carousel = function (element, options) {
		this.$element    = $(element).on('keydown.bs.carousel', $.proxy(this.keydown, this))
		this.$indicators = this.$element.find('.carousel-indicators')
		this.options     = options
		this.paused      =
			this.sliding     =
				this.interval    =
					this.$active     =
						this.$items      = null

		this.options.pause == 'hover' && this.$element
			.on('mouseenter.bs.carousel', $.proxy(this.pause, this))
			.on('mouseleave.bs.carousel', $.proxy(this.cycle, this))
	}

	Carousel.VERSION  = '3.2.0'

	Carousel.DEFAULTS = {
		interval: 5000,
		pause: 'hover',
		wrap: true
	}

	Carousel.prototype.keydown = function (e) {
		switch (e.which) {
			case 37: this.prev(); break
			case 39: this.next(); break
			default: return
		}

		e.preventDefault()
	}

	Carousel.prototype.cycle = function (e) {
		e || (this.paused = false)

		this.interval && clearInterval(this.interval)

		this.options.interval
			&& !this.paused
		&& (this.interval = setInterval($.proxy(this.next, this), this.options.interval))

		return this
	}

	Carousel.prototype.getItemIndex = function (item) {
		this.$items = item.parent().children('.item')
		return this.$items.index(item || this.$active)
	}

	Carousel.prototype.to = function (pos) {
		var that        = this
		var activeIndex = this.getItemIndex(this.$active = this.$element.find('.item.active'))

		if (pos > (this.$items.length - 1) || pos < 0) return

		if (this.sliding)       return this.$element.one('slid.bs.carousel', function () { that.to(pos) }) // yes, "slid"
		if (activeIndex == pos) return this.pause().cycle()

		return this.slide(pos > activeIndex ? 'next' : 'prev', $(this.$items[pos]))
	}

	Carousel.prototype.pause = function (e) {
		e || (this.paused = true)

		if (this.$element.find('.next, .prev').length && $.support.transition) {
			this.$element.trigger($.support.transition.end)
			this.cycle(true)
		}

		this.interval = clearInterval(this.interval)

		return this
	}

	Carousel.prototype.next = function () {
		if (this.sliding) return
		return this.slide('next')
	}

	Carousel.prototype.prev = function () {
		if (this.sliding) return
		return this.slide('prev')
	}

	Carousel.prototype.slide = function (type, next) {
		var $active   = this.$element.find('.item.active')
		var $next     = next || $active[type]()
		var isCycling = this.interval
		var direction = type == 'next' ? 'left' : 'right'
		var fallback  = type == 'next' ? 'first' : 'last'
		var that      = this

		if (!$next.length) {
			if (!this.options.wrap) return
			$next = this.$element.find('.item')[fallback]()
		}

		if ($next.hasClass('active')) return (this.sliding = false)

		var relatedTarget = $next[0]
		var slideEvent = $.Event('slide.bs.carousel', {
			relatedTarget: relatedTarget,
			direction: direction
		})
		this.$element.trigger(slideEvent)
		if (slideEvent.isDefaultPrevented()) return

		this.sliding = true

		isCycling && this.pause()

		if (this.$indicators.length) {
			this.$indicators.find('.active').removeClass('active')
			var $nextIndicator = $(this.$indicators.children()[this.getItemIndex($next)])
			$nextIndicator && $nextIndicator.addClass('active')
		}

		var slidEvent = $.Event('slid.bs.carousel', { relatedTarget: relatedTarget, direction: direction }) // yes, "slid"
		if ($.support.transition && this.$element.hasClass('slide')) {
			$next.addClass(type)
			$next[0].offsetWidth // force reflow
			$active.addClass(direction)
			$next.addClass(direction)
			$active
				.one('bsTransitionEnd', function () {
					$next.removeClass([type, direction].join(' ')).addClass('active')
					$active.removeClass(['active', direction].join(' '))
					that.sliding = false
					setTimeout(function () {
						that.$element.trigger(slidEvent)
					}, 0)
				})
				.emulateTransitionEnd($active.css('transition-duration').slice(0, -1) * 1000)
		} else {
			$active.removeClass('active')
			$next.addClass('active')
			this.sliding = false
			this.$element.trigger(slidEvent)
		}

		isCycling && this.cycle()

		return this
	}


	// CAROUSEL PLUGIN DEFINITION
	// ==========================

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.carousel')
			var options = $.extend({}, Carousel.DEFAULTS, $this.data(), typeof option == 'object' && option)
			var action  = typeof option == 'string' ? option : options.slide

			if (!data) $this.data('bs.carousel', (data = new Carousel(this, options)))
			if (typeof option == 'number') data.to(option)
			else if (action) data[action]()
			else if (options.interval) data.pause().cycle()
		})
	}

	var old = $.fn.carousel

	$.fn.carousel             = Plugin
	$.fn.carousel.Constructor = Carousel


	// CAROUSEL NO CONFLICT
	// ====================

	$.fn.carousel.noConflict = function () {
		$.fn.carousel = old
		return this
	}


	// CAROUSEL DATA-API
	// =================

	$(document).on('click.bs.carousel.data-api', '[data-slide], [data-slide-to]', function (e) {
		var href
		var $this   = $(this)
		var $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) // strip for ie7
		if (!$target.hasClass('carousel')) return
		var options = $.extend({}, $target.data(), $this.data())
		var slideIndex = $this.attr('data-slide-to')
		if (slideIndex) options.interval = false

		Plugin.call($target, options)

		if (slideIndex) {
			$target.data('bs.carousel').to(slideIndex)
		}

		e.preventDefault()
	})

	$(window).on('load', function () {
		$('[data-ride="carousel"]').each(function () {
			var $carousel = $(this)
			Plugin.call($carousel, $carousel.data())
		})
	})

}(jQuery);


+function ($) {
	'use strict';

	var Collapse = function (element, options) {
		this.$element      = $(element)
		this.options       = $.extend({}, Collapse.DEFAULTS, options)
		this.transitioning = null

		if (this.options.parent) this.$parent = $(this.options.parent)
		if (this.options.toggle) this.toggle()
	}

	Collapse.VERSION  = '3.2.0'

	Collapse.DEFAULTS = {
		toggle: true
	}

	Collapse.prototype.dimension = function () {
		var hasWidth = this.$element.hasClass('width')
		return hasWidth ? 'width' : 'height'
	}

	Collapse.prototype.show = function () {
		if (this.transitioning || this.$element.hasClass('in')) return

		var startEvent = $.Event('show.bs.collapse')
		this.$element.trigger(startEvent)
		if (startEvent.isDefaultPrevented()) return

		var actives = this.$parent && this.$parent.find('> .panel > .in')


		if (actives && actives.length) {
			var hasData = actives.data('bs.collapse')
			if (hasData && hasData.transitioning) return
			Plugin.call(actives, 'hide')
			hasData || actives.data('bs.collapse', null)
		}

		var dimension = this.dimension()

		this.$element
			.removeClass('collapse')
			.addClass('collapsing')[dimension](0)

		this.transitioning = 1

		var complete = function () {
			this.$element
				.removeClass('collapsing')
				.addClass('collapse in')[dimension]('')
			this.transitioning = 0
			this.$element
				.trigger('shown.bs.collapse')
		}

		if (!$.support.transition) return complete.call(this)

		var scrollSize = $.camelCase(['scroll', dimension].join('-'))

		this.$element
			.one('bsTransitionEnd', $.proxy(complete, this))
			.emulateTransitionEnd(350)[dimension](this.$element[0][scrollSize])
	}

	Collapse.prototype.hide = function () {
		if (this.transitioning || !this.$element.hasClass('in')) return

		var startEvent = $.Event('hide.bs.collapse')
		this.$element.trigger(startEvent)
		if (startEvent.isDefaultPrevented()) return

		var dimension = this.dimension()

		this.$element[dimension](this.$element[dimension]())[0].offsetHeight

		this.$element
			.addClass('collapsing')
			.removeClass('collapse')
			.removeClass('in')

		this.transitioning = 1

		var complete = function () {
			this.transitioning = 0
			this.$element
				.trigger('hidden.bs.collapse')
				.removeClass('collapsing')
				.addClass('collapse')
		}

		if (!$.support.transition) return complete.call(this)

		this.$element
			[dimension](0)
			.one('bsTransitionEnd', $.proxy(complete, this))
			.emulateTransitionEnd(350)
	}

	Collapse.prototype.toggle = function () {
		this[this.$element.hasClass('in') ? 'hide' : 'show']()
	}


	// COLLAPSE PLUGIN DEFINITION
	// ==========================

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.collapse')
			var options = $.extend({}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option)

			if (!data && options.toggle && option == 'show') option = !option
			if (!data) $this.data('bs.collapse', (data = new Collapse(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.collapse

	$.fn.collapse             = Plugin
	$.fn.collapse.Constructor = Collapse


	// COLLAPSE NO CONFLICT
	// ====================

	$.fn.collapse.noConflict = function () {
		$.fn.collapse = old
		return this
	}


	// COLLAPSE DATA-API
	// =================

	$(document).on('click.bs.collapse.data-api', '[data-toggle="collapse"]', function (e) {
		var href
		var $this   = $(this)
		var target  = $this.attr('data-target')
			|| e.preventDefault()
			|| (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') // strip for ie7
		var $target = $(target)
		var data    = $target.data('bs.collapse')
		var option  = data ? 'toggle' : $this.data()
		var parent  = $this.attr('data-parent')
		var $parent = parent && $(parent)

		if (!data || !data.transitioning) {
			if ($parent) $parent.find('[data-toggle="collapse"][data-parent="' + parent + '"]').not($this).addClass('collapsed')
			$this[$target.hasClass('in') ? 'addClass' : 'removeClass']('collapsed')
		}

		Plugin.call($target, option)
	})

}(jQuery);

+function ($) {
	'use strict';

	// DROPDOWN CLASS DEFINITION
	// =========================

	var backdrop = '.dropdown-backdrop'
	var toggle   = '[data-toggle="dropdown"]'
	var Dropdown = function (element) {
		$(element).on('click.bs.dropdown', this.toggle)
	}

	Dropdown.VERSION = '3.2.0'

	Dropdown.prototype.toggle = function (e) {
		var $this = $(this)

		if ($this.is('.disabled, :disabled')) return

		var $parent  = getParent($this)
		var isActive = $parent.hasClass('open')

		clearMenus()

		if (!isActive) {
			if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
				// if mobile we use a backdrop because click events don't delegate
				$('<div class="dropdown-backdrop"/>').insertAfter($(this)).on('click', clearMenus)
			}

			var relatedTarget = { relatedTarget: this }
			$parent.trigger(e = $.Event('show.bs.dropdown', relatedTarget))

			if (e.isDefaultPrevented()) return

			$this.trigger('focus')

			$parent
				.toggleClass('open')
				.trigger('shown.bs.dropdown', relatedTarget)
		}
		return false
	}

	Dropdown.prototype.keydown = function (e) {
		if (!/(38|40|27)/.test(e.keyCode)) return

		var $this = $(this)

		e.preventDefault()
		e.stopPropagation()

		if ($this.is('.disabled, :disabled')) return

		var $parent  = getParent($this)
		var isActive = $parent.hasClass('open')

		if (!isActive || (isActive && e.keyCode == 27)) {
			if (e.which == 27) $parent.find(toggle).trigger('focus')
			return $this.trigger('click')
		}

		var desc = ' li:not(.divider):visible a'
		var $items = $parent.find('[role="menu"]' + desc + ', [role="listbox"]' + desc)

		if (!$items.length) return

		var index = $items.index($items.filter(':focus'))

		if (e.keyCode == 38 && index > 0)                 index--                        // up
		if (e.keyCode == 40 && index < $items.length - 1) index++                        // down
		if (!~index)                                      index = 0

		$items.eq(index).trigger('focus')
	}

	function clearMenus(e) {
		if (e && e.which === 3) return
		$(backdrop).remove()
		$(toggle).each(function () {
			var $parent = getParent($(this))
			var relatedTarget = { relatedTarget: this }
			if(!$parent.hasClass('open')) return
			$parent.trigger(e = $.Event('hide.bs.dropdown', relatedTarget))
			if(e.isDefaultPrevented()) return
			$parent.removeClass('open').trigger('hidden.bs.dropdown', relatedTarget)
		})
	}

	function getParent($this) {
		var selector = $this.attr('data-target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
		}

		var $parent = selector && $(selector)

		return $parent && $parent.length ? $parent : $this.parent()
	}

	function Plugin(option) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.dropdown')

			if (!data) $this.data('bs.dropdown', (data = new Dropdown(this)))
			if (typeof option == 'string') data[option].call($this)
		})
	}

	var old = $.fn.dropdown

	$.fn.dropdown             = Plugin
	$.fn.dropdown.Constructor = Dropdown


	$.fn.dropdown.noConflict = function () {
		$.fn.dropdown = old
		return this
	}

	$(document)
		.on('click.bs.dropdown.data-api', clearMenus)
		.on('click.bs.dropdown.data-api', '.dropdown form', function (e) { e.stopPropagation() })
		.on('click.bs.dropdown.data-api', toggle, Dropdown.prototype.toggle)
		.on('keydown.bs.dropdown.data-api', toggle + ', [role="menu"], [role="listbox"]', Dropdown.prototype.keydown)

}(jQuery);

+function ($) {
	'use strict';


	var Modal = function (element, options) {
		this.options        = options
		this.$body          = $(document.body)
		this.$element       = $(element)
		this.$backdrop      =
			this.isShown        = null
		this.scrollbarWidth = 0

		if (this.options.remote) {
			this.$element
				.find('.modal-content')
				.load(this.options.remote, $.proxy(function () {
					this.$element.trigger('loaded.bs.modal')
				}, this))
		}
	}

	Modal.VERSION  = '3.2.0'

	Modal.DEFAULTS = {
		backdrop: true,
		keyboard: true,
		show: true
	}

	Modal.prototype.toggle = function (_relatedTarget) {
		return this.isShown ? this.hide() : this.show(_relatedTarget)
	}

	Modal.prototype.show = function (_relatedTarget) {
		var that = this
		var e    = $.Event('show.bs.modal', { relatedTarget: _relatedTarget })

		this.$element.trigger(e)

		if (this.isShown || e.isDefaultPrevented()) return

		this.isShown = true

		this.checkScrollbar()
		this.$body.addClass('modal-open')

		this.setScrollbar()
		this.escape()

		this.$element.on('click.dismiss.bs.modal', '[data-dismiss="modal"]', $.proxy(this.hide, this))

		this.backdrop(function () {
			var transition = $.support.transition && that.$element.hasClass('fade')

			if (!that.$element.parent().length) {
				that.$element.appendTo(that.$body) // don't move modals dom position
			}

			that.$element
				.show()
				.scrollTop(0)

			if (transition) {
				that.$element[0].offsetWidth // force reflow
			}

			that.$element
				.addClass('in')
				.attr('aria-hidden', false)

			that.enforceFocus()

			var e = $.Event('shown.bs.modal', { relatedTarget: _relatedTarget })

			transition ?
				that.$element.find('.modal-dialog') // wait for modal to slide in
					.one('bsTransitionEnd', function () {
						that.$element.trigger('focus').trigger(e)
					})
					.emulateTransitionEnd(300) :
				that.$element.trigger('focus').trigger(e)
		})
	}

	Modal.prototype.hide = function (e) {
		if (e) e.preventDefault()

		e = $.Event('hide.bs.modal')

		this.$element.trigger(e)

		if (!this.isShown || e.isDefaultPrevented()) return

		this.isShown = false

		this.$body.removeClass('modal-open')

		this.resetScrollbar()
		this.escape()

		$(document).off('focusin.bs.modal')

		this.$element
			.removeClass('in')
			.attr('aria-hidden', true)
			.off('click.dismiss.bs.modal')

		$.support.transition && this.$element.hasClass('fade') ?
			this.$element
				.one('bsTransitionEnd', $.proxy(this.hideModal, this))
				.emulateTransitionEnd(300) :
			this.hideModal()
	}

	Modal.prototype.enforceFocus = function () {
		$(document)
			.off('focusin.bs.modal') // guard against infinite focus loop
			.on('focusin.bs.modal', $.proxy(function (e) {
				if (this.$element[0] !== e.target && !this.$element.has(e.target).length) {
					this.$element.trigger('focus')
				}
			}, this))
	}

	Modal.prototype.escape = function () {
		if (this.isShown && this.options.keyboard) {
			this.$element.on('keyup.dismiss.bs.modal', $.proxy(function (e) {
				e.which == 27 && this.hide()
			}, this))
		} else if (!this.isShown) {
			this.$element.off('keyup.dismiss.bs.modal')
		}
	}

	Modal.prototype.hideModal = function () {
		var that = this
		this.$element.hide()
		this.backdrop(function () {
			that.$element.trigger('hidden.bs.modal')
		})
	}

	Modal.prototype.removeBackdrop = function () {
		this.$backdrop && this.$backdrop.remove()
		this.$backdrop = null
	}

	Modal.prototype.backdrop = function (callback) {
		var that = this
		var animate = this.$element.hasClass('fade') ? 'fade' : ''

		if (this.isShown && this.options.backdrop) {
			var doAnimate = $.support.transition && animate

			this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
				.appendTo(this.$body)

			this.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
				if (e.target !== e.currentTarget) return
				this.options.backdrop == 'static'
					? this.$element[0].focus.call(this.$element[0])
					: this.hide.call(this)
			}, this))

			if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

			this.$backdrop.addClass('in')

			if (!callback) return

			doAnimate ?
				this.$backdrop
					.one('bsTransitionEnd', callback)
					.emulateTransitionEnd(150) :
				callback()

		} else if (!this.isShown && this.$backdrop) {
			this.$backdrop.removeClass('in')

			var callbackRemove = function () {
				that.removeBackdrop()
				callback && callback()
			}
			$.support.transition && this.$element.hasClass('fade') ?
				this.$backdrop
					.one('bsTransitionEnd', callbackRemove)
					.emulateTransitionEnd(150) :
				callbackRemove()

		} else if (callback) {
			callback()
		}
	}

	Modal.prototype.checkScrollbar = function () {
		if (document.body.clientWidth >= window.innerWidth) return
		this.scrollbarWidth = this.scrollbarWidth || this.measureScrollbar()
	}

	Modal.prototype.setScrollbar = function () {
		var bodyPad = parseInt((this.$body.css('padding-right') || 0), 10)
		if (this.scrollbarWidth) this.$body.css('padding-right', bodyPad + this.scrollbarWidth)
	}

	Modal.prototype.resetScrollbar = function () {
		this.$body.css('padding-right', '')
	}

	Modal.prototype.measureScrollbar = function () { // thx walsh
		var scrollDiv = document.createElement('div')
		scrollDiv.className = 'modal-scrollbar-measure'
		this.$body.append(scrollDiv)
		var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
		this.$body[0].removeChild(scrollDiv)
		return scrollbarWidth
	}


	function Plugin(option, _relatedTarget) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.modal')
			var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option == 'object' && option)

			if (!data) $this.data('bs.modal', (data = new Modal(this, options)))
			if (typeof option == 'string') data[option](_relatedTarget)
			else if (options.show) data.show(_relatedTarget)
		})
	}

	var old = $.fn.modal

	$.fn.modal             = Plugin
	$.fn.modal.Constructor = Modal



	$.fn.modal.noConflict = function () {
		$.fn.modal = old
		return this
	}


	// MODAL DATA-API
	// ==============

	$(document).on('click.bs.modal.data-api', '[data-toggle="modal"]', function (e) {
		var $this   = $(this)
		var href    = $this.attr('href')
		var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) // strip for ie7
		var option  = $target.data('bs.modal') ? 'toggle' : $.extend({ remote: !/#/.test(href) && href }, $target.data(), $this.data())

		if ($this.is('a')) e.preventDefault()

		$target.one('show.bs.modal', function (showEvent) {
			if (showEvent.isDefaultPrevented()) return // only register focus restorer if modal will actually get shown
			$target.one('hidden.bs.modal', function () {
				$this.is(':visible') && $this.trigger('focus')
			})
		})
		Plugin.call($target, option, this)
	})

}(jQuery);

+function ($) {
	'use strict';

	var Tooltip = function (element, options) {
		this.type       =
			this.options    =
				this.enabled    =
					this.timeout    =
						this.hoverState =
							this.$element   = null

		this.init('tooltip', element, options)
	}

	Tooltip.VERSION  = '3.2.0'

	Tooltip.DEFAULTS = {
		animation: true,
		placement: 'top',
		selector: false,
		template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
		trigger: 'hover focus',
		title: '',
		delay: 0,
		html: false,
		container: false,
		viewport: {
			selector: 'body',
			padding: 0
		}
	}

	Tooltip.prototype.init = function (type, element, options) {
		this.enabled   = true
		this.type      = type
		this.$element  = $(element)
		this.options   = this.getOptions(options)
		this.$viewport = this.options.viewport && $(this.options.viewport.selector || this.options.viewport)

		var triggers = this.options.trigger.split(' ')

		for (var i = triggers.length; i--;) {
			var trigger = triggers[i]

			if (trigger == 'click') {
				this.$element.on('click.' + this.type, this.options.selector, $.proxy(this.toggle, this))
			} else if (trigger != 'manual') {
				var eventIn  = trigger == 'hover' ? 'mouseenter' : 'focusin'
				var eventOut = trigger == 'hover' ? 'mouseleave' : 'focusout'

				this.$element.on(eventIn  + '.' + this.type, this.options.selector, $.proxy(this.enter, this))
				this.$element.on(eventOut + '.' + this.type, this.options.selector, $.proxy(this.leave, this))
			}
		}

		this.options.selector ?
			(this._options = $.extend({}, this.options, { trigger: 'manual', selector: '' })) :
			this.fixTitle()
	}

	Tooltip.prototype.getDefaults = function () {
		return Tooltip.DEFAULTS
	}

	Tooltip.prototype.getOptions = function (options) {
		options = $.extend({}, this.getDefaults(), this.$element.data(), options)

		if (options.delay && typeof options.delay == 'number') {
			options.delay = {
				show: options.delay,
				hide: options.delay
			}
		}

		return options
	}

	Tooltip.prototype.getDelegateOptions = function () {
		var options  = {}
		var defaults = this.getDefaults()

		this._options && $.each(this._options, function (key, value) {
			if (defaults[key] != value) options[key] = value
		})

		return options
	}

	Tooltip.prototype.enter = function (obj) {
		var self = obj instanceof this.constructor ?
			obj : $(obj.currentTarget).data('bs.' + this.type)

		if (!self) {
			self = new this.constructor(obj.currentTarget, this.getDelegateOptions())
			$(obj.currentTarget).data('bs.' + this.type, self)
		}

		clearTimeout(self.timeout)

		self.hoverState = 'in'

		if (!self.options.delay || !self.options.delay.show) return self.show()

		self.timeout = setTimeout(function () {
			if (self.hoverState == 'in') self.show()
		}, self.options.delay.show)
	}

	Tooltip.prototype.leave = function (obj) {
		var self = obj instanceof this.constructor ?
			obj : $(obj.currentTarget).data('bs.' + this.type)

		if (!self) {
			self = new this.constructor(obj.currentTarget, this.getDelegateOptions())
			$(obj.currentTarget).data('bs.' + this.type, self)
		}

		clearTimeout(self.timeout)

		self.hoverState = 'out'

		if (!self.options.delay || !self.options.delay.hide) return self.hide()

		self.timeout = setTimeout(function () {
			if (self.hoverState == 'out') self.hide()
		}, self.options.delay.hide)
	}

	Tooltip.prototype.show = function () {
		var e = $.Event('show.bs.' + this.type)

		if (this.hasContent() && this.enabled) {
			this.$element.trigger(e)

			var inDom = $.contains(document.documentElement, this.$element[0])
			if (e.isDefaultPrevented() || !inDom) return
			var that = this

			var $tip = this.tip()

			var tipId = this.getUID(this.type)

			this.setContent()
			$tip.attr('id', tipId)
			this.$element.attr('aria-describedby', tipId)

			if (this.options.animation) $tip.addClass('fade')

			var placement = typeof this.options.placement == 'function' ?
				this.options.placement.call(this, $tip[0], this.$element[0]) :
				this.options.placement

			var autoToken = /\s?auto?\s?/i
			var autoPlace = autoToken.test(placement)
			if (autoPlace) placement = placement.replace(autoToken, '') || 'top'

			$tip
				.detach()
				.css({ top: 0, left: 0, display: 'block' })
				.addClass(placement)
				.data('bs.' + this.type, this)

			this.options.container ? $tip.appendTo(this.options.container) : $tip.insertAfter(this.$element)

			var pos          = this.getPosition()
			var actualWidth  = $tip[0].offsetWidth
			var actualHeight = $tip[0].offsetHeight

			if (autoPlace) {
				var orgPlacement = placement
				var $parent      = this.$element.parent()
				var parentDim    = this.getPosition($parent)

				placement = placement == 'bottom' && pos.top   + pos.height       + actualHeight - parentDim.scroll > parentDim.height ? 'top'    :
					placement == 'top'    && pos.top   - parentDim.scroll - actualHeight < 0                                   ? 'bottom' :
						placement == 'right'  && pos.right + actualWidth      > parentDim.width                                    ? 'left'   :
							placement == 'left'   && pos.left  - actualWidth      < parentDim.left                                     ? 'right'  :
								placement

				$tip
					.removeClass(orgPlacement)
					.addClass(placement)
			}

			var calculatedOffset = this.getCalculatedOffset(placement, pos, actualWidth, actualHeight)

			this.applyPlacement(calculatedOffset, placement)

			var complete = function () {
				that.$element.trigger('shown.bs.' + that.type)
				that.hoverState = null
			}

			$.support.transition && this.$tip.hasClass('fade') ?
				$tip
					.one('bsTransitionEnd', complete)
					.emulateTransitionEnd(150) :
				complete()
		}
	}

	Tooltip.prototype.applyPlacement = function (offset, placement) {
		var $tip   = this.tip()
		var width  = $tip[0].offsetWidth
		var height = $tip[0].offsetHeight

		// manually read margins because getBoundingClientRect includes difference
		var marginTop = parseInt($tip.css('margin-top'), 10)
		var marginLeft = parseInt($tip.css('margin-left'), 10)

		// we must check for NaN for ie 8/9
		if (isNaN(marginTop))  marginTop  = 0
		if (isNaN(marginLeft)) marginLeft = 0

		offset.top  = offset.top  + marginTop
		offset.left = offset.left + marginLeft

		// $.fn.offset doesn't round pixel values
		// so we use setOffset directly with our own function B-0
		$.offset.setOffset($tip[0], $.extend({
			using: function (props) {
				$tip.css({
					top: Math.round(props.top),
					left: Math.round(props.left)
				})
			}
		}, offset), 0)

		$tip.addClass('in')

		// check to see if placing tip in new offset caused the tip to resize itself
		var actualWidth  = $tip[0].offsetWidth
		var actualHeight = $tip[0].offsetHeight

		if (placement == 'top' && actualHeight != height) {
			offset.top = offset.top + height - actualHeight
		}

		var delta = this.getViewportAdjustedDelta(placement, offset, actualWidth, actualHeight)

		if (delta.left) offset.left += delta.left
		else offset.top += delta.top

		var arrowDelta          = delta.left ? delta.left * 2 - width + actualWidth : delta.top * 2 - height + actualHeight
		var arrowPosition       = delta.left ? 'left'        : 'top'
		var arrowOffsetPosition = delta.left ? 'offsetWidth' : 'offsetHeight'

		$tip.offset(offset)
		this.replaceArrow(arrowDelta, $tip[0][arrowOffsetPosition], arrowPosition)
	}

	Tooltip.prototype.replaceArrow = function (delta, dimension, position) {
		this.arrow().css(position, delta ? (50 * (1 - delta / dimension) + '%') : '')
	}

	Tooltip.prototype.setContent = function () {
		var $tip  = this.tip()
		var title = this.getTitle()

		$tip.find('.tooltip-inner')[this.options.html ? 'html' : 'text'](title)
		$tip.removeClass('fade in top bottom left right')
	}

	Tooltip.prototype.hide = function () {
		var that = this
		var $tip = this.tip()
		var e    = $.Event('hide.bs.' + this.type)

		this.$element.removeAttr('aria-describedby')

		function complete() {
			if (that.hoverState != 'in') $tip.detach()
			that.$element.trigger('hidden.bs.' + that.type)
		}

		this.$element.trigger(e)

		if (e.isDefaultPrevented()) return

		$tip.removeClass('in')

		$.support.transition && this.$tip.hasClass('fade') ?
			$tip
				.one('bsTransitionEnd', complete)
				.emulateTransitionEnd(150) :
			complete()

		this.hoverState = null

		return this
	}

	Tooltip.prototype.fixTitle = function () {
		var $e = this.$element
		if ($e.attr('title') || typeof ($e.attr('data-original-title')) != 'string') {
			$e.attr('data-original-title', $e.attr('title') || '').attr('title', '')
		}
	}

	Tooltip.prototype.hasContent = function () {
		return this.getTitle()
	}

	Tooltip.prototype.getPosition = function ($element) {
		$element   = $element || this.$element
		var el     = $element[0]
		var isBody = el.tagName == 'BODY'
		return $.extend({}, (typeof el.getBoundingClientRect == 'function') ? el.getBoundingClientRect() : null, {
			scroll: isBody ? document.documentElement.scrollTop || document.body.scrollTop : $element.scrollTop(),
			width:  isBody ? $(window).width()  : $element.outerWidth(),
			height: isBody ? $(window).height() : $element.outerHeight()
		}, isBody ? { top: 0, left: 0 } : $element.offset())
	}

	Tooltip.prototype.getCalculatedOffset = function (placement, pos, actualWidth, actualHeight) {
		return placement == 'bottom' ? { top: pos.top + pos.height,   left: pos.left + pos.width / 2 - actualWidth / 2  } :
			placement == 'top'    ? { top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2  } :
				placement == 'left'   ? { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth } :
					/* placement == 'right' */ { top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width   }

	}

	Tooltip.prototype.getViewportAdjustedDelta = function (placement, pos, actualWidth, actualHeight) {
		var delta = { top: 0, left: 0 }
		if (!this.$viewport) return delta

		var viewportPadding = this.options.viewport && this.options.viewport.padding || 0
		var viewportDimensions = this.getPosition(this.$viewport)

		if (/right|left/.test(placement)) {
			var topEdgeOffset    = pos.top - viewportPadding - viewportDimensions.scroll
			var bottomEdgeOffset = pos.top + viewportPadding - viewportDimensions.scroll + actualHeight
			if (topEdgeOffset < viewportDimensions.top) { // top overflow
				delta.top = viewportDimensions.top - topEdgeOffset
			} else if (bottomEdgeOffset > viewportDimensions.top + viewportDimensions.height) { // bottom overflow
				delta.top = viewportDimensions.top + viewportDimensions.height - bottomEdgeOffset
			}
		} else {
			var leftEdgeOffset  = pos.left - viewportPadding
			var rightEdgeOffset = pos.left + viewportPadding + actualWidth
			if (leftEdgeOffset < viewportDimensions.left) { // left overflow
				delta.left = viewportDimensions.left - leftEdgeOffset
			} else if (rightEdgeOffset > viewportDimensions.width) { // right overflow
				delta.left = viewportDimensions.left + viewportDimensions.width - rightEdgeOffset
			}
		}

		return delta
	}

	Tooltip.prototype.getTitle = function () {
		var title
		var $e = this.$element
		var o  = this.options

		title = $e.attr('data-original-title')
			|| (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title)

		return title
	}

	Tooltip.prototype.getUID = function (prefix) {
		do prefix += ~~(Math.random() * 1000000)
		while (document.getElementById(prefix))
		return prefix
	}

	Tooltip.prototype.tip = function () {
		return (this.$tip = this.$tip || $(this.options.template))
	}

	Tooltip.prototype.arrow = function () {
		return (this.$arrow = this.$arrow || this.tip().find('.tooltip-arrow'))
	}

	Tooltip.prototype.validate = function () {
		if (!this.$element[0].parentNode) {
			this.hide()
			this.$element = null
			this.options  = null
		}
	}

	Tooltip.prototype.enable = function () {
		this.enabled = true
	}

	Tooltip.prototype.disable = function () {
		this.enabled = false
	}

	Tooltip.prototype.toggleEnabled = function () {
		this.enabled = !this.enabled
	}

	Tooltip.prototype.toggle = function (e) {
		var self = this
		if (e) {
			self = $(e.currentTarget).data('bs.' + this.type)
			if (!self) {
				self = new this.constructor(e.currentTarget, this.getDelegateOptions())
				$(e.currentTarget).data('bs.' + this.type, self)
			}
		}

		self.tip().hasClass('in') ? self.leave(self) : self.enter(self)
	}

	Tooltip.prototype.destroy = function () {
		clearTimeout(this.timeout)
		this.hide().$element.off('.' + this.type).removeData('bs.' + this.type)
	}


	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.tooltip')
			var options = typeof option == 'object' && option

			if (!data && option == 'destroy') return
			if (!data) $this.data('bs.tooltip', (data = new Tooltip(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.tooltip

	$.fn.tooltip             = Plugin
	$.fn.tooltip.Constructor = Tooltip

	$.fn.tooltip.noConflict = function () {
		$.fn.tooltip = old
		return this
	}

}(jQuery);

+function($){

  "use strict";

 /* TYPEAHEAD PUBLIC CLASS DEFINITION
  * ================================= */

  var Typeahead = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.typeahead.defaults, options)
    this.matcher = this.options.matcher || this.matcher
    this.sorter = this.options.sorter || this.sorter
    this.highlighter = this.options.highlighter || this.highlighter
    this.updater = this.options.updater || this.updater
    this.source = this.options.source
    this.$menu = $(this.options.menu)
    this.shown = false
    this.listen()
  }

  Typeahead.prototype = {

    constructor: Typeahead

  , select: function () {
      var val = this.$menu.find('.active').attr('data-value')
      this.$element
        .val(this.updater(val))
        .change()
      return this.hide()
    }

  , updater: function (item) {
      return item
    }

  , show: function () {
      var pos = $.extend({}, this.$element.position(), {
        height: this.$element[0].offsetHeight
      })

      this.$menu
        .insertAfter(this.$element)
        .css({
          top: pos.top + pos.height
        , left: pos.left
        })
        .show()

      this.shown = true
      return this
    }

  , hide: function () {
      this.$menu.hide()
      this.shown = false
      return this
    }

  , lookup: function (event) {
      var items

      this.query = this.$element.val()

      if (!this.query || this.query.length < this.options.minLength) {
        return this.shown ? this.hide() : this
      }

      items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source

      return items ? this.process(items) : this
    }

  , process: function (items) {
      var that = this

      items = $.grep(items, function (item) {
        return that.matcher(item)
      })

      items = this.sorter(items)

      if (!items.length) {
        return this.shown ? this.hide() : this
      }

      return this.render(items.slice(0, this.options.items)).show()
    }

  , matcher: function (item) {
      return ~item.toLowerCase().indexOf(this.query.toLowerCase())
    }

  , sorter: function (items) {
      var beginswith = []
        , caseSensitive = []
        , caseInsensitive = []
        , item

      while (item = items.shift()) {
        if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
        else if (~item.indexOf(this.query)) caseSensitive.push(item)
        else caseInsensitive.push(item)
      }

      return beginswith.concat(caseSensitive, caseInsensitive)
    }

  , highlighter: function (item) {
      var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
      return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>'
      })
    }

  , render: function (items) {
      var that = this

      items = $(items).map(function (i, item) {
        i = $(that.options.item).attr('data-value', item)
        i.find('a').html(that.highlighter(item))
        return i[0]
      })

      items.first().addClass('active')
      this.$menu.html(items)
      return this
    }

  , next: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , next = active.next()

      if (!next.length) {
        next = $(this.$menu.find('li')[0])
      }

      next.addClass('active')
    }

  , prev: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , prev = active.prev()

      if (!prev.length) {
        prev = this.$menu.find('li').last()
      }

      prev.addClass('active')
    }

  , listen: function () {
      this.$element
        .on('focus',    $.proxy(this.focus, this))
        .on('blur',     $.proxy(this.blur, this))
        .on('keypress', $.proxy(this.keypress, this))
        .on('keyup',    $.proxy(this.keyup, this))

      if (this.eventSupported('keydown')) {
        this.$element.on('keydown', $.proxy(this.keydown, this))
      }

      this.$menu
        .on('click', $.proxy(this.click, this))
        .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
        .on('mouseleave', 'li', $.proxy(this.mouseleave, this))
    }

  , eventSupported: function(eventName) {
      var isSupported = eventName in this.$element
      if (!isSupported) {
        this.$element.setAttribute(eventName, 'return;')
        isSupported = typeof this.$element[eventName] === 'function'
      }
      return isSupported
    }

  , move: function (e) {
      if (!this.shown) return

      switch(e.keyCode) {
        case 9: // tab
        case 13: // enter
        case 27: // escape
          e.preventDefault()
          break

        case 38: // up arrow
          e.preventDefault()
          this.prev()
          break

        case 40: // down arrow
          e.preventDefault()
          this.next()
          break
      }

      e.stopPropagation()
    }

  , keydown: function (e) {
      this.suppressKeyPressRepeat = ~$.inArray(e.keyCode, [40,38,9,13,27])
      this.move(e)
    }

  , keypress: function (e) {
      if (this.suppressKeyPressRepeat) return
      this.move(e)
    }

  , keyup: function (e) {
      switch(e.keyCode) {
        case 40: // down arrow
        case 38: // up arrow
        case 16: // shift
        case 17: // ctrl
        case 18: // alt
          break

        case 9: // tab
        case 13: // enter
          if (!this.shown) return
          this.select()
          break

        case 27: // escape
          if (!this.shown) return
          this.hide()
          break

        default:
          this.lookup()
      }

      e.stopPropagation()
      e.preventDefault()
  }

  , focus: function (e) {
      this.focused = true
    }

  , blur: function (e) {
      this.focused = false
      if (!this.mousedover && this.shown) this.hide()
    }

  , click: function (e) {
      e.stopPropagation()
      e.preventDefault()
      this.select()
      this.$element.focus()
    }

  , mouseenter: function (e) {
      this.mousedover = true
      this.$menu.find('.active').removeClass('active')
      $(e.currentTarget).addClass('active')
    }

  , mouseleave: function (e) {
      this.mousedover = false
      if (!this.focused && this.shown) this.hide()
    }

  }


  /* TYPEAHEAD PLUGIN DEFINITION
   * =========================== */

  var old = $.fn.typeahead

  $.fn.typeahead = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('typeahead')
        , options = typeof option == 'object' && option
      if (!data) $this.data('typeahead', (data = new Typeahead(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.typeahead.defaults = {
    source: []
  , items: 8
  , menu: '<ul class="typeahead dropdown-menu"></ul>'
  , item: '<li><a href="#"></a></li>'
  , minLength: 1
  }

  $.fn.typeahead.Constructor = Typeahead


 /* TYPEAHEAD NO CONFLICT
  * =================== */

  $.fn.typeahead.noConflict = function () {
    $.fn.typeahead = old
    return this
  }


 /* TYPEAHEAD DATA-API
  * ================== */

  $(document).on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
    var $this = $(this)
    if ($this.data('typeahead')) return
    $this.typeahead($this.data())
  })

}(window.jQuery);

+function ($) {
	'use strict';


	var Popover = function (element, options) {
		this.init('popover', element, options)
	}

	if (!$.fn.tooltip) throw new Error('Popover requires tooltip.js')

	Popover.VERSION  = '3.2.0'

	Popover.DEFAULTS = $.extend({}, $.fn.tooltip.Constructor.DEFAULTS, {
		placement: 'right',
		trigger: 'click',
		content: '',
		template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
	})

	Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype)

	Popover.prototype.constructor = Popover

	Popover.prototype.getDefaults = function () {
		return Popover.DEFAULTS
	}

	Popover.prototype.setContent = function () {
		var $tip    = this.tip()
		var title   = this.getTitle()
		var content = this.getContent()

		$tip.find('.popover-title')[this.options.html ? 'html' : 'text'](title)
		$tip.find('.popover-content').empty()[ // we use append for html objects to maintain js events
			this.options.html ? (typeof content == 'string' ? 'html' : 'append') : 'text'
			](content)

		$tip.removeClass('fade top bottom left right in')

		if (!$tip.find('.popover-title').html()) $tip.find('.popover-title').hide()
	}

	Popover.prototype.hasContent = function () {
		return this.getTitle() || this.getContent()
	}

	Popover.prototype.getContent = function () {
		var $e = this.$element
		var o  = this.options

		return $e.attr('data-content')
			|| (typeof o.content == 'function' ?
			o.content.call($e[0]) :
			o.content)
	}

	Popover.prototype.arrow = function () {
		return (this.$arrow = this.$arrow || this.tip().find('.arrow'))
	}

	Popover.prototype.tip = function () {
		if (!this.$tip) this.$tip = $(this.options.template)
		return this.$tip
	}

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.popover')
			var options = typeof option == 'object' && option

			if (!data && option == 'destroy') return
			if (!data) $this.data('bs.popover', (data = new Popover(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.popover

	$.fn.popover             = Plugin
	$.fn.popover.Constructor = Popover


	// POPOVER NO CONFLICT
	// ===================

	$.fn.popover.noConflict = function () {
		$.fn.popover = old
		return this
	}

}(jQuery);


+function ($) {
	'use strict';

	// SCROLLSPY CLASS DEFINITION
	// ==========================

	function ScrollSpy(element, options) {
		var process  = $.proxy(this.process, this)

		this.$body          = $('body')
		this.$scrollElement = $(element).is('body') ? $(window) : $(element)
		this.options        = $.extend({}, ScrollSpy.DEFAULTS, options)
		this.selector       = (this.options.target || '') + ' .nav li > a'
		this.offsets        = []
		this.targets        = []
		this.activeTarget   = null
		this.scrollHeight   = 0

		this.$scrollElement.on('scroll.bs.scrollspy', process)
		this.refresh()
		this.process()
	}

	ScrollSpy.VERSION  = '3.2.0'

	ScrollSpy.DEFAULTS = {
		offset: 10
	}

	ScrollSpy.prototype.getScrollHeight = function () {
		return this.$scrollElement[0].scrollHeight || Math.max(this.$body[0].scrollHeight, document.documentElement.scrollHeight)
	}

	ScrollSpy.prototype.refresh = function () {
		var offsetMethod = 'offset'
		var offsetBase   = 0

		if (!$.isWindow(this.$scrollElement[0])) {
			offsetMethod = 'position'
			offsetBase   = this.$scrollElement.scrollTop()
		}

		this.offsets = []
		this.targets = []
		this.scrollHeight = this.getScrollHeight()

		var self     = this

		this.$body
			.find(this.selector)
			.map(function () {
				var $el   = $(this)
				var href  = $el.data('target') || $el.attr('href')
				var $href = /^#./.test(href) && $(href)

				return ($href
					&& $href.length
					&& $href.is(':visible')
					&& [[$href[offsetMethod]().top + offsetBase, href]]) || null
			})
			.sort(function (a, b) { return a[0] - b[0] })
			.each(function () {
				self.offsets.push(this[0])
				self.targets.push(this[1])
			})
	}

	ScrollSpy.prototype.process = function () {
		var scrollTop    = this.$scrollElement.scrollTop() + this.options.offset
		var scrollHeight = this.getScrollHeight()
		var maxScroll    = this.options.offset + scrollHeight - this.$scrollElement.height()
		var offsets      = this.offsets
		var targets      = this.targets
		var activeTarget = this.activeTarget
		var i

		if (this.scrollHeight != scrollHeight) {
			this.refresh()
		}

		if (scrollTop >= maxScroll) {
			return activeTarget != (i = targets[targets.length - 1]) && this.activate(i)
		}

		if (activeTarget && scrollTop <= offsets[0]) {
			return activeTarget != (i = targets[0]) && this.activate(i)
		}

		for (i = offsets.length; i--;) {
			activeTarget != targets[i]
				&& scrollTop >= offsets[i]
				&& (!offsets[i + 1] || scrollTop <= offsets[i + 1])
			&& this.activate(targets[i])
		}
	}

	ScrollSpy.prototype.activate = function (target) {
		this.activeTarget = target

		$(this.selector)
			.parentsUntil(this.options.target, '.active')
			.removeClass('active')

		var selector = this.selector +
			'[data-target="' + target + '"],' +
			this.selector + '[href="' + target + '"]'

		var active = $(selector)
			.parents('li')
			.addClass('active')

		if (active.parent('.dropdown-menu').length) {
			active = active
				.closest('li.dropdown')
				.addClass('active')
		}

		active.trigger('activate.bs.scrollspy')
	}


	// SCROLLSPY PLUGIN DEFINITION
	// ===========================

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.scrollspy')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bs.scrollspy', (data = new ScrollSpy(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.scrollspy

	$.fn.scrollspy             = Plugin
	$.fn.scrollspy.Constructor = ScrollSpy


	// SCROLLSPY NO CONFLICT
	// =====================

	$.fn.scrollspy.noConflict = function () {
		$.fn.scrollspy = old
		return this
	}


	// SCROLLSPY DATA-API
	// ==================

	$(window).on('load.bs.scrollspy.data-api', function () {
		$('[data-spy="scroll"]').each(function () {
			var $spy = $(this)
			Plugin.call($spy, $spy.data())
		})
	})

}(jQuery);

+function ($) {
	'use strict';

	// TAB CLASS DEFINITION
	// ====================

	var Tab = function (element) {
		this.element = $(element)
	}

	Tab.VERSION = '3.2.0'

	Tab.prototype.show = function () {
		var $this    = this.element
		var $ul      = $this.closest('ul:not(.dropdown-menu)')
		var selector = $this.data('target')

		if (!selector) {
			selector = $this.attr('href')
			selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
		}

		if ($this.parent('li').hasClass('active')) return

		var previous = $ul.find('.active:last a')[0]
		var e        = $.Event('show.bs.tab', {
			relatedTarget: previous
		})

		$this.trigger(e)

		if (e.isDefaultPrevented()) return

		var $target = $(selector)

		this.activate($this.closest('li'), $ul)
		this.activate($target, $target.parent(), function () {
			$this.trigger({
				type: 'shown.bs.tab',
				relatedTarget: previous
			})
		})
	}

	Tab.prototype.activate = function (element, container, callback) {
		var $active    = container.find('> .active')
		var transition = callback
			&& $.support.transition
			&& $active.hasClass('fade')

		function next() {
			$active
				.removeClass('active')
				.find('> .dropdown-menu > .active')
				.removeClass('active')

			element.addClass('active')

			if (transition) {
				element[0].offsetWidth // reflow for transition
				element.addClass('in')
			} else {
				element.removeClass('fade')
			}

			if (element.parent('.dropdown-menu')) {
				element.closest('li.dropdown').addClass('active')
			}

			callback && callback()
		}

		transition ?
			$active
				.one('bsTransitionEnd', next)
				.emulateTransitionEnd(150) :
			next()

		$active.removeClass('in')
	}


	// TAB PLUGIN DEFINITION
	// =====================

	function Plugin(option) {
		return this.each(function () {
			var $this = $(this)
			var data  = $this.data('bs.tab')

			if (!data) $this.data('bs.tab', (data = new Tab(this)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.tab

	$.fn.tab             = Plugin
	$.fn.tab.Constructor = Tab


	// TAB NO CONFLICT
	// ===============

	$.fn.tab.noConflict = function () {
		$.fn.tab = old
		return this
	}


	// TAB DATA-API
	// ============

	$(document).on('click.bs.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
		e.preventDefault()
		Plugin.call($(this), 'show')
	})

}(jQuery);

+function ($) {
	'use strict';

	// AFFIX CLASS DEFINITION
	// ======================

	var Affix = function (element, options) {
		this.options = $.extend({}, Affix.DEFAULTS, options)

		this.$target = $(this.options.target)
			.on('scroll.bs.affix.data-api', $.proxy(this.checkPosition, this))
			.on('click.bs.affix.data-api',  $.proxy(this.checkPositionWithEventLoop, this))

		this.$element     = $(element)
		this.affixed      =
			this.unpin        =
				this.pinnedOffset = null

		this.checkPosition()
	}

	Affix.VERSION  = '3.2.0'

	Affix.RESET    = 'affix affix-top affix-bottom'

	Affix.DEFAULTS = {
		offset: 0,
		target: window
	}

	Affix.prototype.getPinnedOffset = function () {
		if (this.pinnedOffset) return this.pinnedOffset
		this.$element.removeClass(Affix.RESET).addClass('affix')
		var scrollTop = this.$target.scrollTop()
		var position  = this.$element.offset()
		return (this.pinnedOffset = position.top - scrollTop)
	}

	Affix.prototype.checkPositionWithEventLoop = function () {
		setTimeout($.proxy(this.checkPosition, this), 1)
	}

	Affix.prototype.checkPosition = function () {
		if (!this.$element.is(':visible')) return

		var scrollHeight = $(document).height()
		var scrollTop    = this.$target.scrollTop()
		var position     = this.$element.offset()
		var offset       = this.options.offset
		var offsetTop    = offset.top
		var offsetBottom = offset.bottom

		if (typeof offset != 'object')         offsetBottom = offsetTop = offset
		if (typeof offsetTop == 'function')    offsetTop    = offset.top(this.$element)
		if (typeof offsetBottom == 'function') offsetBottom = offset.bottom(this.$element)

		var affix = this.unpin   != null && (scrollTop + this.unpin <= position.top) ? false :
			offsetBottom != null && (position.top + this.$element.height() >= scrollHeight - offsetBottom) ? 'bottom' :
				offsetTop    != null && (scrollTop <= offsetTop) ? 'top' : false

		if (this.affixed === affix) return
		if (this.unpin != null) this.$element.css('top', '')

		var affixType = 'affix' + (affix ? '-' + affix : '')
		var e         = $.Event(affixType + '.bs.affix')

		this.$element.trigger(e)

		if (e.isDefaultPrevented()) return

		this.affixed = affix
		this.unpin = affix == 'bottom' ? this.getPinnedOffset() : null

		this.$element
			.removeClass(Affix.RESET)
			.addClass(affixType)
			.trigger($.Event(affixType.replace('affix', 'affixed')))

		if (affix == 'bottom') {
			this.$element.offset({
				top: scrollHeight - this.$element.height() - offsetBottom
			})
		}
	}


	// AFFIX PLUGIN DEFINITION
	// =======================

	function Plugin(option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('bs.affix')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bs.affix', (data = new Affix(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	var old = $.fn.affix

	$.fn.affix             = Plugin
	$.fn.affix.Constructor = Affix


	// AFFIX NO CONFLICT
	// =================

	$.fn.affix.noConflict = function () {
		$.fn.affix = old
		return this
	}


	// AFFIX DATA-API
	// ==============

	$(window).on('load', function () {
		$('[data-spy="affix"]').each(function () {
			var $spy = $(this)
			var data = $spy.data()

			data.offset = data.offset || {}

			if (data.offsetBottom) data.offset.bottom = data.offsetBottom
			if (data.offsetTop)    data.offset.top    = data.offsetTop

			Plugin.call($spy, data)
		})
	})

}(jQuery);

// --------------------- Select
+function ($) {
	'use strict';

	// Case insensitive search
	$.expr[':'].icontains = function (obj, index, meta) {
		return icontains($(obj).text(), meta[3]);
	};

	// Case and accent insensitive search
	$.expr[':'].aicontains = function (obj, index, meta) {
		return icontains($(obj).data('normalizedText') || $(obj).text(), meta[3]);
	};

	/**
	 * Actual implementation of the case insensitive search.
	 * @access private
	 * @param {String} haystack
	 * @param {String} needle
	 * @returns {boolean}
	 */
	function icontains(haystack, needle) {
		return haystack.toUpperCase().indexOf(needle.toUpperCase()) > -1;
	}

	/**
	 * Remove all diatrics from the given text.
	 * @access private
	 * @param {String} text
	 * @returns {String}
	 */
	function normalizeToBase(text) {
		var rExps = [
			{re: /[\xC0-\xC6]/g, ch: "A"},
			{re: /[\xE0-\xE6]/g, ch: "a"},
			{re: /[\xC8-\xCB]/g, ch: "E"},
			{re: /[\xE8-\xEB]/g, ch: "e"},
			{re: /[\xCC-\xCF]/g, ch: "I"},
			{re: /[\xEC-\xEF]/g, ch: "i"},
			{re: /[\xD2-\xD6]/g, ch: "O"},
			{re: /[\xF2-\xF6]/g, ch: "o"},
			{re: /[\xD9-\xDC]/g, ch: "U"},
			{re: /[\xF9-\xFC]/g, ch: "u"},
			{re: /[\xC7-\xE7]/g, ch: "c"},
			{re: /[\xD1]/g, ch: "N"},
			{re: /[\xF1]/g, ch: "n"}
		];
		$.each(rExps, function () {
			text = text.replace(this.re, this.ch);
		});
		return text;
	}

	var Selectpicker = function (element, options, e) {
		if (e) {
			e.stopPropagation();
			e.preventDefault();
		}

		this.$element = $(element);
		this.$newElement = null;
		this.$button = null;
		this.$menu = null;
		this.$lis = null;
		this.options = options;

		// If we have no title yet, try to pull it from the html title attribute (jQuery doesnt' pick it up as it's not a
		// data-attribute)
		if (this.options.title === null) {
			this.options.title = this.$element.attr('title');
		}

		//Expose public methods
		this.val = Selectpicker.prototype.val;
		this.render = Selectpicker.prototype.render;
		this.refresh = Selectpicker.prototype.refresh;
		this.setStyle = Selectpicker.prototype.setStyle;
		this.selectAll = Selectpicker.prototype.selectAll;
		this.deselectAll = Selectpicker.prototype.deselectAll;
		this.destroy = Selectpicker.prototype.remove;
		this.remove = Selectpicker.prototype.remove;
		this.show = Selectpicker.prototype.show;
		this.hide = Selectpicker.prototype.hide;

		this.init();
	};

	Selectpicker.VERSION = '1.6.2';

	// part of this is duplicated in i18n/defaults-en_US.js. Make sure to update both.
	Selectpicker.DEFAULTS = {
		noneSelectedText: 'Nothing selected',
		noneResultsText: 'No results match',
		countSelectedText: function (numSelected, numTotal) {
			return (numSelected == 1) ? "{0} item selected" : "{0} items selected";
		},
		maxOptionsText: function (numAll, numGroup) {
			var arr = [];

			arr[0] = (numAll == 1) ? 'Limit reached ({n} item max)' : 'Limit reached ({n} items max)';
			arr[1] = (numGroup == 1) ? 'Group limit reached ({n} item max)' : 'Group limit reached ({n} items max)';

			return arr;
		},
		selectAllText: 'Select All',
		deselectAllText: 'Deselect All',
		multipleSeparator: ', ',
		style: 'btn-default',
		size: 'auto',
		title: null,
		selectedTextFormat: 'values',
		width: false,
		container: false,
		hideDisabled: false,
		showSubtext: false,
		showIcon: true,
		showContent: true,
		dropupAuto: true,
		header: false,
		liveSearch: false,
		actionsBox: false,
		iconBase: 'glyphicon',
		tickIcon: 'glyphicon-ok',
		maxOptions: false,
		mobile: false,
		selectOnTab: false,
		dropdownAlignRight: false,
		searchAccentInsensitive: false
	};

	Selectpicker.prototype = {

		constructor: Selectpicker,

		init: function () {
			var that = this,
				id = this.$element.attr('id');

			this.$element.hide();
			this.multiple = this.$element.prop('multiple');
			this.autofocus = this.$element.prop('autofocus');
			this.$newElement = this.createView();
			this.$element.after(this.$newElement);
			this.$menu = this.$newElement.find('> .dropdown-menu');
			this.$button = this.$newElement.find('> button');
			this.$searchbox = this.$newElement.find('input');

			if (this.options.dropdownAlignRight)
				this.$menu.addClass('dropdown-menu-right');

			if (typeof id !== 'undefined') {
				this.$button.attr('data-id', id);
				$('label[for="' + id + '"]').click(function (e) {
					e.preventDefault();
					that.$button.focus();
				});
			}

			this.checkDisabled();
			this.clickListener();
			if (this.options.liveSearch) this.liveSearchListener();
			this.render();
			this.liHeight();
			this.setStyle();
			this.setWidth();
			if (this.options.container) this.selectPosition();
			this.$menu.data('this', this);
			this.$newElement.data('this', this);
			if (this.options.mobile) this.mobile();
		},

		createDropdown: function () {
			// Options
			// If we are multiple, then add the show-tick class by default
			var multiple = this.multiple ? ' show-tick' : '',
				inputGroup = this.$element.parent().hasClass('input-group') ? ' input-group-btn' : '',
				autofocus = this.autofocus ? ' autofocus' : '',
				btnSize = this.$element.parents().hasClass('form-group-lg') ? ' btn-lg' : (this.$element.parents().hasClass('form-group-sm') ? ' btn-sm' : '');
			// Elements
			var header = this.options.header ? '<div class="popover-title"><button type="button" class="close" aria-hidden="true">&times;</button>' + this.options.header + '</div>' : '';
			var searchbox = this.options.liveSearch ? '<div class="bs-searchbox"><input type="text" class="input-block-level form-control" autocomplete="off" /></div>' : '';
			var actionsbox = this.options.actionsBox ? '<div class="bs-actionsbox">' +
				'<div class="btn-group btn-block">' +
				'<button class="actions-btn bs-select-all btn btn-sm btn-default">' +
				this.options.selectAllText +
				'</button>' +
				'<button class="actions-btn bs-deselect-all btn btn-sm btn-default">' +
				this.options.deselectAllText +
				'</button>' +
				'</div>' +
				'</div>' : '';
			var drop =
				'<div class="btn-group bootstrap-select' + multiple + inputGroup + '">' +
					'<button type="button" class="btn dropdown-toggle selectpicker' + btnSize + '" data-toggle="dropdown"' + autofocus + '>' +
					'<span class="filter-option pull-left"></span>&nbsp;' +
					'<span class="caret"></span>' +
					'</button>' +
					'<div class="dropdown-menu open">' +
					header +
					searchbox +
					actionsbox +
					'<ul class="dropdown-menu inner selectpicker" role="menu">' +
					'</ul>' +
					'</div>' +
					'</div>';

			return $(drop);
		},

		createView: function () {
			var $drop = this.createDropdown();
			var $li = this.createLi();
			$drop.find('ul').append($li);
			return $drop;
		},

		reloadLi: function () {
			//Remove all children.
			this.destroyLi();
			//Re build
			var $li = this.createLi();
			this.$menu.find('ul').append($li);
		},

		destroyLi: function () {
			this.$menu.find('li').remove();
		},

		createLi: function () {
			var that = this,
				_li = [],
				optID = 0;

			// Helper functions
			/**
			 * @param content
			 * @param [index]
			 * @param [classes]
			 * @returns {string}
			 */
			var generateLI = function (content, index, classes) {
				return '<li' +
					(typeof classes !== 'undefined' ? ' class="' + classes + '"' : '') +
					(typeof index !== 'undefined' | null === index ? ' data-original-index="' + index + '"' : '') +
					'>' + content + '</li>';
			};

			/**
			 * @param text
			 * @param [classes]
			 * @param [inline]
			 * @param [optgroup]
			 * @returns {string}
			 */
			var generateA = function (text, classes, inline, optgroup) {
				var normText = normalizeToBase($.trim($("<div/>").html(text).text()).replace(/\s\s+/g, ' '));
				return '<a tabindex="0"' +
					(typeof classes !== 'undefined' ? ' class="' + classes + '"' : '') +
					(typeof inline !== 'undefined' ? ' style="' + inline + '"' : '') +
					(typeof optgroup !== 'undefined' ? 'data-optgroup="' + optgroup + '"' : '') +
					' data-normalized-text="' + normText + '"' +
					'>' + text +
					'<span class="' + that.options.iconBase + ' ' + that.options.tickIcon + ' check-mark"></span>' +
					'</a>';
			};

			this.$element.find('option').each(function () {
				var $this = $(this);

				// Get the class and text for the option
				var optionClass = $this.attr('class') || '',
					inline = $this.attr('style'),
					text = $this.data('content') ? $this.data('content') : $this.html(),
					subtext = typeof $this.data('subtext') !== 'undefined' ? '<small class="muted text-muted">' + $this.data('subtext') + '</small>' : '',
					icon = typeof $this.data('icon') !== 'undefined' ? '<span class="' + that.options.iconBase + ' ' + $this.data('icon') + '"></span> ' : '',
					isDisabled = $this.is(':disabled') || $this.parent().is(':disabled'),
					index = $this[0].index;
				if (icon !== '' && isDisabled) {
					icon = '<span>' + icon + '</span>';
				}

				if (!$this.data('content')) {
					// Prepend any icon and append any subtext to the main text.
					text = icon + '<span class="text">' + text + subtext + '</span>';
				}

				if (that.options.hideDisabled && isDisabled) {
					return;
				}

				if ($this.parent().is('optgroup') && $this.data('divider') !== true) {
					if ($this.index() === 0) { // Is it the first option of the optgroup?
						optID += 1;

						// Get the opt group label
						var label = $this.parent().attr('label');
						var labelSubtext = typeof $this.parent().data('subtext') !== 'undefined' ? '<small class="muted text-muted">' + $this.parent().data('subtext') + '</small>' : '';
						var labelIcon = $this.parent().data('icon') ? '<span class="' + that.options.iconBase + ' ' + $this.parent().data('icon') + '"></span> ' : '';
						label = labelIcon + '<span class="text">' + label + labelSubtext + '</span>';

						if (index !== 0 && _li.length > 0) { // Is it NOT the first option of the select && are there elements in the dropdown?
							_li.push(generateLI('', null, 'divider'));
						}

						_li.push(generateLI(label, null, 'dropdown-header'));
					}

					_li.push(generateLI(generateA(text, 'opt ' + optionClass, inline, optID), index));
				} else if ($this.data('divider') === true) {
					_li.push(generateLI('', index, 'divider'));
				} else if ($this.data('hidden') === true) {
					_li.push(generateLI(generateA(text, optionClass, inline), index, 'hide is-hidden'));
				} else {
					_li.push(generateLI(generateA(text, optionClass, inline), index));
				}
			});

			//If we are not multiple, we don't have a selected item, and we don't have a title, select the first element so something is set in the button
			if (!this.multiple && this.$element.find('option:selected').length === 0 && !this.options.title) {
				this.$element.find('option').eq(0).prop('selected', true).attr('selected', 'selected');
			}

			return $(_li.join(''));
		},

		findLis: function () {
			if (this.$lis == null) this.$lis = this.$menu.find('li');
			return this.$lis;
		},

		/**
		 * @param [updateLi] defaults to true
		 */
		render: function (updateLi) {
			var that = this;

			//Update the LI to match the SELECT
			if (updateLi !== false) {
				this.$element.find('option').each(function (index) {
					that.setDisabled(index, $(this).is(':disabled') || $(this).parent().is(':disabled'));
					that.setSelected(index, $(this).is(':selected'));
				});
			}

			this.tabIndex();
			var notDisabled = this.options.hideDisabled ? ':not([disabled])' : '';
			var selectedItems = this.$element.find('option:selected' + notDisabled).map(function () {
				var $this = $(this);
				var icon = $this.data('icon') && that.options.showIcon ? '<i class="' + that.options.iconBase + ' ' + $this.data('icon') + '"></i> ' : '';
				var subtext;
				if (that.options.showSubtext && $this.attr('data-subtext') && !that.multiple) {
					subtext = ' <small class="muted text-muted">' + $this.data('subtext') + '</small>';
				} else {
					subtext = '';
				}
				if ($this.data('content') && that.options.showContent) {
					return $this.data('content');
				} else if (typeof $this.attr('title') !== 'undefined') {
					return $this.attr('title');
				} else {
					return icon + $this.html() + subtext;
				}
			}).toArray();

			//Fixes issue in IE10 occurring when no default option is selected and at least one option is disabled
			//Convert all the values into a comma delimited string
			var title = !this.multiple ? selectedItems[0] : selectedItems.join(this.options.multipleSeparator);

			//If this is multi select, and the selectText type is count, the show 1 of 2 selected etc..
			if (this.multiple && this.options.selectedTextFormat.indexOf('count') > -1) {
				var max = this.options.selectedTextFormat.split('>');
				if ((max.length > 1 && selectedItems.length > max[1]) || (max.length == 1 && selectedItems.length >= 2)) {
					notDisabled = this.options.hideDisabled ? ', [disabled]' : '';
					var totalCount = this.$element.find('option').not('[data-divider="true"], [data-hidden="true"]' + notDisabled).length,
						tr8nText = (typeof this.options.countSelectedText === 'function') ? this.options.countSelectedText(selectedItems.length, totalCount) : this.options.countSelectedText;
					title = tr8nText.replace('{0}', selectedItems.length.toString()).replace('{1}', totalCount.toString());
				}
			}

			this.options.title = this.$element.attr('title');

			if (this.options.selectedTextFormat == 'static') {
				title = this.options.title;
			}

			//If we dont have a title, then use the default, or if nothing is set at all, use the not selected text
			if (!title) {
				title = typeof this.options.title !== 'undefined' ? this.options.title : this.options.noneSelectedText;
			}

			this.$button.attr('title', $.trim($("<div/>").html(title).text()).replace(/\s\s+/g, ' '));
			this.$newElement.find('.filter-option').html(title);
		},

		/**
		 * @param [style]
		 * @param [status]
		 */
		setStyle: function (style, status) {
			if (this.$element.attr('class')) {
				this.$newElement.addClass(this.$element.attr('class').replace(/selectpicker|mobile-device|validate\[.*\]/gi, ''));
			}

			var buttonClass = style ? style : this.options.style;

			if (status == 'add') {
				this.$button.addClass(buttonClass);
			} else if (status == 'remove') {
				this.$button.removeClass(buttonClass);
			} else {
				this.$button.removeClass(this.options.style);
				this.$button.addClass(buttonClass);
			}
		},

		liHeight: function () {
			if (this.options.size === false) return;

			var $selectClone = this.$menu.parent().clone().find('> .dropdown-toggle').prop('autofocus', false).end().appendTo('body'),
				$menuClone = $selectClone.addClass('open').find('> .dropdown-menu'),
				liHeight = $menuClone.find('li').not('.divider').not('.dropdown-header').filter(':visible').children('a').outerHeight(),
				headerHeight = this.options.header ? $menuClone.find('.popover-title').outerHeight() : 0,
				searchHeight = this.options.liveSearch ? $menuClone.find('.bs-searchbox').outerHeight() : 0,
				actionsHeight = this.options.actionsBox ? $menuClone.find('.bs-actionsbox').outerHeight() : 0;

			$selectClone.remove();

			this.$newElement
				.data('liHeight', liHeight)
				.data('headerHeight', headerHeight)
				.data('searchHeight', searchHeight)
				.data('actionsHeight', actionsHeight);
		},

		setSize: function () {
			this.findLis();
			var that = this,
				menu = this.$menu,
				menuInner = menu.find('.inner'),
				selectHeight = this.$newElement.outerHeight(),
				liHeight = this.$newElement.data('liHeight'),
				headerHeight = this.$newElement.data('headerHeight'),
				searchHeight = this.$newElement.data('searchHeight'),
				actionsHeight = this.$newElement.data('actionsHeight'),
				divHeight = this.$lis.filter('.divider').outerHeight(true),
				menuPadding = parseInt(menu.css('padding-top')) +
					parseInt(menu.css('padding-bottom')) +
					parseInt(menu.css('border-top-width')) +
					parseInt(menu.css('border-bottom-width')),
				notDisabled = this.options.hideDisabled ? ', .disabled' : '',
				$window = $(window),
				menuExtras = menuPadding + parseInt(menu.css('margin-top')) + parseInt(menu.css('margin-bottom')) + 2,
				menuHeight,
				selectOffsetTop,
				selectOffsetBot,
				posVert = function () {
					// JQuery defines a scrollTop function, but in pure JS it's a property
					//noinspection JSValidateTypes
					selectOffsetTop = that.$newElement.offset().top - $window.scrollTop();
					selectOffsetBot = $window.height() - selectOffsetTop - selectHeight;
				};
			posVert();
			if (this.options.header) menu.css('padding-top', 0);

			if (this.options.size == 'auto') {
				var getSize = function () {
					var minHeight,
						lisVis = that.$lis.not('.hide');

					posVert();
					menuHeight = selectOffsetBot - menuExtras;

					if (that.options.dropupAuto) {
						that.$newElement.toggleClass('dropup', (selectOffsetTop > selectOffsetBot) && ((menuHeight - menuExtras) < menu.height()));
					}
					if (that.$newElement.hasClass('dropup')) {
						menuHeight = selectOffsetTop - menuExtras;
					}

					if ((lisVis.length + lisVis.filter('.dropdown-header').length) > 3) {
						minHeight = liHeight * 3 + menuExtras - 2;
					} else {
						minHeight = 0;
					}

					menu.css({'max-height': menuHeight + 'px', 'overflow': 'hidden', 'min-height': minHeight + headerHeight + searchHeight + actionsHeight + 'px'});
					menuInner.css({'max-height': menuHeight - headerHeight - searchHeight - actionsHeight - menuPadding + 'px', 'overflow-y': 'auto', 'min-height': Math.max(minHeight - menuPadding, 0) + 'px'});
				};
				getSize();
				this.$searchbox.off('input.getSize propertychange.getSize').on('input.getSize propertychange.getSize', getSize);
				$(window).off('resize.getSize').on('resize.getSize', getSize);
				$(window).off('scroll.getSize').on('scroll.getSize', getSize);
			} else if (this.options.size && this.options.size != 'auto' && menu.find('li' + notDisabled).length > this.options.size) {
				var optIndex = this.$lis.not('.divider' + notDisabled).find(' > *').slice(0, this.options.size).last().parent().index();
				var divLength = this.$lis.slice(0, optIndex + 1).filter('.divider').length;
				menuHeight = liHeight * this.options.size + divLength * divHeight + menuPadding;
				if (that.options.dropupAuto) {
					//noinspection JSUnusedAssignment
					this.$newElement.toggleClass('dropup', (selectOffsetTop > selectOffsetBot) && (menuHeight < menu.height()));
				}
				menu.css({'max-height': menuHeight + headerHeight + searchHeight + actionsHeight + 'px', 'overflow': 'hidden'});
				menuInner.css({'max-height': menuHeight - menuPadding + 'px', 'overflow-y': 'auto'});
			}
		},

		setWidth: function () {
			if (this.options.width == 'auto') {
				this.$menu.css('min-width', '0');

				// Get correct width if element hidden
				var selectClone = this.$newElement.clone().appendTo('body');
				var ulWidth = selectClone.find('> .dropdown-menu').css('width');
				var btnWidth = selectClone.css('width', 'auto').find('> button').css('width');
				selectClone.remove();

				// Set width to whatever's larger, button title or longest option
				this.$newElement.css('width', Math.max(parseInt(ulWidth), parseInt(btnWidth)) + 'px');
			} else if (this.options.width == 'fit') {
				// Remove inline min-width so width can be changed from 'auto'
				this.$menu.css('min-width', '');
				this.$newElement.css('width', '').addClass('fit-width');
			} else if (this.options.width) {
				// Remove inline min-width so width can be changed from 'auto'
				this.$menu.css('min-width', '');
				this.$newElement.css('width', this.options.width);
			} else {
				// Remove inline min-width/width so width can be changed
				this.$menu.css('min-width', '');
				this.$newElement.css('width', '');
			}
			// Remove fit-width class if width is changed programmatically
			if (this.$newElement.hasClass('fit-width') && this.options.width !== 'fit') {
				this.$newElement.removeClass('fit-width');
			}
		},

		selectPosition: function () {
			var that = this,
				drop = '<div />',
				$drop = $(drop),
				pos,
				actualHeight,
				getPlacement = function ($element) {
					$drop.addClass($element.attr('class').replace(/form-control/gi, '')).toggleClass('dropup', $element.hasClass('dropup'));
					pos = $element.offset();
					actualHeight = $element.hasClass('dropup') ? 0 : $element[0].offsetHeight;
					$drop.css({'top': pos.top + actualHeight, 'left': pos.left, 'width': $element[0].offsetWidth, 'position': 'absolute'});
				};
			this.$newElement.on('click', function () {
				if (that.isDisabled()) {
					return;
				}
				getPlacement($(this));
				$drop.appendTo(that.options.container);
				$drop.toggleClass('open', !$(this).hasClass('open'));
				$drop.append(that.$menu);
			});
			$(window).resize(function () {
				getPlacement(that.$newElement);
			});
			$(window).on('scroll', function () {
				getPlacement(that.$newElement);
			});
			$('html').on('click', function (e) {
				if ($(e.target).closest(that.$newElement).length < 1) {
					$drop.removeClass('open');
				}
			});
		},

		setSelected: function (index, selected) {
			this.findLis();
			this.$lis.filter('[data-original-index="' + index + '"]').toggleClass('selected', selected);
		},

		setDisabled: function (index, disabled) {
			this.findLis();
			if (disabled) {
				this.$lis.filter('[data-original-index="' + index + '"]').addClass('disabled').find('a').attr('href', '#').attr('tabindex', -1);
			} else {
				this.$lis.filter('[data-original-index="' + index + '"]').removeClass('disabled').find('a').removeAttr('href').attr('tabindex', 0);
			}
		},

		isDisabled: function () {
			return this.$element.is(':disabled');
		},

		checkDisabled: function () {
			var that = this;

			if (this.isDisabled()) {
				this.$button.addClass('disabled').attr('tabindex', -1);
			} else {
				if (this.$button.hasClass('disabled')) {
					this.$button.removeClass('disabled');
				}

				if (this.$button.attr('tabindex') == -1) {
					if (!this.$element.data('tabindex')) this.$button.removeAttr('tabindex');
				}
			}

			this.$button.click(function () {
				return !that.isDisabled();
			});
		},

		tabIndex: function () {
			if (this.$element.is('[tabindex]')) {
				this.$element.data('tabindex', this.$element.attr('tabindex'));
				this.$button.attr('tabindex', this.$element.data('tabindex'));
			}
		},

		clickListener: function () {
			var that = this;

			this.$newElement.on('touchstart.dropdown', '.dropdown-menu', function (e) {
				e.stopPropagation();
			});

			this.$newElement.on('click', function () {
				that.setSize();
				if (!that.options.liveSearch && !that.multiple) {
					setTimeout(function () {
						that.$menu.find('.selected a').focus();
					}, 10);
				}
			});

			this.$menu.on('click', 'li a', function (e) {
				var $this = $(this),
					clickedIndex = $this.parent().data('originalIndex'),
					prevValue = that.$element.val(),
					prevIndex = that.$element.prop('selectedIndex');

				// Don't close on multi choice menu
				if (that.multiple) {
					e.stopPropagation();
				}

				e.preventDefault();

				//Don't run if we have been disabled
				if (!that.isDisabled() && !$this.parent().hasClass('disabled')) {
					var $options = that.$element.find('option'),
						$option = $options.eq(clickedIndex),
						state = $option.prop('selected'),
						$optgroup = $option.parent('optgroup'),
						maxOptions = that.options.maxOptions,
						maxOptionsGrp = $optgroup.data('maxOptions') || false;

					if (!that.multiple) { // Deselect all others if not multi select box
						$options.prop('selected', false);
						$option.prop('selected', true);
						that.$menu.find('.selected').removeClass('selected');
						that.setSelected(clickedIndex, true);
					} else { // Toggle the one we have chosen if we are multi select.
						$option.prop('selected', !state);
						that.setSelected(clickedIndex, !state);
						$this.blur();

						if ((maxOptions !== false) || (maxOptionsGrp !== false)) {
							var maxReached = maxOptions < $options.filter(':selected').length,
								maxReachedGrp = maxOptionsGrp < $optgroup.find('option:selected').length;

							if ((maxOptions && maxReached) || (maxOptionsGrp && maxReachedGrp)) {
								if (maxOptions && maxOptions == 1) {
									$options.prop('selected', false);
									$option.prop('selected', true);
									that.$menu.find('.selected').removeClass('selected');
									that.setSelected(clickedIndex, true);
								} else if (maxOptionsGrp && maxOptionsGrp == 1) {
									$optgroup.find('option:selected').prop('selected', false);
									$option.prop('selected', true);
									var optgroupID = $this.data('optgroup');

									that.$menu.find('.selected').has('a[data-optgroup="' + optgroupID + '"]').removeClass('selected');

									that.setSelected(clickedIndex, true);
								} else {
									var maxOptionsArr = (typeof that.options.maxOptionsText === 'function') ?
											that.options.maxOptionsText(maxOptions, maxOptionsGrp) : that.options.maxOptionsText,
										maxTxt = maxOptionsArr[0].replace('{n}', maxOptions),
										maxTxtGrp = maxOptionsArr[1].replace('{n}', maxOptionsGrp),
										$notify = $('<div class="notify"></div>');
									// If {var} is set in array, replace it
									/** @deprecated */
									if (maxOptionsArr[2]) {
										maxTxt = maxTxt.replace('{var}', maxOptionsArr[2][maxOptions > 1 ? 0 : 1]);
										maxTxtGrp = maxTxtGrp.replace('{var}', maxOptionsArr[2][maxOptionsGrp > 1 ? 0 : 1]);
									}

									$option.prop('selected', false);

									that.$menu.append($notify);

									if (maxOptions && maxReached) {
										$notify.append($('<div>' + maxTxt + '</div>'));
										that.$element.trigger('maxReached.bs.select');
									}

									if (maxOptionsGrp && maxReachedGrp) {
										$notify.append($('<div>' + maxTxtGrp + '</div>'));
										that.$element.trigger('maxReachedGrp.bs.select');
									}

									setTimeout(function () {
										that.setSelected(clickedIndex, false);
									}, 10);

									$notify.delay(750).fadeOut(300, function () {
										$(this).remove();
									});
								}
							}
						}
					}

					if (!that.multiple) {
						that.$button.focus();
					} else if (that.options.liveSearch) {
						that.$searchbox.focus();
					}

					// Trigger select 'change'
					if ((prevValue != that.$element.val() && that.multiple) || (prevIndex != that.$element.prop('selectedIndex') && !that.multiple)) {
						that.$element.change();
					}
				}
			});

			this.$menu.on('click', 'li.disabled a, .popover-title, .popover-title :not(.close)', function (e) {
				if (e.target == this) {
					e.preventDefault();
					e.stopPropagation();
					if (!that.options.liveSearch) {
						that.$button.focus();
					} else {
						that.$searchbox.focus();
					}
				}
			});

			this.$menu.on('click', 'li.divider, li.dropdown-header', function (e) {
				e.preventDefault();
				e.stopPropagation();
				if (!that.options.liveSearch) {
					that.$button.focus();
				} else {
					that.$searchbox.focus();
				}
			});

			this.$menu.on('click', '.popover-title .close', function () {
				that.$button.focus();
			});

			this.$searchbox.on('click', function (e) {
				e.stopPropagation();
			});


			this.$menu.on('click', '.actions-btn', function (e) {
				if (that.options.liveSearch) {
					that.$searchbox.focus();
				} else {
					that.$button.focus();
				}

				e.preventDefault();
				e.stopPropagation();

				if ($(this).is('.bs-select-all')) {
					that.selectAll();
				} else {
					that.deselectAll();
				}
				that.$element.change();
			});

			this.$element.change(function () {
				that.render(false);
			});
		},

		liveSearchListener: function () {
			var that = this,
				no_results = $('<li class="no-results"></li>');

			this.$newElement.on('click.dropdown.data-api', function () {
				that.$menu.find('.active').removeClass('active');
				if (!!that.$searchbox.val()) {
					that.$searchbox.val('');
					that.$lis.not('.is-hidden').removeClass('hide');
					if (!!no_results.parent().length) no_results.remove();
				}
				if (!that.multiple) that.$menu.find('.selected').addClass('active');
				setTimeout(function () {
					that.$searchbox.focus();

				}, 10);
			});

			this.$searchbox.on('input propertychange', function () {
				if (that.$searchbox.val()) {

					if (that.options.searchAccentInsensitive) {
						that.$lis.not('.is-hidden').removeClass('hide').find('a').not(':aicontains(' + normalizeToBase(that.$searchbox.val()) + ')').parent().addClass('hide');
					} else {
						that.$lis.not('.is-hidden').removeClass('hide').find('a').not(':icontains(' + that.$searchbox.val() + ')').parent().addClass('hide');
					}

					if (!that.$menu.find('li').filter(':visible:not(.no-results)').length) {
						if (!!no_results.parent().length) no_results.remove();
						no_results.html(that.options.noneResultsText + ' "' + that.$searchbox.val() + '"').show();
						that.$menu.find('li').last().after(no_results);
					} else if (!!no_results.parent().length) {
						no_results.remove();
					}

				} else {
					that.$lis.not('.is-hidden').removeClass('hide');
					if (!!no_results.parent().length) no_results.remove();
				}

				that.$menu.find('li.active').removeClass('active');
				that.$menu.find('li').filter(':visible:not(.divider)').eq(0).addClass('active').find('a').focus();
				$(this).focus();
			});
		},

		val: function (value) {
			if (typeof value !== 'undefined') {
				this.$element.val(value);
				this.render();

				return this.$element;
			} else {
				return this.$element.val();
			}
		},

		selectAll: function () {
			this.findLis();
			this.$lis.not('.divider').not('.disabled').not('.selected').filter(':visible').find('a').click();
		},

		deselectAll: function () {
			this.findLis();
			this.$lis.not('.divider').not('.disabled').filter('.selected').filter(':visible').find('a').click();
		},

		keydown: function (e) {
			var $this = $(this),
				$parent = ($this.is('input')) ? $this.parent().parent() : $this.parent(),
				$items,
				that = $parent.data('this'),
				index,
				next,
				first,
				last,
				prev,
				nextPrev,
				prevIndex,
				isActive,
				keyCodeMap = {
					32: ' ', 48: '0', 49: '1', 50: '2', 51: '3', 52: '4', 53: '5', 54: '6', 55: '7', 56: '8', 57: '9', 59: ';',
					65: 'a', 66: 'b', 67: 'c', 68: 'd', 69: 'e', 70: 'f', 71: 'g', 72: 'h', 73: 'i', 74: 'j', 75: 'k', 76: 'l',
					77: 'm', 78: 'n', 79: 'o', 80: 'p', 81: 'q', 82: 'r', 83: 's', 84: 't', 85: 'u', 86: 'v', 87: 'w', 88: 'x',
					89: 'y', 90: 'z', 96: '0', 97: '1', 98: '2', 99: '3', 100: '4', 101: '5', 102: '6', 103: '7', 104: '8', 105: '9'
				};

			if (that.options.liveSearch) $parent = $this.parent().parent();

			if (that.options.container) $parent = that.$menu;

			$items = $('[role=menu] li a', $parent);

			isActive = that.$menu.parent().hasClass('open');

			if (!isActive && /([0-9]|[A-z])/.test(String.fromCharCode(e.keyCode))) {
				if (!that.options.container) {
					that.setSize();
					that.$menu.parent().addClass('open');
					isActive = true;
				} else {
					that.$newElement.trigger('click');
				}
				that.$searchbox.focus();
			}

			if (that.options.liveSearch) {
				if (/(^9$|27)/.test(e.keyCode.toString(10)) && isActive && that.$menu.find('.active').length === 0) {
					e.preventDefault();
					that.$menu.parent().removeClass('open');
					that.$button.focus();
				}
				$items = $('[role=menu] li:not(.divider):not(.dropdown-header):visible', $parent);
				if (!$this.val() && !/(38|40)/.test(e.keyCode.toString(10))) {
					if ($items.filter('.active').length === 0) {
						if (that.options.searchAccentInsensitive) {
							$items = that.$newElement.find('li').filter(':aicontains(' + normalizeToBase(keyCodeMap[e.keyCode]) + ')');
						} else {
							$items = that.$newElement.find('li').filter(':icontains(' + keyCodeMap[e.keyCode] + ')');
						}
					}
				}
			}

			if (!$items.length) return;

			if (/(38|40)/.test(e.keyCode.toString(10))) {
				index = $items.index($items.filter(':focus'));
				first = $items.parent(':not(.disabled):visible').first().index();
				last = $items.parent(':not(.disabled):visible').last().index();
				next = $items.eq(index).parent().nextAll(':not(.disabled):visible').eq(0).index();
				prev = $items.eq(index).parent().prevAll(':not(.disabled):visible').eq(0).index();
				nextPrev = $items.eq(next).parent().prevAll(':not(.disabled):visible').eq(0).index();

				if (that.options.liveSearch) {
					$items.each(function (i) {
						if ($(this).is(':not(.disabled)')) {
							$(this).data('index', i);
						}
					});
					index = $items.index($items.filter('.active'));
					first = $items.filter(':not(.disabled):visible').first().data('index');
					last = $items.filter(':not(.disabled):visible').last().data('index');
					next = $items.eq(index).nextAll(':not(.disabled):visible').eq(0).data('index');
					prev = $items.eq(index).prevAll(':not(.disabled):visible').eq(0).data('index');
					nextPrev = $items.eq(next).prevAll(':not(.disabled):visible').eq(0).data('index');
				}

				prevIndex = $this.data('prevIndex');

				if (e.keyCode == 38) {
					if (that.options.liveSearch) index -= 1;
					if (index != nextPrev && index > prev) index = prev;
					if (index < first) index = first;
					if (index == prevIndex) index = last;
				}

				if (e.keyCode == 40) {
					if (that.options.liveSearch) index += 1;
					if (index == -1) index = 0;
					if (index != nextPrev && index < next) index = next;
					if (index > last) index = last;
					if (index == prevIndex) index = first;
				}

				$this.data('prevIndex', index);

				if (!that.options.liveSearch) {
					$items.eq(index).focus();
				} else {
					e.preventDefault();
					if (!$this.is('.dropdown-toggle')) {
						$items.removeClass('active');
						$items.eq(index).addClass('active').find('a').focus();
						$this.focus();
					}
				}

			} else if (!$this.is('input')) {
				var keyIndex = [],
					count,
					prevKey;

				$items.each(function () {
					if ($(this).parent().is(':not(.disabled)')) {
						if ($.trim($(this).text().toLowerCase()).substring(0, 1) == keyCodeMap[e.keyCode]) {
							keyIndex.push($(this).parent().index());
						}
					}
				});

				count = $(document).data('keycount');
				count++;
				$(document).data('keycount', count);

				prevKey = $.trim($(':focus').text().toLowerCase()).substring(0, 1);

				if (prevKey != keyCodeMap[e.keyCode]) {
					count = 1;
					$(document).data('keycount', count);
				} else if (count >= keyIndex.length) {
					$(document).data('keycount', 0);
					if (count > keyIndex.length) count = 1;
				}

				$items.eq(keyIndex[count - 1]).focus();
			}

			// Select focused option if "Enter", "Spacebar" or "Tab" (when selectOnTab is true) are pressed inside the menu.
			if ((/(13|32)/.test(e.keyCode.toString(10)) || (/(^9$)/.test(e.keyCode.toString(10)) && that.options.selectOnTab)) && isActive) {
				if (!/(32)/.test(e.keyCode.toString(10))) e.preventDefault();
				if (!that.options.liveSearch) {
					$(':focus').click();
				} else if (!/(32)/.test(e.keyCode.toString(10))) {
					that.$menu.find('.active a').click();
					$this.focus();
				}
				$(document).data('keycount', 0);
			}

			if ((/(^9$|27)/.test(e.keyCode.toString(10)) && isActive && (that.multiple || that.options.liveSearch)) || (/(27)/.test(e.keyCode.toString(10)) && !isActive)) {
				that.$menu.parent().removeClass('open');
				that.$button.focus();
			}
		},

		mobile: function () {
			this.$element.addClass('mobile-device').appendTo(this.$newElement);
			if (this.options.container) this.$menu.hide();
		},

		refresh: function () {
			this.$lis = null;
			this.reloadLi();
			this.render();
			this.setWidth();
			this.setStyle();
			this.checkDisabled();
			this.liHeight();
		},

		update: function () {
			this.reloadLi();
			this.setWidth();
			this.setStyle();
			this.checkDisabled();
			this.liHeight();
		},

		hide: function () {
			this.$newElement.hide();
		},

		show: function () {
			this.$newElement.show();
		},

		remove: function () {
			this.$newElement.remove();
			this.$element.remove();
		}
	};

	// SELECTPICKER PLUGIN DEFINITION
	// ==============================
	function Plugin(option, event) {
		// get the args of the outer function..
		var args = arguments;
		var _option = option,
			option = args[0],
			event = args[1];
		[].shift.apply(args);

		// This fixes a bug in the js implementation on android 2.3 #715
		if (typeof option == 'undefined') {
			option = _option;
		}

		var value;
		var chain = this.each(function () {
			var $this = $(this);
			if ($this.is('select')) {
				var data = $this.data('selectpicker'),
					options = typeof option == 'object' && option;

				if (!data) {
					var config = $.extend({}, Selectpicker.DEFAULTS, $.fn.selectpicker.defaults || {}, $this.data(), options);
					$this.data('selectpicker', (data = new Selectpicker(this, config, event)));
				} else if (options) {
					for (var i in options) {
						if (options.hasOwnProperty(i)) {
							data.options[i] = options[i];
						}
					}
				}

				if (typeof option == 'string') {
					if (data[option] instanceof Function) {
						value = data[option].apply(data, args);
					} else {
						value = data.options[option];
					}
				}
			}
		});

		if (typeof value !== 'undefined') {
			//noinspection JSUnusedAssignment
			return value;
		} else {
			return chain;
		}
	}

	var old = $.fn.selectpicker;
	$.fn.selectpicker = Plugin;
	$.fn.selectpicker.Constructor = Selectpicker;

	// SELECTPICKER NO CONFLICT
	// ========================
	$.fn.selectpicker.noConflict = function () {
		$.fn.selectpicker = old;
		return this;
	};

	$(document)
		.data('keycount', 0)
		.on('keydown', '.bootstrap-select [data-toggle=dropdown], .bootstrap-select [role=menu], .bs-searchbox input', Selectpicker.prototype.keydown)
		.on('focusin.modal', '.bootstrap-select [data-toggle=dropdown], .bootstrap-select [role=menu], .bs-searchbox input', function (e) {
			e.stopPropagation();
		});

	// SELECTPICKER DATA-API
	// =====================
	$(window).on('load.bs.select.data-api', function () {
		$('.selectpicker').each(function () {
			var $selectpicker = $(this);
			Plugin.call($selectpicker, $selectpicker.data());
		})
	});
}(jQuery);
