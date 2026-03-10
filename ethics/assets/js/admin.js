// Confirm delete actions
document.querySelectorAll('.delete-confirm').forEach(btn => {
    btn.addEventListener('click', (e) => {
        if (!confirm('Are you sure? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
});