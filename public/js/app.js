const dialog = document.getElementById('confirm-dialog');
let pendingForm = null;
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.confirmed === 'yes') return;
        event.preventDefault();
        pendingForm = form;
        document.getElementById('confirm-text').textContent = form.dataset.confirm;
        dialog.showModal();
    });
});
dialog?.addEventListener('close', () => {
    if (dialog.returnValue === 'confirm' && pendingForm) {
        pendingForm.dataset.confirmed = 'yes';
        pendingForm.requestSubmit();
    }
    pendingForm = null;
    dialog.returnValue = '';
});
