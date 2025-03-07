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
                var logContent = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Keterangan</th>
                                <th>Nama</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                logs.forEach(function(log) {
                    logContent += `
                        <tr>
                            <td>${log.tanggal}</td>
                            <td>${log.status}</td>
                            <td>${log.progress_order}</td>
                            <td>${log.keterangan}</td>
                            <td>${log.nama}</td>
                            <td>${log.role}</td>
                        </tr>
                    `;
                });

                logContent += `</tbody></table>`;

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
