$(document).ready(function () {
    let progressChartInstance = null;
    let categoryChartInstance = null;
    let progressTypeChartInstance = null;
    // Panggil fetchData saat halaman dimuat
    fetchData();

    // Event listener untuk filter form submission
    $("#filterForm").on("submit", function (e) {
        e.preventDefault(); // Hindari submit default
        fetchData();
    });

    function fetchData() {
        let order_by = $("#order_by").val();
        let transaksi = $("#transaksi").val();
        let kategori = $("#kategori").val();
        let start_date = $("#start_date").val();
        let end_date = $("#end_date").val();

        $.ajax({
            url: "dashboard.php",
            type: "GET",
            dataType: "json",
            data: {
                ajax: "true",
                order_by: order_by,
                transaksi: transaksi,
                kategori: kategori,
                start_date: start_date,
                end_date: end_date
            },
            success: function (response) {
                console.log("Response:", response); // Debugging

                // ✅ Update order, pickup, and close counts
                $("#sisa_order_count").text(response.sisa_order || 0);
                $("#order_count").text(response.orders_count?.Order || 0);
                $("#pickup_count").text(response.orders_count?.Pickup || 0);
                $("#close_count").text(response.orders_count?.Close || 0);

                // ✅ Update tabel produktivitas
                updateProduktifitiTable(response.produktifitiData);
                updateCharts(response);
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                $("#table-body").html("<tr><td colspan='8'>Gagal mengambil data</td></tr>");
                console.log("Response Text:", xhr.responseText);
            }
        });
    }

    
    function updateProduktifitiTable(data) {
        let tableBody = $("#table-body");
        tableBody.empty();
    
        if (!data || data.length === 0) {
            tableBody.html("<tr><td colspan='8'>Tidak ada data</td></tr>");
            return;
        }
    
        // Ambil nilai filter dari form
        let order_by = $("#order_by").val();
        let transaksi = $("#transaksi").val();
        let kategori = $("#kategori").val();
        let start_date = $("#start_date").val();
        let end_date = $("#end_date").val();
    
        // Debug: Tampilkan nilai filter di console
        console.log("Filter Values:", {
            order_by: order_by,
            transaksi: transaksi,
            kategori: kategori,
            start_date: start_date,
            end_date: end_date
        });
    
        // Loop melalui data dan buat baris tabel
        $.each(data, function (index, item) {
            // Buat URL dasar dengan nama
            let logLink = `log.php?nama=${encodeURIComponent(item.Nama)}`;
    
            // Tambahkan parameter filter jika ada nilainya
            if (order_by) logLink += `&order_by=${encodeURIComponent(order_by)}`;
            if (transaksi) logLink += `&transaksi=${encodeURIComponent(transaksi)}`;
            if (kategori) logLink += `&kategori=${encodeURIComponent(kategori)}`;
            if (start_date) logLink += `&start_date=${encodeURIComponent(start_date)}`;
            if (end_date) logLink += `&end_date=${encodeURIComponent(end_date)}`;
    
            // Debug: Tampilkan URL yang dihasilkan
            console.log("Generated Log Link:", logLink);
    
            let row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.Nama || '-'}</td>
                    <td>${item.PDA || 0}</td>
                    <td>${item.MO || 0}</td>
                    <td>${item.ORBIT || 0}</td>
                    <td>${item.FFG || 0}</td>
                    <td>${item.UNSPEK || 0}</td>
                    <td>${item.PSB || 0}</td>
                    <td>${item.RO || 0}</td>
                    <td>${item.SO || 0}</td>
                    <td>${item.DO || 0}</td>
                    <td>${item.RecordCount || 0}</td>
                    <td><a href="${logLink}">Lihat Log</a></td>
                </tr>
            `;
            tableBody.append(row);
        });
        // Inisialisasi DataTable dengan konfigurasi yang sama seperti #topProduct
        $("#produktifitiTable").DataTable({
            "pageLength": 5,
            "lengthChange": false, // Hilangkan opsi untuk ubah jumlah data per halaman
            "searching": false, // Hilangkan fitur pencarian
            "ordering": true, // Aktifkan sorting di header tabel
            "destroy": true // Hapus DataTable lama sebelum diinisialisasi ulang
        });
    }

    function updateCharts(data) {
        updateProgressChart(data.progressChart);
        updateCategoryChart(data.categoryChart);
        updateProgressTypeChart(data.progressTypeChart);
    }

    function updateProgressChart(data) {
        const ctx = document.getElementById('progressChart')?.getContext('2d');
        if (!ctx) return;

        if (progressChartInstance) progressChartInstance.destroy();

        progressChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.tanggal),
                datasets: [{
                    label: 'Total Orders per Date',
                    data: data.map(d => d.total),
                    borderColor: '#34495e',
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Total Orders per Date',
                        padding: {
                        bottom: 20
                        },
                        font: {
                        size: 17,
                        color: 'black'
                        }
                    }
                },
                responsive: true
            }
        });
    }

    function updateCategoryChart(data) {
        const ctx = document.getElementById('categoryChart')?.getContext('2d');
        if (!ctx) return;

        data.sort((a, b) => b.total - a.total);

        if (categoryChartInstance) categoryChartInstance.destroy();

        categoryChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.Kategori),
                datasets: [{
                    label: 'Total Orders by Category',
                    data: data.map(d => d.total),
                    backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#8D6E63", "#FF9F40", "#9966FF"],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Orders by Category',
                        padding: { bottom: 20 },
                        font: { size: 17, color: 'black' }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    function updateProgressTypeChart(data) {
        const ctx = document.getElementById('progressTypeChart')?.getContext('2d');
        if (!ctx) return;

        if (progressTypeChartInstance) progressTypeChartInstance.destroy();

        progressTypeChartInstance = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.map(d => d.progress_order),
                datasets: [{
                    label: 'Order Progress Status',
                    data: data.map(d => d.total),
                    backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#8D6E63", "#FF9F40", "#9966FF"],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Type of Progress',
                        padding: { bottom: 20 },
                        font: { size: 17, color: 'black' }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
});
