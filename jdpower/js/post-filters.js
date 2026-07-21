/**
 * Insight Center / Product Finder: AJAX filters, URL sync, load more.
 */
(function ($) {
	'use strict';

	function getRoot() {
		return $('[data-post-filters]');
	}

	/**
	 * Search field uses `keyword` (same as the Search hub; not core `s`) so shareable
	 * URLs do not 404 on Pages. Still read legacy `?pf_s=` / `?s=` when syncing.
	 */
	function searchField($form) {
		return $form.find('[name="keyword"], [name="pf_s"], [name="s"]').first();
	}

	function searchTermFromParams(params) {
		if (params.has('keyword')) {
			return params.get('keyword') || '';
		}
		if (params.has('pf_s')) {
			return params.get('pf_s') || '';
		}
		if (params.has('s')) {
			return params.get('s') || '';
		}
		return '';
	}

	function applyViewToRoot($root, mode) {
		var m = mode === 'list' ? 'list' : 'grid';
		$root.attr('data-view', m);
		var $hv = $root.find('[name="pf_view"]');
		if ($hv.length) {
			$hv.val(m);
		}
		if (m === 'list') {
			$root.addClass('post-filters--view-list');
		} else {
			$root.removeClass('post-filters--view-list');
		}
		var $toggle = $root.find('[data-post-filters-view-toggle]');
		if ($toggle.length) {
			var i18n = typeof jdpowerPostFilters !== 'undefined' && jdpowerPostFilters.i18n ? jdpowerPostFilters.i18n : {};
			if (m === 'list') {
				$toggle.attr('aria-label', i18n.switchToGridLayout || 'Switch to grid layout');
			} else {
				$toggle.attr('aria-label', i18n.switchToListLayout || 'Switch to list layout');
			}
		}
	}

	function syncViewFromUrl($root) {
		var params = new URLSearchParams(window.location.search);
		var v = params.get('pf_view') === 'list' ? 'list' : 'grid';
		applyViewToRoot($root, v);
	}

	function splitCommaSlugs(val) {
		if (!val) {
			return [];
		}
		return val.split(',').map(function (s) {
			return s.trim();
		}).filter(function (s) {
			return s;
		});
	}

	/**
	 * Sidebar term links are server-rendered; after AJAX only the grid updates.
	 * Sync .is-active and the × remove control from current form state.
	 */
	function insightHasSearch($root) {
		var $form = $root.find('.post-filters__search-form');
		return $.trim(String(searchField($form).val() || '')) !== '';
	}

	/**
	 * Insight Center: show Relevancy only during keyword search (matches PHP display choices).
	 */
	function syncInsightSortChoicesForSearch($root, hasSearch) {
		if ($root.data('context') !== 'insight_center') {
			return;
		}
		var $sel = $root.find('.post-filters__sort-select');
		var $menu = $root.find('.post-filters__sort-menu');
		var $sizer = $root.find('.post-filters__sort-sizer');
		if (!$sel.length) {
			return;
		}

		var $revOpt = $sel.find('option[value="relevancy"]');
		if (hasSearch && !$revOpt.length) {
			$sel.prepend($('<option></option>').attr('value', 'relevancy').text('Relevancy'));
			$menu.prepend(
				$('<li></li>')
					.addClass('post-filters__sort-option')
					.attr({
						role: 'option',
						tabindex: '-1',
						'data-value': 'relevancy',
						'aria-selected': 'false'
					})
					.text('Relevancy')
			);
			if ($sizer.length) {
				$sizer.prepend($('<span></span>').addClass('post-filters__sort-sizer-line').text('Relevancy'));
			}
		} else if (!hasSearch && $revOpt.length) {
			$revOpt.remove();
			$menu.find('.post-filters__sort-option[data-value="relevancy"]').remove();
			$sizer.find('.post-filters__sort-sizer-line').filter(function () {
				return $.trim($(this).text()) === 'Relevancy';
			}).remove();
			if (String($sel.val()) === 'relevancy') {
				$sel.val('newest');
			}
		}
	}

	/**
	 * Entering a keyword search defaults sort to Relevancy; clearing search leaves Newest.
	 */
	function applyInsightSortDefaultForSearchTransition($root, hasSearch) {
		if ($root.data('context') !== 'insight_center') {
			return;
		}
		var $sel = $root.find('.post-filters__sort-select');
		if (!$sel.length) {
			return;
		}
		syncInsightSortChoicesForSearch($root, hasSearch);
		var hadSearch = $root.data('pfHadSearch') === true;
		if (hasSearch && !hadSearch) {
			$sel.val('relevancy');
		} else if (!hasSearch && hadSearch && String($sel.val()) === 'relevancy') {
			$sel.val('newest');
		}
		$root.data('pfHadSearch', hasSearch);
		syncSortDropdownFromSelect($root);
	}

	function syncSortDropdownFromSelect($root) {
		var $sel = $root.find('.post-filters__sort-select');
		var $toggleLabel = $root.find('.post-filters__sort-toggle-label');
		if (!$sel.length || !$toggleLabel.length) {
			return;
		}
		var hasSearch = insightHasSearch($root);
		var val = $sel.val() || (hasSearch ? 'relevancy' : 'newest');
		var text = '';
		$sel.find('option').each(function () {
			if (String($(this).val()) === String(val)) {
				text = $(this).text();
				return false;
			}
		});
		if (text) {
			$toggleLabel.text(text);
		}
		$root.find('.post-filters__sort-option').each(function () {
			var $opt = $(this);
			var isSel = String($opt.data('value')) === String(val);
			$opt.attr('aria-selected', isSel ? 'true' : 'false');
		});
	}

	function closeSortDropdown($root, skipFocus) {
		var $wrap = $root.find('.post-filters__sort-dropdown');
		if (!$wrap.length || !$wrap.hasClass('is-open')) {
			return;
		}
		$wrap.removeClass('is-open');
		$root.find('.post-filters__sort-menu').attr('hidden', 'hidden');
		$root.find('.post-filters__sort-toggle').attr('aria-expanded', 'false');
		if (!skipFocus) {
			$root.find('.post-filters__sort-toggle').focus();
		}
	}

	function openSortDropdown($root) {
		var $wrap = $root.find('.post-filters__sort-dropdown');
		if (!$wrap.length) {
			return;
		}
		getRoot().each(function () {
			var $r = $(this);
			if ($r[0] !== $root[0] && $r.find('.post-filters__sort-dropdown').hasClass('is-open')) {
				closeSortDropdown($r, true);
			}
		});
		$wrap.addClass('is-open');
		$root.find('.post-filters__sort-menu').removeAttr('hidden');
		$root.find('.post-filters__sort-toggle').attr('aria-expanded', 'true');
		var $selected = $root.find('.post-filters__sort-option[aria-selected="true"]');
		var $toFocus = $selected.length ? $selected : $root.find('.post-filters__sort-option').first();
		if ($toFocus.length) {
			$toFocus.focus();
		}
	}

	/**
	 * Size the sort control to the widest option label (matches toggle typography via CSS).
	 */
	function syncSortDropdownMinWidth($root) {
		var $sizer = $root.find('.post-filters__sort-sizer');
		var $dd = $root.find('.post-filters__sort-dropdown');
		if (!$sizer.length || !$dd.length) {
			return;
		}
		var maxW = 0;
		$sizer.find('.post-filters__sort-sizer-line').each(function () {
			var w = $(this).outerWidth();
			if (w > maxW) {
				maxW = w;
			}
		});
		var $toggle = $root.find('.post-filters__sort-toggle');
		var $chev = $root.find('.post-filters__sort-toggle-chevron');
		var chevronW = $chev.length ? $chev.outerWidth(true) : 14;
		var pl = parseFloat($toggle.css('padding-left')) || 0;
		var pr = parseFloat($toggle.css('padding-right')) || 0;
		var gap = parseFloat($toggle.css('column-gap')) || parseFloat($toggle.css('gap')) || 6;
		$dd.css('min-width', Math.ceil(maxW + chevronW + gap + pl + pr) + 'px');
	}

	/**
	 * Refresh sidebar term <a href> from AJAX (matches PHP toggle URLs). Without this, hrefs stay at first paint.
	 *
	 * @param {Array<{key: string, value: string, href: string}>} list
	 */
	function syncSidebarTermHrefs($root, list) {
		if (!Array.isArray(list) || !list.length) {
			return;
		}
		var map = {};
		list.forEach(function (item) {
			if (!item || item.key === undefined || item.value === undefined || !item.href) {
				return;
			}
			map[String(item.key) + '|' + String(item.value)] = item.href;
		});
		$root.find('.post-filters__term-link').each(function () {
			var $a = $(this);
			var key = $a.attr('data-filter-key');
			var val = String($a.attr('data-filter-value') || '');
			var href = map[key + '|' + val];
			if (href) {
				$a.attr('href', href);
			}
		});
	}

	function syncSidebarTermLinks($root) {
		var ctx = $root.data('context');
		var $form = $root.find('.post-filters__search-form');
		if (!$form.length) {
			return;
		}

		var pfPt = String($form.find('[name="pf_pt"]').val() || '');
		var region = String($form.find('[name="region"]').val() || '');
		var industries = splitCommaSlugs($form.find('[name="post_industry"]').val());
		var topics = splitCommaSlugs($form.find('[name="post_topic"]').val());
		var insightSeg = splitCommaSlugs($form.find('[name="post_segment"]').val());
		var prodInd = splitCommaSlugs($form.find('[name="product_industry"]').val());
		var prodSeg = splitCommaSlugs($form.find('[name="product_segment"]').val());

		$root.find('.post-filters__term-link').each(function () {
			var $a = $(this);
			var key = $a.attr('data-filter-key');
			var val = String($a.attr('data-filter-value') || '');
			var active = false;

			if (ctx === 'insight_center') {
				if (key === 'pf_pt') {
					active = val !== '' && pfPt === val;
				} else if (key === 'post_industry') {
					active = industries.indexOf(val) !== -1;
				} else if (key === 'post_topic') {
					active = topics.indexOf(val) !== -1;
				} else if (key === 'post_segment') {
					active = insightSeg.indexOf(val) !== -1;
				}
			} else if (ctx === 'product_finder') {
				if (key === 'region') {
					active = val !== '' && region === val;
				} else if (key === 'product_industry') {
					active = prodInd.indexOf(val) !== -1;
				} else if (key === 'product_segment') {
					active = prodSeg.indexOf(val) !== -1;
				}
			}

			$a.toggleClass('is-active', active);
			var $rm = $a.find('.post-filters__remove');
			if (active) {
				if (!$rm.length) {
					$a.append('<span class="post-filters__remove" aria-hidden="true">×</span>');
				}
			} else {
				$rm.remove();
			}
		});
	}

	function productFinderDefaultRegionSlug() {
		return typeof jdpowerPostFilters !== 'undefined' && jdpowerPostFilters.defaultRegionSlug
			? String(jdpowerPostFilters.defaultRegionSlug)
			: '';
	}

	function collectPayload($root, paged, append) {
		var ctx = $root.data('context');
		var payload = {
			action: 'jdpower_post_filters_fetch',
			nonce: jdpowerPostFilters.nonce,
			context: ctx,
			pf_paged: paged || 1,
			append: append ? 1 : 0
		};

		if (jdpowerPostFilters.lang) {
			payload.lang = jdpowerPostFilters.lang;
		}

		var $sort = $root.find('[name="pf_sort"]');
		var hasSearch =
			ctx === 'insight_center'
				? insightHasSearch($root)
				: $.trim(String(searchField($root.find('.post-filters__search-form')).val() || '')) !== '';
		var sortDefault = hasSearch && ctx === 'insight_center' ? 'relevancy' : 'newest';
		var sortVal = $sort.length ? $sort.val() : sortDefault;
		payload.pf_sort = sortVal || sortDefault;

		var viewMode = $root.attr('data-view') || 'grid';
		var $pfv = $root.find('[name="pf_view"]');
		if ($pfv.length) {
			viewMode = $pfv.val() || viewMode;
		}
		payload.pf_view = viewMode === 'list' ? 'list' : 'grid';

		if (ctx === 'insight_center') {
			var $form = $root.find('.post-filters__search-form');
			payload.s = searchField($form).val() || '';
			payload.pf_pt = $form.find('[name="pf_pt"]').val() || '';
			payload.post_industry = $form.find('[name="post_industry"]').val() || '';
			payload.post_topic = $form.find('[name="post_topic"]').val() || '';
			payload.post_segment = $form.find('[name="post_segment"]').val() || '';
		} else {
			var $formP = $root.find('.post-filters__search-form');
			payload.s = searchField($formP).val() || '';
			payload.region = $formP.find('[name="region"]').val() || '';
			payload.product_industry = $formP.find('[name="product_industry"]').val() || '';
			payload.product_segment = $formP.find('[name="product_segment"]').val() || '';
			if (jdpowerPostFilters.finderPageId) {
				payload.finder_page_id = jdpowerPostFilters.finderPageId;
			}
		}

		return payload;
	}

	/**
	 * @param {JQuery} $root
	 * @param {string} [searchString]  Optional `location.search` (e.g. `?a=1&b=2`). Use when syncing from a clicked link URL so we do not rely on replaceState + location.search in the same tick.
	 */
	function syncHiddenFromUrl($root, searchString) {
		var raw = typeof searchString === 'string' ? searchString : window.location.search;
		var params = new URLSearchParams(raw || '');
		var $form = $root.find('.post-filters__search-form');
		var ctx = $root.data('context');

		if (ctx === 'insight_center') {
			searchField($form).val(searchTermFromParams(params));
			var $pfpt = $form.find('[name="pf_pt"]');
			if ($pfpt.length) {
				$pfpt.val(params.has('pf_pt') ? params.get('pf_pt') : '');
			} else if (params.has('pf_pt')) {
				$('<input type="hidden" name="pf_pt" />')
					.val(params.get('pf_pt'))
					.appendTo($form);
			}
			var hi = $form.find('[name="post_industry"]');
			if (hi.length) {
				hi.val(params.has('post_industry') ? params.get('post_industry') : '');
			} else if (params.has('post_industry')) {
				$('<input type="hidden" name="post_industry" />')
					.val(params.get('post_industry'))
					.appendTo($form);
			}
			var ht = $form.find('[name="post_topic"]');
			if (ht.length) {
				ht.val(params.has('post_topic') ? params.get('post_topic') : '');
			} else if (params.has('post_topic')) {
				$('<input type="hidden" name="post_topic" />')
					.val(params.get('post_topic'))
					.appendTo($form);
			}
			var hs = $form.find('[name="post_segment"]');
			if (hs.length) {
				hs.val(params.has('post_segment') ? params.get('post_segment') : '');
			} else if (params.has('post_segment')) {
				$('<input type="hidden" name="post_segment" />')
					.val(params.get('post_segment'))
					.appendTo($form);
			}
		} else {
			searchField($form).val(searchTermFromParams(params));
			$form.find('[name="region"]').val(
				params.has('region') ? params.get('region') : productFinderDefaultRegionSlug()
			);
			var pi = $form.find('[name="product_industry"]');
			if (pi.length) {
				pi.val(params.has('product_industry') ? params.get('product_industry') : '');
			} else if (params.has('product_industry')) {
				$('<input type="hidden" name="product_industry" />')
					.val(params.get('product_industry'))
					.appendTo($form);
			}
			var ps = $form.find('[name="product_segment"]');
			if (ps.length) {
				ps.val(params.has('product_segment') ? params.get('product_segment') : '');
			} else if (params.has('product_segment')) {
				$('<input type="hidden" name="product_segment" />')
					.val(params.get('product_segment'))
					.appendTo($form);
			}
		}

		var urlHasSearch = searchTermFromParams(params) !== '';
		syncInsightSortChoicesForSearch($root, urlHasSearch);
		$root.data('pfHadSearch', urlHasSearch);

		var $srt = $root.find('[name="pf_sort"]');
		if ($srt.length) {
			if (params.has('pf_sort')) {
				$srt.val(params.get('pf_sort'));
			} else {
				$srt.val(urlHasSearch ? 'relevancy' : 'newest');
			}
		}

		syncViewFromUrl($root);
		syncSidebarTermLinks($root);
		syncSortDropdownFromSelect($root);
	}

	function runFetch($root, paged, append) {
		var payload = collectPayload($root, paged, append);

		return $.post(jdpowerPostFilters.ajaxUrl, payload)
			.done(function (res) {
				if (!res || !res.success || !res.data) {
					return;
				}
				var d = res.data;
				var $grid = $root.find('[data-post-filters-results]');

				if (append) {
					$grid.append(d.html);
				} else {
					$grid.html(d.html || '<p class="post-filters__empty">' + jdpowerPostFilters.i18n.noResults + '</p>');
				}

				if (d.displaying_html) {
					$root.find('[data-post-filters-displaying]').html(d.displaying_html);
				}

				var $toolbarFilters = $root.find('.post-filters__toolbar-filters');
				if ($toolbarFilters.length) {
					if (d.has_active_filters) {
						$toolbarFilters.removeAttr('hidden');
					} else {
						$toolbarFilters.attr('hidden', 'hidden');
					}
				}
				if (typeof d.pills_html === 'string') {
					$root.find('.post-filters__pills').html(d.pills_html);
				}

				$root.attr('data-paged', String(d.current_page));

				var $btn = $root.find('[data-post-filters-load-more]');
				if (d.has_more) {
					if (!$btn.length) {
						$root.find('.post-filters__footer').append(
							'<button type="button" class="post-filters__load-more" data-post-filters-load-more>' +
								jdpowerPostFilters.i18n.loadNext +
								'</button>'
						);
					}
				} else {
					$btn.remove();
				}

				if (d.url && window.history && window.history.replaceState) {
					window.history.replaceState({}, '', d.url);
				}

				if (Array.isArray(d.sidebar_term_hrefs) && d.sidebar_term_hrefs.length) {
					syncSidebarTermHrefs($root, d.sidebar_term_hrefs);
				}

				syncViewFromUrl($root);
				syncSidebarTermLinks($root);
			});
	}

	function closeMobileFiltersPanel($root, skipFocus) {
		if (!$root.hasClass('post-filters--mobile-filters-open')) {
			return;
		}
		$root.removeClass('post-filters--mobile-filters-open');
		$root.find('[data-post-filters-mobile-toggle]').attr('aria-expanded', 'false');
		if (!skipFocus) {
			$root.find('[data-post-filters-mobile-toggle]').focus();
		}
	}

	function isDesktopFiltersLayout() {
		return window.matchMedia('(min-width: 768px)').matches;
	}

	function syncSidebarFiltersLayout($root) {
		var desktop = isDesktopFiltersLayout();

		if (desktop) {
			closeMobileFiltersPanel($root, true);
			$root.find('.post-filters__sidebar-panel--desktop .post-filters__accordion').prop('open', true);
			return;
		}

		$root.find('.post-filters__sidebar-panel--mobile .post-filters__accordion').prop('open', false);
	}

	function removeSlugFromParam(val, slug) {
		if (!val) {
			return '';
		}
		var parts = val.split(',').map(function (s) {
			return s.trim();
		}).filter(function (s) {
			return s && s !== slug;
		});
		return parts.join(',');
	}

	$(function () {
		var $root = getRoot();
		if (!$root.length || typeof jdpowerPostFilters === 'undefined') {
			return;
		}

		syncHiddenFromUrl($root);
		syncSidebarFiltersLayout($root);

		function scheduleSortMinWidth() {
			syncSortDropdownMinWidth($root);
		}

		function scheduleLayoutSync() {
			syncSidebarFiltersLayout($root);
			scheduleSortMinWidth();
		}

		if (document.fonts && document.fonts.ready) {
			document.fonts.ready.then(scheduleSortMinWidth);
		} else {
			scheduleSortMinWidth();
		}

		var sortResizeTimer;
		$(window).on('resize', function () {
			clearTimeout(sortResizeTimer);
			sortResizeTimer = setTimeout(scheduleLayoutSync, 150);
		});

		$root.on('click', '[data-post-filters-mobile-toggle]', function (e) {
			e.preventDefault();
			e.stopPropagation();
			if (isDesktopFiltersLayout()) {
				return;
			}
			var willOpen = !$root.hasClass('post-filters--mobile-filters-open');
			$root.toggleClass('post-filters--mobile-filters-open', willOpen);
			$(this).attr('aria-expanded', willOpen ? 'true' : 'false');
			if (willOpen) {
				$root.find('.post-filters__sidebar-panel--mobile .post-filters__accordion').prop('open', false);
			}
		});

		$(document).on('keydown.postFiltersMobileEsc', function (e) {
			if (e.key !== 'Escape' || isDesktopFiltersLayout()) {
				return;
			}
			getRoot().each(function () {
				closeMobileFiltersPanel($(this), false);
			});
		});

		$root.on('submit', '.post-filters__search-form', function (e) {
			e.preventDefault();
			applyInsightSortDefaultForSearchTransition($root, insightHasSearch($root));
			runFetch($root, 1, false);
		});

		$root.on('change', '.post-filters__global-select', function () {
			runFetch($root, 1, false);
		});

		$root.on('change', '.post-filters__sort-select', function () {
			syncSortDropdownFromSelect($root);
			runFetch($root, 1, false);
		});

		$root.on('click', '.post-filters__sort-toggle', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $dd = $(this).closest('.post-filters__sort-dropdown');
			if ($dd.hasClass('is-open')) {
				closeSortDropdown($root, false);
			} else {
				openSortDropdown($root);
			}
		});

		$root.on('keydown', '.post-filters__sort-toggle', function (e) {
			if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') {
				return;
			}
			var $dd = $(this).closest('.post-filters__sort-dropdown');
			if ($dd.hasClass('is-open')) {
				return;
			}
			e.preventDefault();
			openSortDropdown($root);
		});

		$root.on('click', '.post-filters__sort-option', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var val = $(this).data('value');
			var $sel = $root.find('.post-filters__sort-select');
			$sel.val(val);
			syncSortDropdownFromSelect($root);
			closeSortDropdown($root, true);
			$sel.trigger('change');
		});

		$root.on('keydown', '.post-filters__sort-option', function (e) {
			var $items = $root.find('.post-filters__sort-option');
			var idx = $items.index(this);
			if (e.key === 'ArrowDown') {
				e.preventDefault();
				$items.eq(Math.min(idx + 1, $items.length - 1)).focus();
			} else if (e.key === 'ArrowUp') {
				e.preventDefault();
				$items.eq(Math.max(idx - 1, 0)).focus();
			} else if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				$(this).trigger('click');
			} else if (e.key === 'Escape') {
				e.preventDefault();
				closeSortDropdown($root, false);
			}
		});

		$(document).on('click.postFiltersSortClose', function (e) {
			if ($(e.target).closest('.post-filters__sort-dropdown').length) {
				return;
			}
			getRoot().each(function () {
				closeSortDropdown($(this), true);
			});
		});

		$(document).on('keydown.postFiltersSortEsc', function (e) {
			if (e.key !== 'Escape') {
				return;
			}
			getRoot().each(function () {
				var $r = $(this);
				if ($r.find('.post-filters__sort-dropdown').hasClass('is-open')) {
					closeSortDropdown($r, false);
				}
			});
		});

		$root.on('click', '[data-post-filters-view-toggle]', function (e) {
			e.preventDefault();
			var cur = $root.attr('data-view') === 'list' ? 'list' : 'grid';
			var mode = cur === 'list' ? 'grid' : 'list';
			applyViewToRoot($root, mode);
			var u = new URL(window.location.href);
			if (mode === 'list') {
				u.searchParams.set('pf_view', 'list');
			} else {
				u.searchParams.delete('pf_view');
			}
			if (window.history && window.history.replaceState) {
				window.history.replaceState({}, '', u.toString());
			}
		});

		function applyTermLinkHref($anchor) {
			var href = $anchor.attr('href');
			if (!href) {
				return;
			}
			var nextUrl;
			try {
				nextUrl = new URL(href, window.location.href);
			} catch (err) {
				return;
			}
			if (window.history && window.history.replaceState) {
				window.history.replaceState({}, '', nextUrl.pathname + nextUrl.search + nextUrl.hash);
			}
			syncHiddenFromUrl($root, nextUrl.search);
			runFetch($root, 1, false);
		}

		$root.on('click', '.post-filters__term-link', function (e) {
			if ($(e.target).closest('.post-filters__remove').length) {
				return;
			}
			e.preventDefault();
			applyTermLinkHref($(this));
		});

		$root.on('click', '.post-filters__remove', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var $a = $(this).closest('.post-filters__term-link');
			if ($a.length) {
				applyTermLinkHref($a);
			}
		});

		$root.on('click', '[data-post-filters-load-more]', function () {
			var $btn = $(this);
			if ($btn.prop('disabled')) {
				return;
			}

			var cur = parseInt($root.attr('data-paged'), 10) || 1;
			var label = $btn.data('load-more-label');
			if (!label) {
				label = $.trim($btn.text());
				$btn.data('load-more-label', label);
			}

			$btn.prop('disabled', true).attr('aria-busy', 'true').text(jdpowerPostFilters.i18n.loading);

			runFetch($root, cur + 1, true).always(function () {
				if (!$btn.closest('body').length) {
					return;
				}
				$btn.prop('disabled', false).removeAttr('aria-busy').text(label);
			});
		});

		$root.on('click', '.post-filters__pill', function () {
			var key = $(this).data('pill-key');
			var val = String($(this).data('pill-value'));
			var $form = $root.find('.post-filters__search-form');
			var ctx = $root.data('context');

			if (ctx === 'insight_center') {
				if (key === 's') {
					searchField($form).val('');
				} else if (key === 'pf_pt') {
					$form.find('[name="pf_pt"]').val('');
				} else if (key === 'post_industry') {
					var v = $form.find('[name="post_industry"]').val() || '';
					$form.find('[name="post_industry"]').val(removeSlugFromParam(v, val));
				} else if (key === 'post_topic') {
					var vt = $form.find('[name="post_topic"]').val() || '';
					$form.find('[name="post_topic"]').val(removeSlugFromParam(vt, val));
				} else if (key === 'post_segment') {
					var vs = $form.find('[name="post_segment"]').val() || '';
					$form.find('[name="post_segment"]').val(removeSlugFromParam(vs, val));
				}
			} else {
				if (key === 's') {
					searchField($form).val('');
				} else if (key === 'region') {
					$form.find('[name="region"]').val('');
				} else if (key === 'product_industry') {
					var vi = $form.find('[name="product_industry"]').val() || '';
					$form.find('[name="product_industry"]').val(removeSlugFromParam(vi, val));
				} else if (key === 'product_segment') {
					var vs = $form.find('[name="product_segment"]').val() || '';
					$form.find('[name="product_segment"]').val(removeSlugFromParam(vs, val));
				}
			}

			applyInsightSortDefaultForSearchTransition($root, insightHasSearch($root));
			runFetch($root, 1, false);
		});

		$root.on('click', '.post-filters__clear', function (e) {
			e.preventDefault();
			var base = $root.data('base-url') || jdpowerPostFilters.baseUrl;
			if (window.history && window.history.replaceState) {
				window.history.replaceState({}, '', base);
			}
			var $form = $root.find('.post-filters__search-form');
			searchField($form).val('');
			if ($root.data('context') === 'insight_center') {
				$form.find('[name="pf_pt"]').val('');
				$form.find('[name="post_industry"]').val('');
				$form.find('[name="post_topic"]').val('');
				$form.find('[name="post_segment"]').val('');
			} else {
				$form.find('[name="region"]').val(productFinderDefaultRegionSlug());
				$form.find('[name="product_industry"]').val('');
				$form.find('[name="product_segment"]').val('');
			}
			$root.find('.post-filters__toolbar-filters').attr('hidden', 'hidden');
			$root.find('.post-filters__pills').empty();
			applyViewToRoot($root, 'grid');
			applyInsightSortDefaultForSearchTransition($root, false);
			runFetch($root, 1, false);
		});

		$root.data('pfHadSearch', insightHasSearch($root));
	});
})(jQuery);
