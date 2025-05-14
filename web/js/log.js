function showLog(noTiket) {
    // Debug: Tampilkan di console bahwa tombol diklik
    console.log("Tombol Lihat Log diklik. No Tiket:", noTiket);

    // AJAX request untuk mengambil data log
    $.ajax({
        url: 'get_log.php', // Endpoint API
        type: 'GET', // Metode request
        data: { no_tiket: noTiket }, // Parameter yang dikirim
        success: function(response) {
            // Debug: Tampilkan response dari server
            console.log("Response dari server:", response);

            try {
                // 1️⃣ Siapkan template tabel
                var logs = response; // Asumsi response berupa array JSON
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

                // 2️⃣ Loop data log dan bangun baris tabel
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

                // 3️⃣ Update konten modal dan tampilkan
                $('#logContent').html(logContent);
                $('#logModal').modal('show'); // Tampilkan modal
            } catch (e) {
                // Handle error parsing response
                console.error("Error parsing JSON:", e);
            }
        },
        error: function(xhr, status, error) {
            // Handle error AJAX
            console.error('Error fetching log data:', error);
        }
    });
}
