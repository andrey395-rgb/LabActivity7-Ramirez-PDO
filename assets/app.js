// Client-side validation that complements the HTML5 attributes
// (required, minlength, maxlength, type="email"). The server validates everything again.

document.querySelectorAll('form').forEach(function (form) {
    // "Confirm password" fields must match the field named in data-match
    form.querySelectorAll('[data-match]').forEach(function (confirmField) {
        var original = form.querySelector('#' + confirmField.dataset.match);
        function checkMatch() {
            confirmField.setCustomValidity(
                confirmField.value && confirmField.value !== original.value ? 'Passwords do not match.' : ''
            );
        }
        confirmField.addEventListener('input', checkMatch);
        original.addEventListener('input', checkMatch);
    });

    form.addEventListener('submit', function (event) {
        // Ask before destructive actions (forms with data-confirm, e.g. delete)
        if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            return;
        }

        // Trim text fields so whitespace-only input counts as empty (passwords are left as typed)
        form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function (field) {
            field.value = field.value.trim();
        });

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        // Prevent double submissions
        var button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
        }
    });

    // Clear the server-side error styling once the user edits the field
    form.querySelectorAll('.is-invalid').forEach(function (field) {
        field.addEventListener('input', function () {
            field.classList.remove('is-invalid');
            var error = field.parentElement.querySelector('.field-error');
            if (error) {
                error.remove();
            }
        }, { once: true });
    });
});
