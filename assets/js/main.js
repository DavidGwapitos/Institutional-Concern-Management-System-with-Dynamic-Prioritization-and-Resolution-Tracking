// assets/js/main.js - Global UI interactions

document.addEventListener('DOMContentLoaded', () => {
  // Mobile sidebar toggle
  const mobileMenuBtn = document.getElementById('mobileMenuBtn');
  const sidebar = document.getElementById('appSidebar');

  if (mobileMenuBtn && sidebar) {
    mobileMenuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      sidebar.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
      if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== mobileMenuBtn) {
        sidebar.classList.remove('open');
      }
    });
  }

  // Star Rating Interactive Widget (Figures 3 & 13)
  const starContainer = document.querySelector('.star-rating');
  if (starContainer) {
    const stars = starContainer.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingValue');

    stars.forEach(star => {
      star.addEventListener('click', () => {
        const val = parseInt(star.getAttribute('data-value'), 10);
        if (ratingInput) ratingInput.value = val;
        
        stars.forEach(s => {
          const sVal = parseInt(s.getAttribute('data-value'), 10);
          if (sVal <= val) {
            s.classList.add('selected');
          } else {
            s.classList.remove('selected');
          }
        });
      });
    });
  }
});

// Notification Dropdown Helpers
function toggleNotificationDropdown(e) {
  if (e) e.stopPropagation();
  const dropdown = document.getElementById('notificationDropdown');
  if (dropdown) {
    dropdown.classList.toggle('active');
  }
}

function markAllNotificationsRead(e) {
  if (e) e.stopPropagation();
  const items = document.querySelectorAll('.notification-item.unread');
  items.forEach(item => item.classList.remove('unread'));
  const badge = document.getElementById('notifBadgeCount');
  if (badge) badge.style.display = 'none';
  const unreadBadge = document.getElementById('notifUnreadBadge');
  if (unreadBadge) {
    unreadBadge.textContent = '0 New';
    unreadBadge.style.backgroundColor = '#f1f5f9';
    unreadBadge.style.color = '#64748b';
  }
  showToast('All notifications marked as read.', 'success', 'Notifications');
}

// Close notification dropdown when clicking outside or pressing Escape
document.addEventListener('click', (e) => {
  const dropdown = document.getElementById('notificationDropdown');
  const btn = document.getElementById('notificationBtn');
  if (dropdown && dropdown.classList.contains('active')) {
    if (!dropdown.contains(e.target) && (!btn || !btn.contains(e.target))) {
      dropdown.classList.remove('active');
    }
  }
});

// Modal helpers
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
    // Only restore scroll if no other modals are active
    if (!document.querySelector('.modal-backdrop.active')) {
      document.body.style.overflow = '';
    }
  }
}

function closeAllModals() {
  document.querySelectorAll('.modal-backdrop.active').forEach(m => {
    m.classList.remove('active');
  });
  document.body.style.overflow = '';
}

// Global Keyboard Listener for Modals & Dropdowns (Escape key)
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeAllModals();
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) dropdown.classList.remove('active');
  }
});

