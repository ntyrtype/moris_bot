$(document).ready(function() {
    $('#showKeteranganButton').click(function() {
        $('#keterangan_cancel').css('display', 'inline');
        $('#submit_cancel').css('display', 'inline');
        $(this).hide();
    });

    $('#cancelForm').submit(function() {
        var keterangan = $('#keterangan_cancel').val().trim();
        if (keterangan === '') {
            alert('Harap isi keterangan sebelum melakukan cancel.');
            return false; // Mencegah form dikirim jika kosong
        }
        return true;
    });
});
