function bindNativeDialog(dialog, closeSelector) {
    if (! dialog) {
        return;
    }

    dialog.querySelectorAll(closeSelector).forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        dialog.close();
    });
}

function initPlanningCalendarActions() {
    const deleteDialog = document.getElementById('planning-delete-dialog');
    const deleteForm = document.getElementById('planning-delete-dialog-form');
    const deleteLabel = document.getElementById('planning-delete-dialog-label');
    const deleteDate = document.getElementById('planning-delete-dialog-date');

    const duplicateDialog = document.getElementById('planning-duplicate-dialog');
    const duplicateForm = document.getElementById('planning-duplicate-dialog-form');
    const duplicateDateInput = document.getElementById('planning-duplicate-dialog-date');

    if (! deleteDialog && ! duplicateDialog) {
        return;
    }

    bindNativeDialog(deleteDialog, '[data-planning-delete-close]');
    bindNativeDialog(duplicateDialog, '[data-planning-duplicate-close]');

    document.addEventListener('click', (event) => {
        const deleteTrigger = event.target.closest('[data-planning-delete]');
        if (deleteTrigger && deleteDialog && deleteForm) {
            event.preventDefault();
            event.stopPropagation();

            deleteForm.action = deleteTrigger.getAttribute('data-delete-url') ?? '';
            if (deleteLabel) {
                deleteLabel.textContent = deleteTrigger.getAttribute('data-delete-label') ?? '';
            }
            if (deleteDate) {
                deleteDate.textContent = deleteTrigger.getAttribute('data-delete-date') ?? '';
            }

            deleteDialog.showModal();
            return;
        }

        const duplicateTrigger = event.target.closest('[data-planning-duplicate-open]');
        if (duplicateTrigger && duplicateDialog && duplicateForm && duplicateDateInput) {
            event.preventDefault();
            event.stopPropagation();

            duplicateForm.action = duplicateTrigger.getAttribute('data-duplicate-url') ?? '';
            duplicateDateInput.value = duplicateTrigger.getAttribute('data-duplicate-date') ?? '';
            duplicateDialog.showModal();
        }
    }, true);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPlanningCalendarActions);
} else {
    initPlanningCalendarActions();
}
