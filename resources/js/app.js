(function() {
    // Attach once DOM loaded (defer attribute is set in layout)
    document.querySelectorAll('tr[data-sid]').forEach(row => {
        // Dots: present / online
        row.querySelectorAll('.status-group').forEach(group => {
            const field = group.getAttribute('data-field');
            const hidden = row.querySelector(`input[name^="items"][name$="[${field}]"]`);
            if (!hidden) return;
            group.querySelectorAll('.dot').forEach(btn => {
                btn.setAttribute('type', 'button');
                btn.addEventListener('click', () => {
                    hidden.value = btn.getAttribute('data-value');
                    group.querySelectorAll('.dot').forEach(b => b.classList.remove('dot-selected'));
                    btn.classList.add('dot-selected');
                });
            });
        });
        // Diamonds: stars
        row.querySelectorAll('.stars-group').forEach(group => {
            const hidden = row.querySelector(`input[name^="items"][name$="[stars]"]`);
            if (!hidden) return;
            group.querySelectorAll('.diamond').forEach(btn => {
                btn.setAttribute('type', 'button');
                btn.addEventListener('click', () => {
                    const v = parseInt(btn.getAttribute('data-value'), 10);
                    hidden.value = v;
                    group.querySelectorAll('.diamond').forEach(b => {
                        const k = parseInt(b.getAttribute('data-value'), 10);
                        b.classList.toggle('on', k <= v);
                    });
                });
            });
        });
    });
})();
