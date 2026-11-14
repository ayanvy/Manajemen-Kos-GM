

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/*
 * Tombol kirim berubah jadi "Memproses…" dan dikunci setelah form dikirim,
 * supaya tidak terkirim dua kali. Form yang dibatalkan (misalnya konfirmasi hapus
 * ditolak) tidak terpengaruh karena event-nya sudah dibatalkan.
 */
document.addEventListener('submit', (e) => {
    if (e.defaultPrevented) return;

    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    const btn = e.submitter || form.querySelector('button[type="submit"], button:not([type])');
    if (!btn) return;

    if (form.dataset.submitting === '1') {
        e.preventDefault();
        return;
    }

    form.dataset.submitting = '1';

    // Ditunda satu tick: menonaktifkan tombol di dalam event submit bisa membatalkan pengiriman.
    setTimeout(() => {
        btn.dataset.labelAsli = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = 'Memproses…';
    }, 0);
});

// Kembali lewat tombol "back" browser: pulihkan tombol yang masih terkunci.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting="1"]').forEach((form) => {
        delete form.dataset.submitting;
        form.querySelectorAll('button[data-label-asli]').forEach((btn) => {
            btn.innerHTML = btn.dataset.labelAsli;
            btn.disabled = false;
            delete btn.dataset.labelAsli;
        });
    });
});

/*
 * Kotak pencarian otomatis di atas tabel yang barisnya 5 atau lebih.
 * Memfilter baris langsung saat mengetik, tanpa memuat ulang halaman.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.kos-content table').forEach((table) => {
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        if (rows.length < 5) return;

        const wrapper = table.closest('.overflow-x-auto') || table;
        const parent = wrapper.parentElement;

        const box = document.createElement('div');
        box.className = 'kos-tablesearch';

        const input = document.createElement('input');
        input.type = 'search';
        input.className = 'kos-input';
        input.placeholder = 'Cari di tabel…';
        input.setAttribute('aria-label', 'Cari di tabel');
        box.appendChild(input);

        const kosong = document.createElement('p');
        kosong.className = 'kos-row-sub kos-tablesearch-empty';
        kosong.textContent = 'Tidak ada data yang cocok.';
        kosong.hidden = true;

        parent.insertBefore(box, wrapper);
        wrapper.after(kosong);

        input.addEventListener('input', () => {
            const kata = input.value.trim().toLowerCase();
            let cocok = 0;

            rows.forEach((row) => {
                const ada = row.textContent.toLowerCase().includes(kata);
                row.hidden = !ada;
                if (ada) cocok++;
            });

            kosong.hidden = cocok !== 0;
        });
    });
});
