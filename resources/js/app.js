import $ from 'jquery';
import DataTable from 'datatables.net-dt';
import Swal from 'sweetalert2';
import toastr from 'toastr';
import select2 from 'select2';

window.$ = window.jQuery = $;
window.DataTable = DataTable;
DataTable.use($);
window.Swal = Swal;
window.toastr = toastr;

// Initialize select2 plugin onto jQuery
if (typeof select2 === 'function') {
    try {
        select2(window, $);
    } catch {
        select2();
    }
}

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 4000,
    showDuration: 200,
    hideDuration: 200
};

window.confirmDelete = function (formOrEvent, message = 'Are you sure you want to delete this?') {
    if (formOrEvent && formOrEvent.preventDefault) {
        formOrEvent.preventDefault();
        const form = formOrEvent.target.closest('form');
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'font-sans',
                confirmButton: 'px-4 py-2 text-sm font-semibold',
                cancelButton: 'px-4 py-2 text-sm font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed && form) {
                // If form has ajax handler, trigger it, otherwise submit
                if (form.classList.contains('ajax-delete-form') || window.handleAjaxDelete) {
                    window.handleAjaxDelete(form);
                } else {
                    form.submit();
                }
            }
        });
        return false;
    }
};
