<script>
window.showNotificationPopUp = function(type, message, title = null) {
    const toast = document.createElement('div');

    const map = {
        success: { icon: 'fa-check-circle', color: 'success' },
        error: { icon: 'fa-times-circle', color: 'danger' },
        warning: { icon: 'fa-exclamation-triangle', color: 'warning' },
        info: { icon: 'fa-info-circle', color: 'primary' }
    };

    const cfg = map[type] ?? map.info;

    toast.className = `admin-toast shadow border-${cfg.color}`;
    toast.innerHTML = `
        <i class="fas ${cfg.icon} text-${cfg.color} mr-2"></i>
        <div class="toast-body">
            <strong class="text-${cfg.color}">
                ${title ?? type.charAt(0).toUpperCase() + type.slice(1)}
            </strong>
            <p>${message}</p>
        </div>
        <button type="button" class="close ml-3">&times;</button>
    `;

    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('show'));

    const close = () => {
        toast.classList.remove('show');
        toast.classList.add('hide');
        setTimeout(() => toast.remove(), 500);
    };

    toast.querySelector('.close').addEventListener('click', close);
    setTimeout(close, 7000);
};
</script>
