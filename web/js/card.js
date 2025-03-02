$(document).ready(function () {
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
                $("#order_count").text(response.orders_count?.Order || 0);
                $("#pickup_count").text(response.orders_count?.Pickup || 0);
                $("#close_count").text(response.orders_count?.Close || 0);

                // ✅ Update tabel produktivitas
                updateProduktifitiTable(response.produktifitiData);
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                $("#table-body").html("<tr><td colspan='8'>Gagal mengambil data</td></tr>");
            }
        });
    }

    function updateProduktifitiTable(data) {
        let tableBody = $("#table-body");
        tableBody.empty(); // Kosongkan tabel sebelum mengisi ulang
    
        if (!data || data.length === 0) {
            tableBody.html("<tr><td colspan='8'>Tidak ada data</td></tr>");
            return;
        }
    
        $.each(data, function (index, item) {
            let logLink = `log.php?nama=${encodeURIComponent(item.Nama)}`;
            let row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.Nama || '-'}</td>
                    <td>${item.PDA || 0}</td>
                    <td>${item.MO || 0}</td>
                    <td>${item.ORBIT || 0}</td>
                    <td>${item.FFG || 0}</td>
                    <td>${item.UNSPEK || 0}</td>
                    <td>${item.RecordCount || 0}</td>
                    <td><a href="${logLink}">Lihat Log</a></td>
                </tr>
            `;
            tableBody.append(row);
        });
    }
});
