/**
 * RME KLINIK - Global Interactive Scripts
 * SweetAlert2 confirmation, DataTables ID defaults, Select2 AJAX, Sidebar Toggles
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle (Desktop Collapse & Mobile Offcanvas)
    const sidebarWrapper = document.getElementById('sidebar-wrapper');
    const toggleBtns = document.querySelectorAll('.nav-toggle-btn');
    const backdrop = document.querySelector('.sidebar-backdrop');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (window.innerWidth < 992) {
                sidebarWrapper.classList.toggle('mobile-show');
                if (backdrop) backdrop.classList.toggle('show');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('rme_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed'));
            }
        });
    });

    if (backdrop) {
        backdrop.addEventListener('click', function () {
            sidebarWrapper.classList.remove('mobile-show');
            backdrop.classList.remove('show');
        });
    }

    // Restore desktop collapsed state
    if (window.innerWidth >= 992 && localStorage.getItem('rme_sidebar_collapsed') === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }

    // 2. DataTables: Indonesian language, 50 rows by default and a
    //    numbers-only pager (no previous / next / first / last controls).
    if (typeof $.fn.DataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_–_END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0–0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                zeroRecords: "Tidak ditemukan data yang cocok",
                emptyTable: "Tidak ada data tersedia pada tabel ini",
                paginate: {
                    first: "Awal",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Akhir"
                }
            },
            pageLength: 50,
            lengthMenu: [[25, 50, 100, 250], [25, 50, 100, 250]],
            pagingType: "numbers",
            order: [],
            searchDelay: 300,
            autoWidth: false
        });

        $('.datatable, .datatable-responsive').each(function () {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable();
            }
        });

        // 2b. DataTables has no built-in ellipsis for pagingType "numbers", so it
        //     renders every page number. Window the list down to a compact set of
        //     numbers with a purely visual "…" — never arrows, never prev/next.
        $(document).on('draw.dt', function (event, settings) {
            rmeWindowDataTablesPagination(settings);
        });
    }

    // 3. Initialize Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: $(this).data('placeholder') || 'Pilih salah satu...'
        });
    }

    // 4. Keep the chosen rows-per-page across filter forms and same-page links
    rmePreservePerPage();
});

/**
 * Keep the "per_page" query parameter alive while a user refilters or moves
 * between the tabs of the current list page. Page 1 is always used after a
 * filter change, so a stale page number can never produce an empty table.
 */
function rmePreservePerPage() {
    const current = new URLSearchParams(window.location.search);
    const perPage = current.get('per_page');

    if (!perPage) {
        return;
    }

    document.querySelectorAll('form[method="GET" i]').forEach(function (form) {
        if (form.querySelector('[name="per_page"]')) {
            return;
        }

        form.addEventListener('submit', function () {
            if (form.querySelector('[name="per_page"]')) {
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'per_page';
            input.value = perPage;
            form.appendChild(input);
        });
    });

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');

        if (!link) {
            return;
        }

        const raw = link.getAttribute('href');

        if (!raw || raw.charAt(0) === '#' || /^javascript:/i.test(raw)) {
            return;
        }

        if (link.hasAttribute('data-bs-toggle') || link.hasAttribute('data-dismiss') || link.hasAttribute('onclick')) {
            return;
        }

        let url;

        try {
            url = new URL(raw, window.location.origin);
        } catch (error) {
            return;
        }

        if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) {
            return;
        }

        if (url.searchParams.get('per_page')) {
            return;
        }

        url.searchParams.set('per_page', perPage);
        link.href = url.toString();
    }, true);
}

/**
 * Window the DataTables pager down to a compact run of page numbers
 * (first, current ± 2, last) separated by a purely visual ellipsis.
 * The current page is always visible and no arrow control is ever added.
 *
 * @param {object} settings DataTables draw settings
 */
function rmeWindowDataTablesPagination(settings) {
    if (!settings || !settings.nTableWrapper) {
        return;
    }

    const paginate = settings.nTableWrapper.querySelector('.dataTables_paginate');

    if (!paginate) {
        return;
    }

    // Drop whatever the previous draw produced before rebuilding the window.
    Array.prototype.forEach.call(paginate.querySelectorAll('.rme-dt-ellipsis'), function (el) {
        el.parentNode.removeChild(el);
    });

    const list = paginate.querySelector('ul.pagination') || paginate;
    const items = Array.prototype.slice.call(list.children).filter(function (el) {
        return el.nodeType === 1;
    });

    items.forEach(function (el) {
        el.style.display = '';
    });

    if (items.length <= 7) {
        return;
    }

    let currentIndex = -1;

    items.forEach(function (el, index) {
        if (el.classList.contains('current') || el.querySelector('.current')) {
            currentIndex = index;
        }
    });

    if (currentIndex < 0) {
        return;
    }

    const lastIndex = items.length - 1;
    const windowSize = 2;
    const visible = { 0: true, [lastIndex]: true };

    for (let index = currentIndex - windowSize; index <= currentIndex + windowSize; index++) {
        if (index >= 0 && index <= lastIndex) {
            visible[index] = true;
        }
    }

    let hiddenRunStart = -1;

    for (let index = 0; index <= lastIndex; index++) {
        if (visible[index]) {
            if (hiddenRunStart !== -1) {
                rmeInsertPaginationEllipsis(list, items[hiddenRunStart]);
                hiddenRunStart = -1;
            }
        } else {
            items[index].style.display = 'none';

            if (hiddenRunStart === -1) {
                hiddenRunStart = index;
            }
        }
    }
}

/**
 * Insert a non-interactive "…" marker in front of a hidden run of page numbers.
 *
 * @param {Element} list Pagination list or container
 * @param {Element} reference Element the marker is placed before
 */
function rmeInsertPaginationEllipsis(list, reference) {
    const isList = list.tagName === 'UL';
    const ellipsis = document.createElement(isList ? 'li' : 'span');

    ellipsis.className = isList
        ? 'page-item disabled rme-dt-ellipsis'
        : 'paginate_button rme-dt-ellipsis';
    ellipsis.setAttribute('aria-hidden', 'true');
    ellipsis.textContent = '…';

    list.insertBefore(ellipsis, reference);
}

/**
 * SweetAlert2 Confirmation Dialogs
 */
function confirmAction(options) {
    Swal.fire({
        title: options.title || 'Apakah Anda yakin?',
        text: options.text || 'Tindakan ini tidak dapat dibatalkan!',
        icon: options.icon || 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2EACF0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmButtonText || 'Ya, Lanjutkan!',
        cancelButtonText: options.cancelButtonText || 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed && typeof options.onConfirm === 'function') {
            options.onConfirm();
        }
    });
}

function confirmDelete(formId, itemName) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus ${itemName || 'data ini'}? Tindakan ini bersifat permanen!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function confirmFinalize(formId, message) {
    Swal.fire({
        title: 'Finalisasi Rekam Medis',
        text: message || 'Setelah difinalisasi, catatan medis ini akan dikunci dan hanya dapat diubah melalui Addendum!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Finalisasi & Kunci!',
        cancelButtonText: 'Periksa Kembali',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function confirmDispense(formId) {
    Swal.fire({
        title: 'Konfirmasi Penyerahan Obat',
        text: 'Sistem akan memotong stok batch obat sesuai metode FEFO dan mengirim rincian tagihan ke kasir!',
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#2EACF0',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Serahkan Obat!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function formatRupiah(amount) {
    return 'Rp' + new Intl.NumberFormat('id-ID').format(amount);
}
