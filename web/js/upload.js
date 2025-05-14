// Jalankan saat DOM siap
$(document).ready(function () {
    // Ketika tombol upload diklik, trigger elemen input file secara tersembunyi
    $("#uploadButton").click(function () {
        $("#fileInput").click(); // Trigger file input
    });

    // Event saat file dipilih
    $("#fileInput").change(function () {
        const fileInput = $("#fileInput")[0];

        // Validasi: pastikan ada file yang dipilih
        if (!fileInput.files.length) {
            alert("Pilih file terlebih dahulu!");
            return;
        }

        const file = fileInput.files[0];
        // Validasi: hanya izinkan file CSV atau Excel
        if (file.type !== "text/csv" && file.type !== "application/vnd.ms-excel" && file.type !== "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            alert("File harus berupa CSV atau Excel!");
            return;
        }

        const reader = new FileReader();
        // Fungsi ketika file selesai dibaca
        reader.onload = function (e) {
            const data = e.target.result;
            // Jika file CSV, gunakan PapaParse untuk parsing
            if (file.type === "text/csv") {
                Papa.parse(data, {
                    header: true, // Gunakan baris pertama sebagai header
                    delimiter: ",", // Sesuaikan dengan delimiter di file
                    quoteChar: '"', // Tangani multiline text
                    skipEmptyLines: true, // Abaikan baris kosong
                    complete: function (results) {
                        console.log("Parsed Data:", results.data); // Debugging
                        uploadData(results.data); // Kirim data ke server
                    }
                });
            } else {
                // Jika file Excel, gunakan SheetJS untuk parsing
                const workbook = XLSX.read(data, { type: "binary" });
                const sheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[sheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);
                console.log("Parsed Excel Data:", jsonData); // Debugging
                uploadData(jsonData); // Kirim data ke server
            }
        };

        // Baca file sesuai jenisnya
        if (file.type === "text/csv") {
            reader.readAsText(file);
        } else {
            reader.readAsBinaryString(file);
        }
    });

    // Fungsi untuk membersihkan dan mengirim data ke server
    function uploadData(data) {
        // Validasi: file tidak boleh kosong
        if (data.length < 1) {
            alert("File tidak boleh kosong!");
            return;
        }

        // Bersihkan data sebelum dikirim
        const cleanedData = data.map(row => ({
            order_id: row["Order_ID"]?.trim() || "",
            transaksi: row["Transaksi"]?.trim() || "",
            kategori: row["Kategori"]?.trim() || "",
            keterangan: row["Keterangan"]?.trim() || "",
            status: row["Status"]?.trim() || "",
            order_by: row["Order_By"]?.trim() || "",
        }));

        // Kirim data ke server via AJAX
        $.ajax({
            url: "upload.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(cleanedData),
            success: function (response) {
                console.log("Response:", response); // Debug respons
                alert(response.message); // Tampilkan pesan dari server
                location.reload(); // Reload halaman setelah upload berhasil
            },
            error: function (xhr, status, error) {
                console.error("Error:", error); // Debug error
                alert("Terjadi kesalahan saat mengupload file.");
            }
        });
    }
});
