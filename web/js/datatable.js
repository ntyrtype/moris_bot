$(document).ready(function() {
    $('#dataTable').DataTable({
        "scrollX": true,
        "ordering": false, // Menonaktifkan fitur sortir
        "autoWidth": false,
        "columnDefs": [
            { targets: 5, width: "300px" ,className: "text-truncate" } // Tetapkan lebar kolom "Keterangan"
        ],
    });

    $('#dataTable').css({
        "table-layout": "fixed",
        "width": "100%"
    });
});