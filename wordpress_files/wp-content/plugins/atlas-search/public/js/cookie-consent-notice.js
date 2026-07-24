(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') {
      fn();
    } else {
      document.addEventListener('DOMContentLoaded', fn);
    }
  }

  function init() {
    if (typeof window.ajaxurl === 'undefined') {
      return;
    }

    if (typeof window.wpeAitkData === 'undefined') {
      return;
    }

    document.addEventListener('click', function (e) {
      if (e.target && e.target.id === 'cookie-accept') {
        e.preventDefault();
        handleCookieAccept(e.target);
      }
    });
  }

  function handleCookieAccept(button) {
    var notice = findClosestNotice(button);
    if (!notice) {
      return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.ajaxurl, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          try {
            var response = JSON.parse(xhr.responseText);
            handleAjaxResponse(response, notice);
          } catch (e) {
            handleAjaxError(notice);
          }
        } else {
          handleAjaxError(notice);
        }
      }
    };

    var formData =
      'action=' +
      encodeURIComponent(window.wpeAitkData.action) +
      '&_ajax_nonce=' +
      encodeURIComponent(window.wpeAitkData.nonce);

    xhr.send(formData);
  }

  function findClosestNotice(element) {
    var current = element;
    while (current && current !== document) {
      if (
        current.classList &&
        (current.classList.contains('notice') ||
          current.classList.contains('wpe-aitk-notice'))
      ) {
        return current;
      }
      current = current.parentNode;
    }
    return null;
  }

  function handleAjaxResponse(response, notice) {
    if (response.success) {
      notice.classList.remove('notice-warning');
      notice.classList.add('notice-success');

      notice.innerHTML =
        '<p><strong>✓ Cookie Acknowledgement Complete</strong></p>' +
        '<p>Thank you for acknowledging our data collection policy for AI-Powered Recommendations.</p>';

      setTimeout(function () {
        notice.style.transition = 'opacity 0.5s';
        notice.style.opacity = '0';
        setTimeout(function () {
          if (notice.parentNode) {
            notice.parentNode.removeChild(notice);
          }
        }, 500);
      }, 3000);
    } else {
      handleAjaxError(notice);
    }
  }

  function handleAjaxError(notice) {
    notice.classList.remove('notice-warning');
    notice.classList.add('notice-error');

    notice.innerHTML =
      '<p><strong>Something went wrong.</strong> Please try again in a moment.</p>';

    setTimeout(function () {
      window.location.reload();
    }, 3000);
  }

  ready(init);
})();
