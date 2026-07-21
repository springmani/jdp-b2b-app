jQuery(document).ready(function ($) {

	/**
	 * Navigation: small-screen menu toggle, dropdown focus, transparent header scroll.
	 */
	(function () {
		var container, button, menu, links, i, len;

		container = document.getElementById('site-navigation');
		if (!container) {
			return;
		}

		button = container.getElementsByClassName('menu-toggle')[0];
		if ('undefined' === typeof button) {
			console.log('Button Not Found');
			return;
		}

		menu = container.getElementsByTagName('ul')[0];

		if ('undefined' === typeof menu) {
			button.style.display = 'none';
			return;
		}

		if (-1 === menu.className.indexOf('nav-menu')) {
			menu.className += ' nav-menu';
		}

		/**
		 * Mobile (below nav breakpoint / 1440px): accordion on top-level items with children.
		 * Flyouts: first tap expands, second tap follows the URL. Mega menu parents: tap toggles panel only (no navigation).
		 */
		var JDPOWER_NAV_DESKTOP_MIN = 1440;

		function jdpowerIsMobileNavAccordion() {
			return typeof window.matchMedia === 'function' && window.matchMedia('(max-width: ' + (JDPOWER_NAV_DESKTOP_MIN - 1) + 'px)').matches;
		}

		function jdpowerSyncMobileNavScrollPanel() {
			var header = document.getElementById('masthead') || document.querySelector('.site-header');
			var branding = header ? header.querySelector('.site-branding') : null;
			var isOpen =
				jdpowerIsMobileNavAccordion() && -1 !== container.className.indexOf('toggled');

			if (!header || !isOpen || !branding) {
				if (header) {
					header.style.removeProperty('--jdp-mobile-nav-scroll-top');
				}
				return;
			}

			header.style.setProperty(
				'--jdp-mobile-nav-scroll-top',
				Math.round(branding.getBoundingClientRect().bottom) + 'px'
			);
		}

		function jdpowerSetNavDrawerOpen(isOpen) {
			if (jdpowerIsMobileNavAccordion()) {
				document.body.classList.toggle('nav-drawer-open', isOpen);
				if (isOpen) {
					window.requestAnimationFrame(function () {
						window.requestAnimationFrame(jdpowerSyncMobileNavScrollPanel);
					});
				} else {
					jdpowerSyncMobileNavScrollPanel();
				}
			} else {
				document.body.classList.remove('nav-drawer-open');
				jdpowerSyncMobileNavScrollPanel();
			}
		}

		button.onclick = function () {
			if (-1 !== container.className.indexOf('toggled')) {
				container.className = container.className.replace(' toggled', '');
				button.setAttribute('aria-expanded', 'false');
				jdpowerResetMobileNavAccordions(menu);
				jdpowerSetNavDrawerOpen(false);
			} else {
				container.className += ' toggled';
				button.setAttribute('aria-expanded', 'true');
				jdpowerSetNavDrawerOpen(true);
			}
		};

		document.addEventListener('click', function (event) {
			var isClickInside = container.contains(event.target);

			if (!isClickInside) {
				container.className = container.className.replace(' toggled', '');
				button.setAttribute('aria-expanded', 'false');
				jdpowerResetMobileNavAccordions(menu);
				jdpowerSetNavDrawerOpen(false);
			}
		});

		function jdpowerResetMobileNavAccordions(primaryMenu) {
			if (!primaryMenu || !primaryMenu.id || primaryMenu.id !== 'primary-menu') {
				return;
			}
			var top = primaryMenu.children;
			for (var j = 0; j < top.length; j++) {
				var item = top[j];
				if (item.tagName !== 'LI') {
					continue;
				}
				item.classList.remove('is-submenu-open');
				var linkTop = item.querySelector(':scope > a');
				if (linkTop) {
					linkTop.removeAttribute('aria-expanded');
				}
			}
		}

		if (menu.id === 'primary-menu') {
			window.addEventListener('resize', function () {
				if (!jdpowerIsMobileNavAccordion()) {
					jdpowerResetMobileNavAccordions(menu);
					jdpowerSetNavDrawerOpen(false);
				} else if (-1 !== container.className.indexOf('toggled')) {
					jdpowerSyncMobileNavScrollPanel();
				}
			});

			menu.addEventListener(
				'click',
				function (e) {
					if (!jdpowerIsMobileNavAccordion() || -1 === container.className.indexOf('toggled')) {
						return;
					}
					var link = e.target.closest('a');
					if (!link || !menu.contains(link)) {
						return;
					}
					var li = link.parentElement;
					if (!li || li.parentElement !== menu) {
						return;
					}
					if (!li.classList.contains('menu-item-has-children')) {
						return;
					}
					if (link !== li.querySelector(':scope > a')) {
						return;
					}

					var open = li.classList.contains('is-submenu-open');
					var isMegaParent = li.classList.contains('mega-menu-parent');
					var isPllParent = li.classList.contains('pll-parent-menu-item');
					if (!open) {
						e.preventDefault();
						var i;
						for (i = 0; i < menu.children.length; i++) {
							var sib = menu.children[i];
							if (sib === li || sib.tagName !== 'LI') {
								continue;
							}
							if (sib.classList.contains('menu-item-has-children')) {
								sib.classList.remove('is-submenu-open');
								var sa = sib.querySelector(':scope > a');
								if (sa) {
									sa.setAttribute('aria-expanded', 'false');
								}
							}
						}
						li.classList.add('is-submenu-open');
						link.setAttribute('aria-expanded', 'true');
						return;
					}
					// Mega / language parents: toggle only (never navigate on the trigger).
					if (isMegaParent || isPllParent) {
						e.preventDefault();
					}
					li.classList.remove('is-submenu-open');
					link.setAttribute('aria-expanded', 'false');
				},
				false
			);
		}

		links = menu.getElementsByTagName('a');

		for (i = 0, len = links.length; i < len; i++) {
			links[i].addEventListener('focus', toggleFocus, true);
			links[i].addEventListener('blur', toggleFocus, true);
		}

		function toggleFocus() {
			var self = this;

			while (-1 === self.className.indexOf('nav-menu')) {
				if ('li' === self.tagName.toLowerCase()) {
					if (-1 !== self.className.indexOf('focus')) {
						self.className = self.className.replace(' focus', '');
					} else {
						self.className += ' focus';
					}
				}

				self = self.parentElement;
			}
		}

		(function () {
			var touchStartFn,
				parentLink = container.querySelectorAll('.menu-item-has-children > a, .page_item_has_children > a');

			if ('ontouchstart' in window) {
				touchStartFn = function (e) {
					var menuItem = this.parentNode;

					// Mobile accordion handles top-level expand/collapse; avoid blocking clicks on those links.
					if (
						window.matchMedia &&
						window.matchMedia('(max-width: ' + (JDPOWER_NAV_DESKTOP_MIN - 1) + 'px)').matches &&
						menuItem.parentNode &&
						menuItem.parentNode.id === 'primary-menu'
					) {
						return;
					}

					if (!menuItem.classList.contains('focus')) {
						e.preventDefault();
						for (i = 0; i < menuItem.parentNode.children.length; ++i) {
							if (menuItem === menuItem.parentNode.children[i]) {
								continue;
							}
							menuItem.parentNode.children[i].classList.remove('focus');
						}
						menuItem.classList.add('focus');
					} else {
						menuItem.classList.remove('focus');
					}
				};

				for (i = 0; i < parentLink.length; ++i) {
					parentLink[i].addEventListener('touchstart', touchStartFn, false);
				}
			}
		})(container);

		(function () {
			var header = document.getElementById('masthead');
			var scrollThreshold = 120;
			// Match _navigation.scss: horizontal nav / mega menu use nav (1440px), not xl.
			var mqDesktop = window.matchMedia('(min-width: ' + JDPOWER_NAV_DESKTOP_MIN + 'px)');
			var transparentNav = document.body.classList.contains('has-transparent-nav');

			if (!header) {
				return;
			}

			function updateHeaderScrollState() {
				if (!mqDesktop.matches) {
					header.classList.remove('site-header--nav-solid');
					header.classList.remove('site-header--scrolled');
					return;
				}
				var scrolled = window.scrollY >= scrollThreshold;
				if (transparentNav) {
					if (scrolled) {
						header.classList.add('site-header--nav-solid');
					} else {
						header.classList.remove('site-header--nav-solid');
					}
				} else {
					header.classList.remove('site-header--nav-solid');
				}
				if (scrolled) {
					header.classList.add('site-header--scrolled');
				} else {
					header.classList.remove('site-header--scrolled');
				}
			}

			updateHeaderScrollState();
			window.addEventListener('scroll', updateHeaderScrollState, { passive: true });
			window.addEventListener('resize', updateHeaderScrollState);
		})();
	})();

	/**
	 * Horizontal card carousel: prev/next, native scroll, drag on pointer.
	 */
	(function () {
		'use strict';

		var DRAG_THRESHOLD = 8;
		var OUTSIDE_EPSILON = 2;

		var BLOCKS = [
			{
				section: '.related-products-block',
				card: '.related-products-block__card',
				prev: '.related-products-block__nav-btn--prev',
				next: '.related-products-block__nav-btn--next',
			},
			{
				section: '.featured-insights-block',
				card: '.featured-insights-block__card',
				prev: '.featured-insights-block__nav-btn--prev',
				next: '.featured-insights-block__nav-btn--next',
			},
		];

		function getStepPx(track, cardSelector) {
			var card = track.querySelector(cardSelector);
			if (!card) {
				return Math.max(200, Math.floor(track.clientWidth * 0.85));
			}
			var style = window.getComputedStyle(track);
			var gap = parseFloat(style.gap || style.columnGap) || 0;
			return Math.round(card.getBoundingClientRect().width + gap);
		}

		function initTrack(track, cfg) {
			var block = track.closest(cfg.section);
			if (!block) {
				return;
			}

			var prev = block.querySelector(cfg.prev);
			var next = block.querySelector(cfg.next);
			var dragMoved = false;
			var dragActive = false;

			function getContainerRight() {
				var container = block.querySelector('.container');
				if (!container) {
					return null;
				}
				return container.getBoundingClientRect().right;
			}

			function isCardOutsideContainer(cardRect, containerRight) {
				if (null === containerRight) {
					return false;
				}
				if (cardRect.left >= containerRight - OUTSIDE_EPSILON) {
					return true;
				}
				return (
					cardRect.right > containerRight + OUTSIDE_EPSILON &&
					cardRect.left < containerRight
				);
			}

			function updateCardOpacity() {
				var containerRight = getContainerRight();
				if (null === containerRight) {
					return;
				}
				track.querySelectorAll(cfg.card).forEach(function (card) {
					var outside = isCardOutsideContainer(card.getBoundingClientRect(), containerRight);
					card.classList.toggle('is-carousel-outside', outside);
				});
			}

			function updateNavState() {
				if (!prev || !next) {
					return;
				}
				var maxScroll = track.scrollWidth - track.clientWidth;
				var epsilon = 2;
				var sl = track.scrollLeft;
				var atStart = sl <= epsilon;
				var atEnd = maxScroll <= epsilon || sl >= maxScroll - epsilon;
				prev.disabled = atStart;
				next.disabled = atEnd;
				prev.setAttribute('aria-disabled', atStart ? 'true' : 'false');
				next.setAttribute('aria-disabled', atEnd ? 'true' : 'false');
			}

			function onScrollUpdateNav() {
				if (!dragActive) {
					updateNavState();
				}
				updateCardOpacity();
			}

			function step(dir) {
				var amount = getStepPx(track, cfg.card);
				track.scrollBy({ left: dir * amount, behavior: 'smooth' });
			}

			if (prev) {
				prev.addEventListener('click', function () {
					if (prev.disabled) {
						return;
					}
					step(-1);
				});
			}
			if (next) {
				next.addEventListener('click', function () {
					if (next.disabled) {
						return;
					}
					step(1);
				});
			}

			track.addEventListener('scroll', onScrollUpdateNav, { passive: true });
			window.addEventListener('resize', function () {
				updateNavState();
				updateCardOpacity();
			}, { passive: true });
			if (typeof ResizeObserver !== 'undefined') {
				var ro = new ResizeObserver(function () {
					updateNavState();
					updateCardOpacity();
				});
				ro.observe(track);
			}
			window.addEventListener('load', function () {
				updateNavState();
				updateCardOpacity();
			}, { passive: true });
			requestAnimationFrame(function () {
				requestAnimationFrame(function () {
					updateNavState();
					updateCardOpacity();
				});
			});
			updateNavState();
			updateCardOpacity();

			var dragRaf = 0;
			var pendingPageX = 0;
			var dragState = {
				startPageX: 0,
				startScrollLeft: 0,
			};

			function applyDragScroll() {
				dragRaf = 0;
				var dx = pendingPageX - dragState.startPageX;
				if (Math.abs(dx) > DRAG_THRESHOLD) {
					dragMoved = true;
				}
				track.scrollLeft = dragState.startScrollLeft - dx;
				updateCardOpacity();
			}

			function onMouseMove(e) {
				pendingPageX = e.pageX;
				if (Math.abs(pendingPageX - dragState.startPageX) > DRAG_THRESHOLD && e.cancelable) {
					e.preventDefault();
				}
				if (!dragRaf) {
					dragRaf = window.requestAnimationFrame(applyDragScroll);
				}
			}

			function onMouseUp() {
				window.removeEventListener('mousemove', onMouseMove);
				window.removeEventListener('mouseup', onMouseUp);
				if (dragRaf) {
					window.cancelAnimationFrame(dragRaf);
					dragRaf = 0;
					applyDragScroll();
				}
				dragActive = false;
				track.classList.remove('is-dragging');
				updateNavState();
				updateCardOpacity();
			}

			track.addEventListener(
				'mousedown',
				function (e) {
					if (e.button !== 0) {
						return;
					}
					if (!track.contains(e.target)) {
						return;
					}
					dragMoved = false;
					dragActive = true;
					pendingPageX = e.pageX;
					dragState.startPageX = e.pageX;
					dragState.startScrollLeft = track.scrollLeft;
					track.classList.add('is-dragging');
					window.addEventListener('mousemove', onMouseMove, { passive: false });
					window.addEventListener('mouseup', onMouseUp);
				},
				true
			);

			track.addEventListener(
				'click',
				function (e) {
					if (!dragMoved) {
						return;
					}
					e.preventDefault();
					e.stopPropagation();
					dragMoved = false;
				},
				true
			);
		}

		function initCarousels() {
			document.querySelectorAll('[data-jdpower-carousel]').forEach(function (track) {
				for (var i = 0; i < BLOCKS.length; i++) {
					if (track.closest(BLOCKS[i].section)) {
						initTrack(track, BLOCKS[i]);
						return;
					}
				}
			});
		}

		initCarousels();
	})();

	/**
	 * Featured Solutions: custom region dropdown + grid filter.
	 * Programmatic region (e.g. geo popup):
	 *   window.jdpowerFeaturedSolutions.setRegion( gridDomId, regionSlug );
	 */
	(function () {
		'use strict';

		function parseRegionSlugs(card) {
			var raw = card.getAttribute('data-region-slugs') || '';
			return raw.split(',').map(function (s) {
				return s.trim();
			}).filter(Boolean);
		}

		function findRegionOption(list, value) {
			var options = list.querySelectorAll('[role="option"]');
			for (var i = 0; i < options.length; i++) {
				if ((options[i].getAttribute('data-value') || '') === value) {
					return options[i];
				}
			}
			return null;
		}

		function filterGrid(gridId, regionSlug) {
			var grid = document.getElementById(gridId);
			if (!grid) {
				return;
			}
			var slug = regionSlug ? String(regionSlug) : '';
			grid.querySelectorAll('[data-featured-solutions-card]').forEach(function (card) {
				if (card.getAttribute('data-region-all') === '1') {
					card.hidden = false;
					return;
				}
				if (!slug) {
					card.hidden = false;
					return;
				}
				var slugs = parseRegionSlugs(card);
				card.hidden = slugs.indexOf(slug) === -1;
			});
		}

		function syncFromSelect(root, select, list, labelEl) {
			var gridId = root.getAttribute('data-featured-solutions-grid');
			var v = select.value || '';
			var opt = findRegionOption(list, v);
			if (opt) {
				labelEl.textContent = opt.textContent.trim();
			}
			list.querySelectorAll('[role="option"]').forEach(function (o) {
				var ov = o.getAttribute('data-value') || '';
				o.setAttribute('aria-selected', ov === v ? 'true' : 'false');
			});
			if (gridId) {
				filterGrid(gridId, v);
			}
		}

		function closeAllRegionDropdowns() {
			document.querySelectorAll('.featured-solutions-block__region-combobox').forEach(function (root) {
				var list = root.querySelector('.featured-solutions-block__region-list');
				var trigger = root.querySelector('button.featured-solutions-block__region-trigger');
				if (!list || list.hidden) {
					return;
				}
				list.hidden = true;
				if (trigger) {
					trigger.setAttribute('aria-expanded', 'false');
				}
				root.classList.remove('is-open');
			});
		}

		function initCombobox(root) {
			var select = root.querySelector('.featured-solutions-block__region-select-hidden');
			var trigger = root.querySelector('button.featured-solutions-block__region-trigger');
			var list = root.querySelector('.featured-solutions-block__region-list');
			var labelEl = root.querySelector('[data-featured-solutions-region-label]');
			if (!select || !trigger || !list || !labelEl) {
				return;
			}

			function emitChange() {
				select.dispatchEvent(new Event('change', { bubbles: true }));
			}

			select.addEventListener('change', function () {
				syncFromSelect(root, select, list, labelEl);
			});

			trigger.addEventListener('click', function (e) {
				e.stopPropagation();
				var opening = list.hidden;
				if (opening) {
					closeAllRegionDropdowns();
				}
				list.hidden = !opening;
				trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
				root.classList.toggle('is-open', opening);
			});

			list.querySelectorAll('[role="option"]').forEach(function (opt) {
				opt.addEventListener('click', function () {
					var v = opt.getAttribute('data-value') || '';
					select.value = v;
					emitChange();
					list.hidden = true;
					trigger.setAttribute('aria-expanded', 'false');
					root.classList.remove('is-open');
					trigger.focus();
				});
			});

			root.addEventListener('click', function (e) {
				e.stopPropagation();
			});

			emitChange();
		}

		document.addEventListener('click', closeAllRegionDropdowns);

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') {
				return;
			}
			var open = document.querySelector('.featured-solutions-block__region-combobox.is-open');
			if (open) {
				closeAllRegionDropdowns();
				var tr = open.querySelector('button.featured-solutions-block__region-trigger');
				if (tr) {
					tr.focus();
				}
			}
		});

		document.querySelectorAll('.featured-solutions-block__region-combobox').forEach(initCombobox);

		window.jdpowerFeaturedSolutions = window.jdpowerFeaturedSolutions || {};
		window.jdpowerFeaturedSolutions.setRegion = function (gridDomId, regionSlug) {
			var sel = document.getElementById(gridDomId + '-region');
			if (!sel) {
				return;
			}
			sel.value = regionSlug === undefined || regionSlug === null ? '' : String(regionSlug);
			sel.dispatchEvent(new Event('change', { bubbles: true }));
		};
	})();


	/**
	 * Logins block: region dropdown + client-side search filter.
	 */
	(function () {
		'use strict';

		function findRegionOption(list, value) {
			var options = list.querySelectorAll('[role="option"]');
			for (var i = 0; i < options.length; i++) {
				if ((options[i].getAttribute('data-value') || '') === value) {
					return options[i];
				}
			}
			return null;
		}

		function applyLoginsFilters(root) {
			var select = root.querySelector('.logins-block__region-select-hidden');
			var searchInput = root.querySelector('[data-logins-search]');
			var regionSlug = select ? (select.value || '') : '';
			var query = searchInput ? searchInput.value.trim().toLowerCase() : '';

			root.querySelectorAll('[data-logins-region]').forEach(function (section) {
				var sectionSlug = section.getAttribute('data-logins-region') || '';
				var sectionMatchesRegion = !regionSlug || sectionSlug === regionSlug;
				var hasVisibleItems = false;

				section.querySelectorAll('[data-logins-item]').forEach(function (item) {
					var haystack = (item.getAttribute('data-logins-search') || '').toLowerCase();
					var matchesSearch = !query || haystack.indexOf(query) !== -1;
					var show = sectionMatchesRegion && matchesSearch;
					item.hidden = !show;
					if (show) {
						hasVisibleItems = true;
					}
				});

				section.hidden = !hasVisibleItems;
			});
		}

		function closeAllLoginsDropdowns() {
			document.querySelectorAll('.logins-block__region-combobox.is-open').forEach(function (root) {
				var list = root.querySelector('.logins-block__region-list');
				var trigger = root.querySelector('button.logins-block__region-trigger');
				if (!list || list.hidden) {
					return;
				}
				list.hidden = true;
				if (trigger) {
					trigger.setAttribute('aria-expanded', 'false');
				}
				root.classList.remove('is-open');
			});
		}

		function syncRegionCombobox(combobox) {
			var select = combobox.querySelector('.logins-block__region-select-hidden');
			var list = combobox.querySelector('.logins-block__region-list');
			var labelEl = combobox.querySelector('[data-logins-region-label]');
			if (!select || !list || !labelEl) {
				return;
			}

			var value = select.value || '';
			var opt = findRegionOption(list, value);
			if (opt) {
				labelEl.textContent = opt.textContent.trim();
			}

			list.querySelectorAll('[role="option"]').forEach(function (option) {
				var optionValue = option.getAttribute('data-value') || '';
				option.setAttribute('aria-selected', optionValue === value ? 'true' : 'false');
			});
		}

		function initRegionCombobox(combobox) {
			var blockRoot = combobox.closest('[data-logins-root]');
			var select = combobox.querySelector('.logins-block__region-select-hidden');
			var trigger = combobox.querySelector('button.logins-block__region-trigger');
			var list = combobox.querySelector('.logins-block__region-list');
			if (!blockRoot || !select || !trigger || !list) {
				return;
			}

			select.addEventListener('change', function () {
				syncRegionCombobox(combobox);
				applyLoginsFilters(blockRoot);
			});

			trigger.addEventListener('click', function (e) {
				e.stopPropagation();
				var opening = list.hidden;
				if (opening) {
					closeAllLoginsDropdowns();
				}
				list.hidden = !opening;
				trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
				combobox.classList.toggle('is-open', opening);
			});

			list.querySelectorAll('[role="option"]').forEach(function (option) {
				option.addEventListener('click', function () {
					select.value = option.getAttribute('data-value') || '';
					select.dispatchEvent(new Event('change', { bubbles: true }));
					list.hidden = true;
					trigger.setAttribute('aria-expanded', 'false');
					combobox.classList.remove('is-open');
					trigger.focus();
				});
			});

			combobox.addEventListener('click', function (e) {
				e.stopPropagation();
			});

			syncRegionCombobox(combobox);
		}

		function initLoginsBlock(root) {
			if (root.dataset.loginsInit) {
				return;
			}
			root.dataset.loginsInit = '1';

			var searchInput = root.querySelector('[data-logins-search]');
			if (searchInput) {
				searchInput.addEventListener('input', function () {
					applyLoginsFilters(root);
				});
			}

			root.querySelectorAll('[data-logins-region-combobox]').forEach(initRegionCombobox);
			applyLoginsFilters(root);
		}

		document.addEventListener('click', closeAllLoginsDropdowns);

		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') {
				return;
			}
			var open = document.querySelector('.logins-block__region-combobox.is-open');
			if (open) {
				closeAllLoginsDropdowns();
				var trigger = open.querySelector('button.logins-block__region-trigger');
				if (trigger) {
					trigger.focus();
				}
			}
		});

		document.querySelectorAll('[data-logins-root]').forEach(initLoginsBlock);
	})();


	/**
	 * CTA Banner: native dialog for form embed (modal display mode).
	 */
	(function () {
		document.querySelectorAll('button.cta-banner__modal-trigger').forEach(function (btn) {
			var dialogId = btn.getAttribute('aria-controls');
			if (!dialogId) {
				return;
			}
			var dialog = document.getElementById(dialogId);
			if (!dialog || typeof dialog.showModal !== 'function') {
				return;
			}

			btn.addEventListener('click', function () {
				dialog.showModal();
				btn.setAttribute('aria-expanded', 'true');
			});

			dialog.querySelectorAll('.cta-banner__dialog-close').forEach(function (closeBtn) {
				closeBtn.addEventListener('click', function () {
					dialog.close();
				});
			});

			dialog.addEventListener('click', function (e) {
				if (e.target === dialog) {
					dialog.close();
				}
			});

			dialog.addEventListener('close', function () {
				btn.setAttribute('aria-expanded', 'false');
				btn.focus();
			});
		});
	})();

	/**
	 * Leaders block: card (role=button) opens native biography dialog — same behavior as CTA modal, no full-card <button>.
	 */
	(function () {
		document.querySelectorAll('dialog.leaders-block__dialog').forEach(function (dialog) {
			if (dialog.dataset.jdpowerLeadersDialogInit) {
				return;
			}
			dialog.dataset.jdpowerLeadersDialogInit = '1';

			dialog.querySelectorAll('.leaders-block__dialog-close').forEach(function (closeBtn) {
				closeBtn.addEventListener('click', function () {
					dialog.close();
				});
			});

			dialog.addEventListener('click', function (e) {
				if (e.target === dialog) {
					dialog.close();
				}
			});
		});

		document.querySelectorAll('section.leaders-block').forEach(function (block) {
			if (block.dataset.jdpowerLeadersBlockInit) {
				return;
			}
			block.dataset.jdpowerLeadersBlockInit = '1';

			function openDialogForCard(card) {
				var id = card.getAttribute('aria-controls');
				if (!id) {
					return;
				}
				var dialog = document.getElementById(id);
				if (!dialog || typeof dialog.showModal !== 'function') {
					return;
				}
				if (dialog.open) {
					return;
				}
				dialog.showModal();
				card.setAttribute('aria-expanded', 'true');
				var onClose = function () {
					card.setAttribute('aria-expanded', 'false');
					card.focus();
					dialog.removeEventListener('close', onClose);
				};
				dialog.addEventListener('close', onClose);
			}

			block.addEventListener('click', function (e) {
				var card = e.target.closest('.leaders-block__card');
				if (!card || !block.contains(card)) {
					return;
				}
				openDialogForCard(card);
			});

			block.addEventListener('keydown', function (e) {
				if (e.key !== 'Enter' && e.key !== ' ') {
					return;
				}
				var t = e.target;
				if (!t || !t.classList || !t.classList.contains('leaders-block__card')) {
					return;
				}
				if (!block.contains(t)) {
					return;
				}
				e.preventDefault();
				openDialogForCard(t);
			});
		});
	})();

	/**
	 * Stats block: count-up from data-target (see sass/animations/_animations.scss).
	 */
	(function () {
		function animateStat(el) {
			var targetStr = el.dataset.target;
			if (!targetStr) {
				return;
			}
			var target = parseFloat(targetStr, 10);
			if (!isFinite(target)) {
				return;
			}
			var duration = 2000;
			var startTime = performance.now();
			var decimals = targetStr.indexOf('.') !== -1 ? targetStr.split('.')[1].length : 0;

			function update(now) {
				var elapsed = now - startTime;
				var progress = Math.min(elapsed / duration, 1);
				var ease = 1 - Math.pow(2, -10 * progress);
				var current = ease * target;
				el.textContent = current.toFixed(decimals);
				if (progress < 1) {
					requestAnimationFrame(update);
				} else {
					var label = el.getAttribute('aria-label');
					if (label) {
						el.textContent = label;
					}
				}
			}
			requestAnimationFrame(update);
		}

		var stats = document.querySelectorAll('.stats-block--animate-stats .stats-block__value--count-up');
		if (!stats.length) {
			return;
		}
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			stats.forEach(function (el) {
				var label = el.getAttribute('aria-label');
				if (label) {
					el.textContent = label;
				}
			});
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					var el = entry.target;
					if (el.classList.contains('stats-block__value--count-up')) {
						animateStat(el);
						observer.unobserve(el);
					}
				});
			},
			{ threshold: 0.1 }
		);

		stats.forEach(function (el) {
			var targetStr = el.dataset.target;
			if (targetStr) {
				var decimals = targetStr.indexOf('.') !== -1 ? targetStr.split('.')[1].length : 0;
				el.textContent = (0).toFixed(decimals);
			}
			observer.observe(el);
		});
	})();

	/**
	 * Scroll reveal: `.jdp-animate-view-fade-in-up` fades the whole element in and nudges it up
	 * when it enters the viewport. Stagger is per column within a row (not global card index).
	 */
	(function () {
		var STAGGER_MS = 110;
		var BP_SM = 576;
		var BP_XL = 1200;
		var BP_LG = 992;

		function revealContainerColumns(container) {
			if (!container || !container.classList) {
				return 1;
			}

			var cn = container.className || '';
			var w = window.innerWidth;

			if (cn.indexOf('__grid') !== -1) {
				var configured = 3;
				if (cn.indexOf('__grid--cols-4') !== -1) {
					configured = 4;
				} else if (cn.indexOf('__grid--cols-3') !== -1) {
					configured = 3;
				} else if (cn.indexOf('__grid--cols-2') !== -1) {
					configured = 2;
				} else if (cn.indexOf('__grid--cols-1') !== -1) {
					return 1;
				}

				if (w < BP_SM) {
					return 1;
				}
				if (w < BP_XL && configured >= 3) {
					return 2;
				}
				if (configured === 2 && w < BP_SM) {
					return 1;
				}
				return configured;
			}

			if (container.classList.contains('row')) {
				var first = container.children[0];
				var childCls = first && first.className ? first.className : '';
				if (childCls.indexOf('col-lg-3') !== -1) {
					return w >= BP_LG ? 4 : 1;
				}
				if (childCls.indexOf('col-lg-4') !== -1) {
					return w >= BP_LG ? 3 : 1;
				}
				if (childCls.indexOf('col-lg-6') !== -1) {
					return w >= BP_LG ? 2 : 1;
				}
			}

			return 1;
		}

		function revealSiblingIndex(container, el) {
			if (!container) {
				return 0;
			}
			var children = container.children;
			for (var i = 0; i < children.length; i++) {
				if (children[i] === el) {
					return i;
				}
			}
			return 0;
		}

		function revealStaggerDelay(el) {
			var container = el.parentElement;
			if (!container) {
				return 0;
			}
			var cols = revealContainerColumns(container);
			if (cols <= 1) {
				return 0;
			}
			var index = revealSiblingIndex(container, el);
			return (index % cols) * STAGGER_MS;
		}

		var els = document.querySelectorAll('.jdp-animate-view-fade-in-up');
		if (!els.length) {
			return;
		}
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			els.forEach(function (el) {
				el.classList.add('is-inview');
			});
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) {
						return;
					}
					var el = entry.target;
					el.style.setProperty('--jdp-reveal-delay', revealStaggerDelay(el) + 'ms');
					el.classList.add('is-inview');
					observer.unobserve(el);
				});
			},
			{ root: null, rootMargin: '0px 0px -6% 0px', threshold: 0.08 }
		);

		els.forEach(function (el) {
			observer.observe(el);
		});
	})();

});

/**
 * Regional mismatch popup: dialog is printed in wp_footer before scripts.
 */
(function () {
	function initRegionalPopup() {
		var dialog = document.getElementById('jdpower-regional-popup');
		if (!dialog || typeof dialog.showModal !== 'function') {
			return;
		}
		if (dialog.dataset.jdpowerRegionalPopupInit) {
			return;
		}
		dialog.dataset.jdpowerRegionalPopupInit = '1';

		dialog.querySelectorAll('.regional-popup__close, .regional-popup__remain').forEach(function (closeBtn) {
			closeBtn.addEventListener('click', function () {
				dialog.close();
			});
		});

		dialog.addEventListener('click', function (e) {
			if (e.target === dialog) {
				dialog.close();
			}
		});

		if (!dialog.open) {
			dialog.showModal();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initRegionalPopup);
	} else {
		initRegionalPopup();
	}
})();
