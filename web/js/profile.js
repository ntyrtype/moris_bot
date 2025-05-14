// Event listener untuk tombol profil
$('#profileButton').click(function() {
    // Saat tombol diklik, tampilkan atau sembunyikan konten dropdown profil
    $('#profileContent').toggle(); // Toggle profile dropdown visibility
});

// Close the profile dropdown if clicked outside
$(document).click(function(event) {
    // Jika elemen yang diklik bukan bagian dari dropdown profil
    if (!$(event.target).closest('.profile-dropdown').length) {
        // Sembunyikan konten dropdown profil
        $('#profileContent').hide();
}
});