// Fungsi utama yang dijalankan saat dokumen siap
$(document).ready(function() {
    // Event handler untuk tombol tampilkan form cancel
    $('#showKeteranganButton').click(function() {
        // Menampilkan input keterangan dan tombol submit
        $('#keterangan_cancel').css('display', 'inline');
        $('#submit_cancel').css('display', 'inline');
        // Menyembunyikan tombol trigger setelah diklik
        $(this).hide();
    });

    // Event handler untuk validasi form cancel
    $('#cancelForm').submit(function() {
        // Mengambil nilai input keterangan dan menghapus spasi kosong
        var keterangan = $('#keterangan_cancel').val().trim();

        // Validasi input kosong
        if (keterangan === '') {
            // Tampilkan peringatan dan cegah pengiriman form
            alert('Harap isi keterangan sebelum melakukan cancel.');
            return false; // Mencegah form dikirim jika kosong
        }
         // Lanjutkan proses submit jika validasi berhasil
        return true;
    });
});
