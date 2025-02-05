$(document).ready(function() {
    $('#closeTable').DataTable({
        "ordering": false // Menonaktifkan fitur sortir
    });

    $('#toggleSidebar').click(function() {
        $('#sidebar').toggleClass('hidden');
        $('#content').toggleClass('expanded');
    });
});