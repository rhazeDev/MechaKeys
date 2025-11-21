class CustomAlert {
    constructor() {
        this.createOverlay();
        this.createToastContainer();
    }

    createOverlay() {
        if (document.getElementById('customAlertOverlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'customAlertOverlay';
        overlay.className = 'custom-alert-overlay';
        overlay.innerHTML = `
            <div class="custom-alert-box" onclick="event.stopPropagation()">
                <div class="custom-alert-header">
                    <div class="custom-alert-icon" id="alertIcon"></div>
                    <div class="custom-alert-content">
                        <h3 class="custom-alert-title" id="alertTitle"></h3>
                    </div>
                </div>
                <div class="custom-alert-message" id="alertMessage"></div>
                <div class="custom-alert-actions" id="alertActions"></div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    createToastContainer() {
        if (document.getElementById('toastContainer')) return;

        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    show(options) {
        const {
            type = 'info',
            title = 'Notification',
            message = '',
            confirmText = 'OK',
            cancelText = 'Cancel',
            showCancel = false,
            onConfirm = null,
            onCancel = null
        } = options;

        const overlay = document.getElementById('customAlertOverlay');
        const icon = document.getElementById('alertIcon');
        const titleEl = document.getElementById('alertTitle');
        const messageEl = document.getElementById('alertMessage');
        const actionsEl = document.getElementById('alertActions');

        const icons = {
            success: '<i class="fas fa-check"></i>',
            error: '<i class="fas fa-times"></i>',
            warning: '<i class="fas fa-exclamation"></i>',
            info: '<i class="fas fa-info"></i>'
        };

        icon.className = `custom-alert-icon ${type}`;
        icon.innerHTML = icons[type] || icons.info;
        titleEl.textContent = title;
        messageEl.textContent = message;

        actionsEl.innerHTML = '';

        if (showCancel) {
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'custom-alert-btn secondary';
            cancelBtn.textContent = cancelText;
            cancelBtn.onclick = () => {
                this.close();
                if (onCancel) onCancel();
            };
            actionsEl.appendChild(cancelBtn);
        }

        const confirmBtn = document.createElement('button');
        confirmBtn.className = `custom-alert-btn ${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'}`;
        confirmBtn.textContent = confirmText;
        confirmBtn.onclick = () => {
            this.close();
            if (onConfirm) onConfirm();
        };
        actionsEl.appendChild(confirmBtn);

        overlay.classList.add('active');

        overlay.onclick = (e) => {
            if (e.target === overlay) {
                this.close();
                if (onCancel) onCancel();
            }
        };

        const escHandler = (e) => {
            if (e.key === 'Escape') {
                this.close();
                if (onCancel) onCancel();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    close() {
        const overlay = document.getElementById('customAlertOverlay');
        overlay.classList.remove('active');
    }

    success(message, title = 'Success') {
        this.show({
            type: 'success',
            title,
            message,
            confirmText: 'OK'
        });
    }

    error(message, title = 'Error') {
        this.show({
            type: 'error',
            title,
            message,
            confirmText: 'OK'
        });
    }

    warning(message, title = 'Warning') {
        this.show({
            type: 'warning',
            title,
            message,
            confirmText: 'OK'
        });
    }

    info(message, title = 'Information') {
        this.show({
            type: 'info',
            title,
            message,
            confirmText: 'OK'
        });
    }

    confirm(options) {
        const {
            message,
            title = 'Confirm',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            onConfirm = null,
            onCancel = null
        } = options;

        this.show({
            type: 'warning',
            title,
            message,
            confirmText,
            cancelText,
            showCancel: true,
            onConfirm,
            onCancel
        });
    }

    toast(options) {
        const {
            type = 'info',
            title = '',
            message = '',
            duration = 3000
        } = options;

        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
            success: '<i class="fas fa-check-circle"></i>',
            error: '<i class="fas fa-exclamation-circle"></i>',
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            info: '<i class="fas fa-info-circle"></i>'
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || icons.info}</div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close">×</button>
        `;

        const APPEAR_DELAY = 500;
        const showTimeout = setTimeout(() => {
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.add('enter');
            });

            const removeTimeout = setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, duration);

            toast._removeTimeout = removeTimeout;

            const closeBtn = toast.querySelector('.toast-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (toast._appearTimeout) {
                        clearTimeout(toast._appearTimeout);
                        delete toast._appearTimeout;
                    }
                    if (toast._removeTimeout) {
                        clearTimeout(toast._removeTimeout);
                        delete toast._removeTimeout;
                    }
                    toast.classList.add('removing');
                    setTimeout(() => toast.remove(), 300);
                });
            }
        }, APPEAR_DELAY);

        toast._appearTimeout = showTimeout;
    }
}

const customAlert = new CustomAlert();

function showAlert(message, type = 'info', title = '') {
    customAlert.show({
        type,
        title: title || (type.charAt(0).toUpperCase() + type.slice(1)),
        message
    });
}

function showSuccess(message, title = 'Success', duration = 3000) {
    customAlert.toast({ type: 'success', title, message, duration });
}

function showError(message, title = 'Error', duration = 3000) {
    customAlert.toast({ type: 'error', title, message, duration });
}

function showWarning(message, title = 'Warning', duration = 3000) {
    customAlert.toast({ type: 'warning', title, message, duration });
}

function showInfo(message, title = 'Information', duration = 3000) {
    customAlert.toast({ type: 'info', title, message, duration });
}

function showConfirm(message, onConfirm, onCancel = null, title = 'Confirm') {
    customAlert.confirm({
        message,
        title,
        onConfirm,
        onCancel
    });
}

function showToast(message, type = 'info', title = '', duration = 3000) {
    customAlert.toast({
        type,
        title,
        message,
        duration
    });
}
