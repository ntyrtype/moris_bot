$('#profileButton').click(function() {
    $('#profileContent').toggle(); // Toggle profile dropdown visibility
});

// Close the profile dropdown if clicked outside
$(document).click(function(event) {
if (!$(event.target).closest('.profile-dropdown').length) {
    $('#profileContent').hide();
}
});