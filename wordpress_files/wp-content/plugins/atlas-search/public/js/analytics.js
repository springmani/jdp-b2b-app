class AnalyticsTracker {
  constructor() {
    this.url =
      window.wpeAitkAnalytics.restUrl +
      'analytics/v1/behavioral-analytics/event';
    this.isBeaconSupported =
      navigator && typeof navigator.sendBeacon === 'function';
  }

  trackEvent(eventType, eventData) {
    const requestData = {
      // eslint-disable-next-line no-undef
      session: { id: userSessionStore.getSessionId() || 'anonymous' },
      // eslint-disable-next-line no-undef
      userID: userSessionStore.getUserId() || 'anonymous',
    };

    const payload = {
      ...requestData,
      ...eventData,
      // eslint-disable-next-line no-undef
      _wpnonce: wpeAitkAnalytics.nonce,
    };

    const endpoint = `${this.url}/${eventType}`;

    if (this.isBeaconSupported) {
      const blob = new Blob([JSON.stringify(payload)], {
        type: 'application/json',
      });
      if (navigator.sendBeacon(endpoint, blob)) {
        return;
      }
    }

    // Fall back to fetch if sendBeacon isn't supported or fails
    this.sendWithFetch(endpoint, payload);
  }

  sendWithFetch(endpoint, payload) {
    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
      keepalive: true,
    }).catch((error) => {
      console.error('Analytics fetch error: ', error);
    });
  }

  trackPageView(pageViewData) {
    return this.trackEvent('page_view', pageViewData);
  }

  trackSearch(searchData) {
    return this.trackEvent('search', searchData);
  }

  trackSearchResultClick(clickData) {
    return this.trackEvent('search_click', clickData);
  }
}

const state = {
  trackerInstance: null,
  eventQueue: [],
  searchResults: [],
  searchObject: null,
  clickHandlerInitialized: false,
};

function removeTrailingSlash(url) {
  return url.endsWith('/') && url.length > 1 ? url.slice(0, -1) : url;
}

