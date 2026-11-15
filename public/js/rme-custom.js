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

    // 2. Initialize DataTables with Indonesian Language
    if (typeof $.fn.DataTable !== 'undefined') {
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
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
            responsive: true,
            pageLength: 15
        });

        $('.datatable').each(function () {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable();
            }
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
});

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
