// Event listener untuk elemen dengan class 'show-more'
// Menggunakan event delegation agar tetap bekerja pada elemen yang dimuat secara dinamis
$(document).on('click', '.show-more', function() {
    // Ambil elemen teks singkat (2 elemen sebelum tombol)
    let shortText = $(this).prev().prev();

     // Ambil elemen teks lengkap (1 elemen sebelum tombol)
    let fullText = $(this).prev();

    // Cek apakah teks lengkap sedang disembunyikan
    if (fullText.is(":hidden")) {
        // Sembunyikan teks singkat
        shortText.hide();
        // Tampilkan teks lengkap dengan batas ukuran dan scrollbar
        fullText.css({
            "display": "block",
            "max-width": "300px", // Pastikan lebar tetap
            "max-height": "150px", // Pastikan teks tetap dalam batas
            "overflow-y": "auto" // Tambahkan scroll jika teks panjang
        }); 
         // Ubah teks tombol menjadi "Show Less"
        $(this).text("Show Less");
    } else {
        // Jika teks lengkap sudah ditampilkan, kembali ke tampilan singkat
        shortText.show();
        fullText.hide();

        // Ubah teks tombol menjadi "Show More"
        $(this).text("Show More");
    }
});