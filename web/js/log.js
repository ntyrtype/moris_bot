function showLog(noTiket) {
    console.log("Tombol Lihat Log diklik. No Tiket:", noTiket);

    $.ajax({
        url: 'get_log.php',
        type: 'GET',
        data: { no_tiket: noTiket },
        success: function(response) {
            console.log("Response dari server:", response);

            try {
                var logs = response;
                var logContent = '';

                logs.forEach(function(log) {
                    logContent += '<p><strong>Waktu:</strong> ' + log.waktu + '</p>';
                    logContent += '<p><strong>Status:</strong> ' + log.status + '</p>';
                    logContent += '<p><strong>Progress Order:</strong> ' + log.progress_order + '</p>';
                    logContent += '<p><strong>Keterangan:</strong> ' + log.keterangan + '</p>';
                    logContent += '<p><strong>Nama:</strong> ' + log.nama + '</p>';
                    logContent += '<p><strong>Role:</strong> ' + log.role + '</p>';
                    logContent += '<hr>';
                });

                $('#logContent').html(logContent);
                $('#logModal').modal('show'); // Tampilkan modal
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching log data:', error);
        }
    });
}