// Universal Action Confirmation Modal
function showConfirmModal(options) {
  const modal = document.getElementById('confirmationModal');
  if (!modal) {
    if (confirm(options.message || 'Are you sure you want to proceed?')) {
      if (options.actionUrl) window.location.href = options.actionUrl;
      if (typeof options.onConfirm === 'function') options.onConfirm();
    }
    return;
  }

  const titleEl = document.getElementById('confirmModalTitle');
  const msgEl = document.getElementById('confirmModalMessage');
  const btnEl = document.getElementById('confirmModalActionBtn');
  const iconWrap = document.getElementById('confirmModalIconWrap');

  if (titleEl) titleEl.textContent = options.title || 'Confirm Action';
  if (msgEl) msgEl.innerHTML = options.message || 'Are you sure you want to proceed? This cannot be undone.';

  if (btnEl) {
    btnEl.textContent = options.confirmText || 'Confirm';
    btnEl.className = 'btn ' + (options.btnClass || 'btn-primary');

    const type = options.type || 'danger';
    if (type === 'danger') {
      btnEl.style.backgroundColor = '#dc2626';
      btnEl.style.borderColor = '#dc2626';
      btnEl.style.color = '#ffffff';
    } else if (type === 'warning') {
      btnEl.style.backgroundColor = '#d97706';
      btnEl.style.borderColor = '#d97706';
      btnEl.style.color = '#ffffff';
    } else {
      btnEl.style.backgroundColor = '#2563eb';
      btnEl.style.borderColor = '#2563eb';
      btnEl.style.color = '#ffffff';
    }

    if (options.actionUrl) {
      btnEl.href = options.actionUrl;
      btnEl.onclick = null;
    } else if (typeof options.onConfirm === 'function') {
      btnEl.href = 'javascript:void(0)';
      btnEl.onclick = function(e) {
        e.preventDefault();
        closeModal('confirmationModal');
        options.onConfirm();
      };
    }
  }

  if (iconWrap) {
    const type = options.type || 'danger';
    iconWrap.className = 'confirm-icon-wrap ' + 
      (type === 'warning' ? 'confirm-icon-warning' : (type === 'info' ? 'confirm-icon-info' : 'confirm-icon-danger'));
  }

  openModal('confirmationModal');
}

// Global data-confirm listener for links and buttons
document.addEventListener('click', (e) => {
  const trigger = e.target.closest('[data-confirm]');
  if (trigger) {
    e.preventDefault();
    const message = trigger.getAttribute('data-confirm');
    const title = trigger.getAttribute('data-confirm-title') || 'Confirm Action';
    const confirmText = trigger.getAttribute('data-confirm-text') || 'Confirm';
    const btnClass = trigger.getAttribute('data-confirm-btn') || 'btn-primary';
    const type = trigger.getAttribute('data-confirm-type') || 'danger';
    const actionUrl = trigger.getAttribute('href');

    const form = trigger.closest('form');
    if ((!actionUrl || actionUrl === '#' || actionUrl.startsWith('javascript:')) && form) {
      showConfirmModal({
        title,
        message,
        confirmText,
        btnClass,
        type,
        onConfirm: () => form.submit()
      });
      return;
    }

    showConfirmModal({
      title,
      message,
      confirmText,
      btnClass,
      type,
      actionUrl
    });
  }
});

// Toast Notification System (Top-Right Upper Corner)
function showToast(message, type = 'success', title = '', duration = 4500) {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const defaultTitles = {
    success: 'Success',
    error: 'Error Notice',
    warning: 'Attention Needed',
    info: 'Information'
  };

  const icons = {
    success: `<svg class="svg-icon" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
    error: `<svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
    warning: `<svg class="svg-icon" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
    info: `<svg class="svg-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`
  };

  const toastType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
  const toastTitle = title || defaultTitles[toastType];

  const toast = document.createElement('div');
  toast.className = `toast toast-${toastType}`;

  toast.innerHTML = `
    <div class="toast-icon-wrap">
      ${icons[toastType]}
    </div>
    <div class="toast-content">
      <div class="toast-title">${toastTitle}</div>
      <div class="toast-message">${message}</div>
    </div>
    <button type="button" class="toast-close" title="Dismiss" aria-label="Close">&times;</button>
    <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
  `;

  container.appendChild(toast);

  let dismissTimeout = setTimeout(() => hideToast(toast), duration);

  // Pause progress & dismiss on hover
  toast.addEventListener('mouseenter', () => {
    clearTimeout(dismissTimeout);
    const prog = toast.querySelector('.toast-progress');
    if (prog) prog.style.animationPlayState = 'paused';
  });

  toast.addEventListener('mouseleave', () => {
    const prog = toast.querySelector('.toast-progress');
    if (prog) prog.style.animationPlayState = 'running';
    dismissTimeout = setTimeout(() => hideToast(toast), 2200);
  });

  const closeBtn = toast.querySelector('.toast-close');
  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      clearTimeout(dismissTimeout);
      hideToast(toast);
    });
  }
}

function hideToast(toast) {
  if (!toast || toast.classList.contains('toast-hiding')) return;
  toast.classList.add('toast-hiding');
  toast.addEventListener('animationend', () => {
    toast.remove();
  }, { once: true });
}
