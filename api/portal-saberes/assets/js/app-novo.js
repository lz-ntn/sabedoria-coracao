(function () {
  'use strict';

  function showToast(message, type) {
    var container = document.querySelector('.toast-container');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'toast';
    el.textContent = message;
    container.appendChild(el);
    setTimeout(function () { el.remove(); }, 3000);
  }

  window.PortalApp = { showToast: showToast };
})();
