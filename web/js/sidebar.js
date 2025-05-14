// Jalankan fungsi setelah seluruh dokumen siap (DOM sudah termuat)
$(document).ready(function() {  
    // Event listener untuk tombol dengan ID 'toggleSidebar'
    $('#toggleSidebar').click(function() {
        // Toggle class 'hidden' pada elemen sidebar untuk menampilkan atau menyembunyikannya
        $('#sidebar').toggleClass('hidden');

        // Toggle class 'expanded' pada konten utama agar menyesuaikan lebar saat sidebar disembunyikan
        $('#content').toggleClass('expanded');
    });
});