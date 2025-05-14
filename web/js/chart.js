// Jalankan kode saat dokumen siap
$(document).ready(function() {
    // Ambil data dari PHP (AJAX request)
    $.getJSON("", function(data) {
        // Siapkan array untuk label dan nilai chart
        let labels = [];
        let values = [];

        // Proses data dari response API
        data.forEach(item => {
            labels.push(item.kategori); // Kumpulkan label kategori
            values.push(item.total); // Kumpulkan nilai total
        });

        // **1️⃣ Tampilkan grafik batang (Bar Chart)**
        let ctx1 = document.getElementById("categoryChart").getContext("2d");
        new Chart(ctx1, {
            type: "bar",
            data: {
                labels: labels, // Label dari data kategori
                datasets: [{
                    label: "Kategori", 
                    data: values, // Nilai dari total per kategori
                    backgroundColor: "blue" // Warna batang
                }]
            }
        });

        // **2️⃣ Tampilkan Pie Chart (Jenis Progres)**
        let ctx2 = document.getElementById("progressTypeChart").getContext("2d");
        new Chart(ctx2, {
            type: "pie",
            data: {
                labels: ["In Progress", "On Rekap"], // Label hardcoded
                datasets: [{
                    data: [50, 50], // Bisa diambil dari data.php juga
                    backgroundColor: ["cyan", "blue"] // Warna slice
                }]
            }
        });

        // **3️⃣ Tampilkan grafik line (Progres by Tanggal)**
        let ctx3 = document.getElementById("progressChart").getContext("2d");
        new Chart(ctx3, {
            type: "line",
            data: {
                labels: ["12 Feb", "13 Feb"], // Bisa diambil dari data
                datasets: [{
                    label: "Record Count",
                    data: [1, 1], // Bisa diambil dari data
                    borderColor: "blue", // Warna garis
                    fill: false // Tidak ada area bawah garis
                }]
            }
        });
    });
});