const AnalyticsTrackerAPI = {
  createTracker() {
    if (!state.trackerInstance) {
      state.trackerInstance = new AnalyticsTracker();

      // Process queued events
      if (state.eventQueue.length > 0) {
        state.eventQueue.forEach(([eventType, data]) => {
          state.trackerInstance.trackEvent(eventType, data);
        });
        state.eventQueue = [];
      }
    }
  },

  clickDataParamName: '_recommendations_data',

  trackPageView() {
    // only track page views for non-search pages that have a document ID
    const urlParams = new URLSearchParams(window.location.search);
    // eslint-disable-next-line no-undef
    if (!wpeAitkAnalytics.documentID || urlParams.has('s')) {
      return;
    }

    // Get current URL without click data parameter
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete(this.clickDataParamName);
    const cleanUrl = currentUrl.toString();

    const pageViewData = {
      data: {
        // eslint-disable-next-line no-undef
        documentID: wpeAitkAnalytics.documentID,
        page: {
          referrer: document.referrer,
          // eslint-disable-next-line no-undef
          title: wpeAitkAnalytics.title,
          url: cleanUrl,
        },
      },
    };

    AnalyticsTrackerAPI.createTracker();
    if (state.trackerInstance) {
      state.trackerInstance.trackPageView(pageViewData);
    } else {
      state.eventQueue.push(['page_view', pageViewData]);
    }
  },

  getSearchParams(urlParams) {
    const searchTerm = urlParams.get('s') || '*';
    const sortName = urlParams.get('orderby') || 'default';
    // Get all other parameters as filters
    const filters = [];
    const excludeKeys = ['s', 'orderby'];

    [...new Set(urlParams.keys())]
      .filter((key) => !excludeKeys.includes(key))
      .forEach((key) => {
        const items = urlParams.getAll(key).flatMap((item) => item.split(','));
        if (items.length > 0) {
          filters.push({
            filterName: key,
            items: items,
          });
        }
      });
    return { searchTerm, filters, sortName };
  },

  trackSearch(urlParams) {
    const { searchTerm, filters, sortName } = this.getSearchParams(urlParams);

    // eslint-disable-next-line no-undef
    state.searchResults = wpeAitkAnalytics.searchResults;

    state.searchObject = {
      query: searchTerm,
      filters: filters,
      sort: { name: sortName },
      results: {
        items: state.searchResults.map((item) => ({
          documentID: item.documentID,
          page: {
            title: item.title,
            referrer: document.referrer,
            url: item.url,
          },
        })),
      },
    };

    const searchData = { data: { search: state.searchObject } };

    AnalyticsTrackerAPI.createTracker();
    if (state.trackerInstance) {
      state.trackerInstance.trackSearch(searchData);
    } else {
      state.eventQueue.push(['search', searchData]);
    }
  },

  trackSearchResultClick() {
    const urlParams = new URLSearchParams(window.location.search);
    const paramData = urlParams.get(this.clickDataParamName);
    if (!paramData) {
      return;
    }

    const url = new URL(window.location.href);
    url.searchParams.delete(this.clickDataParamName);
    const cleanUrl = url.toString();
    window.history.replaceState(window.history.state, '', cleanUrl);

    if (!document.referrer) {
      return;
    }
    const referrer = new URL(document.referrer);

    if (
      referrer.hostname !== window.location.hostname ||
      !referrer.searchParams.has('s')
    ) {
      return;
    }

    const { searchTerm, filters, sortName } = this.getSearchParams(
      referrer.searchParams
    );

    let position;
    try {
      const decodedData = JSON.parse(atob(paramData));
      position = decodedData.position;
    } catch (e) {
      // If parsing fails, position will be undefined
    }

    const clickData = {
      data: {
        // eslint-disable-next-line no-undef
        documentID: wpeAitkAnalytics.documentID,
        ...(position !== undefined && { position }),
        page: {
          referrer: document.referrer,
          // eslint-disable-next-line no-undef
          title: wpeAitkAnalytics.title,
          url: cleanUrl,
        },
        search: {
          query: searchTerm,
          filters: filters,
          sort: { name: sortName },
        },
      },
    };

    AnalyticsTrackerAPI.createTracker();
    if (state.trackerInstance) {
      state.trackerInstance.trackSearchResultClick(clickData);
    } else {
      state.eventQueue.push(['search_click', clickData]);
    }
  },

  initSearchResultClickTracking() {
    if (
      state.searchResults.length === 0 ||
      !state.searchObject ||
      state.clickHandlerInitialized
    ) {
      return;
    }

    // Get pagination info to calculate global position
    // eslint-disable-next-line no-undef
    const currentPage = wpeAitkAnalytics.currentPage || 1;
    // eslint-disable-next-line no-undef
    const postsPerPage = wpeAitkAnalytics.postsPerPage;

    document.querySelectorAll('a').forEach((link) => {
      if (link.href.startsWith('javascript:')) return;

      const currentPagePath = window.location.href.split('#')[0];
      const anchorPath = link.href.split('#')[0];
      if (link.href.includes('#') && anchorPath === currentPagePath) return;

      // Find the position (0-indexed) on current page
      const localPosition = state.searchResults.findIndex(
        (result) =>
          removeTrailingSlash(result.url) === removeTrailingSlash(link.href)
      );

      if (localPosition !== -1) {
        // Calculate global position across all pages (1-indexed)
        let globalPosition;
        if (postsPerPage === -1) {
          // No pagination (-1 means show all posts), use local position only
          globalPosition = localPosition + 1;
        } else {
          // With pagination, calculate offset from previous pages
          const offset = (currentPage - 1) * postsPerPage;
          globalPosition = offset + localPosition + 1;
        }

        const paramData = { position: globalPosition };
        let base64 = btoa(JSON.stringify(paramData));
        const url = new URL(link.href, window.location.origin);
        url.searchParams.set(this.clickDataParamName, base64);
        link.href = url.toString();
      }
    });
    state.clickHandlerInitialized = true;
  },
};

function init() {
  // Track page view on initial load
  window.addEventListener('pageshow', () => {
    AnalyticsTrackerAPI.trackPageView();
    AnalyticsTrackerAPI.trackSearchResultClick();
  });

  // Track page views on navigation changes
  const trackPageViewOnNavigation = () => {
    AnalyticsTrackerAPI.trackPageView();
  };

  if (window.history) {
    const originalPushState = window.history.pushState;
    window.history.pushState = (...args) => {
      originalPushState.apply(window.history, args);
      window.dispatchEvent(new Event('ewt:pushstate'));
    };
    window.addEventListener('ewt:pushstate', trackPageViewOnNavigation);
    window.addEventListener('popstate', trackPageViewOnNavigation);
  } else {
    window.addEventListener('hashchange', trackPageViewOnNavigation);
  }

  // Initialize search tracking when the DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('s')) {
      AnalyticsTrackerAPI.trackSearch(urlParams);
      AnalyticsTrackerAPI.initSearchResultClickTracking();
    }
  });
}

init();
