// log.js
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
                var logTableBody = $('#logTable tbody');
                logTableBody.empty(); // Kosongkan tabel sebelum menambahkan data baru

                logs.forEach(function(log) {
                    var row = '<tr>' +
                        '<td>' + log.waktu + '</td>' +
                        '<td>' + log.status + '</td>' +
                        '<td>' + log.progress_order + '</td>' +
                        '<td>' + log.keterangan + '</td>' +
                        '<td>' + log.nama + '</td>' +
                        '<td>' + log.role + '</td>' +
                        '</tr>';
                    logTableBody.append(row);
                });

                $('#logSection').show(); // Tampilkan tabel log jika tersembunyi
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching log data:', error);
        }
    });
}
