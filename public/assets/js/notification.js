/**
 * Notification System
 * Simplified, clean toast notification system
 */

window.Notification = {
  container: null,

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'notification-container';
      this.container.className = 'fixed top-4 right-4 z-[9999] flex flex-col gap-3 pointer-events-none';
      this.container.style.maxWidth = '400px';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 5000) {
    this.init();

    const colors = {
      success: { bg: '#10b981', icon: '✓' },
      error: { bg: '#ef4444', icon: '✕' },
      warning: { bg: '#f59e0b', icon: '⚠' },
      info: { bg: '#3b82f6', icon: 'ℹ' }
    };

    const config = colors[type] || colors.info;

    // Create notification element
    const notif = document.createElement('div');
    notif.className = 'pointer-events-auto';
    notif.innerHTML = `
            <div class="notification-item flex items-start gap-3 p-4 rounded-lg shadow-lg text-white animate-slide-in"
                 style="background: ${config.bg}; min-width: 300px; max-width: 400px;">
                <span class="text-xl font-bold flex-shrink-0">${config.icon}</span>
                <span class="flex-1 text-sm leading-relaxed">${message}</span>
                <button onclick="this.closest('.notification-item').parentElement.remove()" 
                        class="flex-shrink-0 hover:opacity-70 transition text-xl leading-none">
                    ×
                </button>
            </div>
        `;

    this.container.insertBefore(notif, this.container.firstChild);

    // Auto remove
    if (duration > 0) {
      setTimeout(() => {
        notif.style.opacity = '0';
        notif.style.transform = 'translateX(100%)';
        notif.style.transition = 'all 0.3s ease-out';
        setTimeout(() => notif.remove(), 300);
      }, duration);
    }

    return notif;
  },

  success(message, duration) {
    return this.show(message, 'success', duration);
  },

  error(message, duration) {
    return this.show(message, 'error', duration);
  },

  warning(message, duration) {
    return this.show(message, 'warning', duration);
  },

  info(message, duration) {
    return this.show(message, 'info', duration);
  }
};

// Add animation styles
if (!document.getElementById('notification-styles')) {
  const style = document.createElement('style');
  style.id = 'notification-styles';
  style.textContent = `
        @keyframes slide-in {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
    `;
  document.head.appendChild(style);
}

// Backward compatibility
window.toast = window.Notification;
