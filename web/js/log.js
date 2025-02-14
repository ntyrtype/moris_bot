function showLog(noTiket) {
    let logContent = document.getElementById("logContent");
    logContent.innerHTML = "<p>Loading...</p>";

    fetch("get_log.php?no_tiket=" + encodeURIComponent(noTiket))
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                logContent.innerHTML = "<p style='color:red'>" + data.error + "</p>";
            } else if (data.message) {
                logContent.innerHTML = "<p>" + data.message + "</p>";
            } else {
                let logHTML = "<ul>";
                data.forEach(log => {
                    logHTML += `<li>${log.log_aktivitas} <br><small><i>${log.waktu_log}</i></small></li>`;
                });
                logHTML += "</ul>";
                logContent.innerHTML = logHTML;
            }

            // Tampilkan modal Bootstrap
            let myModal = new bootstrap.Modal(document.getElementById("logModal"));
            myModal.show();
        })
        .catch(error => {
            logContent.innerHTML = "<p style='color:red'>Terjadi kesalahan saat mengambil data.</p>";
            console.error("Error:", error);
        });
}
