$(document).ready(function () {
    $("#uploadButton").click(function () {
        $("#fileInput").click(); // Trigger file input
    });

    $("#fileInput").change(function () {
        const fileInput = $("#fileInput")[0];
        if (!fileInput.files.length) {
            alert("Pilih file terlebih dahulu!");
            return;
        }

        const file = fileInput.files[0];
        if (file.type !== "text/csv" && file.type !== "application/vnd.ms-excel" && file.type !== "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            alert("File harus berupa CSV atau Excel!");
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const data = e.target.result;
            if (file.type === "text/csv") {
                Papa.parse(data, {
                    header: true,
                    delimiter: ",", // Sesuaikan dengan delimiter di file
                    quoteChar: '"', // Tangani multiline text
                    skipEmptyLines: true,
                    complete: function (results) {
                        console.log("Parsed Data:", results.data); // Debugging
                        uploadData(results.data);
                    }
                });
            } else {
                const workbook = XLSX.read(data, { type: "binary" });
                const sheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[sheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);
                console.log("Parsed Excel Data:", jsonData); // Debugging
                uploadData(jsonData);
            }
        };

        if (file.type === "text/csv") {
            reader.readAsText(file);
        } else {
            reader.readAsBinaryString(file);
        }
    });

    function uploadData(data) {
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

        $.ajax({
            url: "upload.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify(cleanedData),
            success: function (response) {
                console.log("Response:", response);
                alert(response.message);
                location.reload(); // Reload halaman setelah upload berhasil
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
                alert("Terjadi kesalahan saat mengupload file.");
            }
        });
    }
});
