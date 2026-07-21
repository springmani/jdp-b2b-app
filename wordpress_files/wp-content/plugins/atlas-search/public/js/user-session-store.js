function getCookieValue(name) {
  const parts = ('; ' + document.cookie).split('; ' + name + '=');
  if (parts.length === 2 && parts[1]) {
    return parts.pop().split(';').shift();
  }
  return null;
}

function setCookieValue(name, value, expirationDate, path = '/') {
  document.cookie = `${name}=${value}; expires=${expirationDate.toUTCString()}; path=${path}`;
}

function generateUniqueId() {
  return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (digit) =>
    (
      digit ^
      (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (digit / 4)))
    ).toString(16)
  );
}

const ONE_DAY = 24 * 60 * 60 * 1000;
const THIRTY_MINUTES = 30 * 60 * 1000;
const USER_COOKIE = 'EA_UID';
const SESSION_COOKIE = 'EA_SID';
const SESSION_SAMPLED_COOKIE = 'EA_SESSION_SAMPLED';
const ACKNOWLEDGEMENT_COOKIE = 'EA_COOKIE_ACK';
const DEFAULT_SAMPLING = 1;

class UserSessionStore {
  constructor() {
    this.userToken = getCookieValue(USER_COOKIE) || generateUniqueId();
    this.userTokenLifetime = ONE_DAY;
    this.sessionLifetime = THIRTY_MINUTES;
    this.samplingRate = DEFAULT_SAMPLING;

    // Only initialize cookies if acknowledgement is given
    if (this.isAcknowledged()) {
      this.initializeCookies();
    }
  }

  isAcknowledged() {
    // Check server-side acknowledgement status first, then fall back to cookie
    if (
      typeof window.userSessionConfig !== 'undefined' &&
      window.userSessionConfig.isAcknowledged === 'true'
    ) {
      return true;
    }

    // Fallback to cookie-based check for client-side state
    return getCookieValue(ACKNOWLEDGEMENT_COOKIE) === 'true';
  }

  setAcknowledgement(acknowledged = true) {
    if (acknowledged) {
      const expirationDate = new Date(Date.now() + ONE_DAY);
      setCookieValue(ACKNOWLEDGEMENT_COOKIE, 'true', expirationDate);
      this.initializeCookies();
    } else {
      // Clear all tracking cookies if acknowledgement is revoked
      this.clearAllCookies();
    }
  }

  initializeCookies() {
    if (!getCookieValue(SESSION_SAMPLED_COOKIE)) {
      this.setSessionSampledCookie(
        (Math.random() <= this.samplingRate).toString()
      );
    }

    if (this.userToken !== getCookieValue(USER_COOKIE)) {
      this.updateUserCookie();
    }

    this.updateSessionCookie();
  }

  clearAllCookies() {
    // Set cookies to expire immediately
    const pastDate = new Date(0);
    setCookieValue(USER_COOKIE, '', pastDate);
    setCookieValue(SESSION_COOKIE, '', pastDate);
    setCookieValue(SESSION_SAMPLED_COOKIE, '', pastDate);
    setCookieValue(ACKNOWLEDGEMENT_COOKIE, '', pastDate);
  }

  getUserId() {
    if (!this.isAcknowledged()) {
      return null;
    }

    let userId = getCookieValue(USER_COOKIE);
    if (!userId) {
      this.updateUserCookie();
      userId = getCookieValue(USER_COOKIE);
    }
    return userId;
  }

  isSessionSampled() {
    if (!this.isAcknowledged()) {
      return false;
    }
    return getCookieValue(SESSION_SAMPLED_COOKIE) === 'true';
  }

  setSessionSampledCookie(value) {
    if (!this.isAcknowledged()) {
      return;
    }
    const expirationDate = new Date(Date.now() + THIRTY_MINUTES);
    setCookieValue(SESSION_SAMPLED_COOKIE, value, expirationDate);
  }

  getSessionId() {
    if (!this.isAcknowledged()) {
      return null;
    }
    return getCookieValue(SESSION_COOKIE);
  }

  updateSessionCookie() {
    if (!this.isAcknowledged()) {
      return;
    }
    const sessionId = getCookieValue(SESSION_COOKIE) || generateUniqueId();
    const expirationDate = new Date(Date.now() + this.sessionLifetime);
    setCookieValue(SESSION_COOKIE, sessionId, expirationDate);
  }

  updateUserCookie() {
    if (!this.isAcknowledged()) {
      return;
    }
    const expirationDate = new Date(Date.now() + this.userTokenLifetime);
    setCookieValue(USER_COOKIE, this.userToken, expirationDate);
  }
}

const userSessionStore = new UserSessionStore();

// Make userSessionStore globally available
window.userSessionStore = userSessionStore;

// Listen for consent events to enable cookie tracking
document.addEventListener('click', function (e) {
  if (e.target && e.target.id === 'cookie-accept') {
    // Set acknowledgement when user clicks consent button
    userSessionStore.setAcknowledgement(true);
  }
});

// Initialize session cookie only if already acknowledged
if (userSessionStore.isAcknowledged()) {
  userSessionStore.updateSessionCookie();
}
