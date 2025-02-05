$(document).ready(function() {
    $('#toggleSidebar').click(function() {
        $('#sidebar').toggleClass('hidden');
        $('#content').toggleClass('expanded');
    });
});