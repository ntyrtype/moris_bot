$(document).ready(function() {
    // Inisialisasi DataTables
    const table = $('#dataTable').DataTable({
        "scrollX": true, // Aktifkan scroll horizontal
        "ordering": false,  // Nonaktifkan fitur sorting
        "columnDefs": [ 
            {   targets: 5, // Target kolom ke-5 (indeks dimulai dari 0)
                width: "300px", // Atur lebar tetap
                className: "text-truncate" } // Tetapkan lebar kolom "Keterangan"
        ],
    });

    // Atur CSS untuk tabel
    $('#dataTable').css({
        "table-layout": "fixed", // Gunakan fixed table layout
        "width": "100%"  // Lebar tabel 100%
    });

    // Fungsi untuk membersihkan teks dari HTML
    function extractTextFromHTML(html) {
        const temp = document.createElement("div");
        temp.innerHTML = html; // Parse string HTML ke DOM

        // Cari elemen dengan class "hidden-text"
        const hiddenTextElement = temp.querySelector(".hidden-text");
        if (hiddenTextElement) {
            return hiddenTextElement.textContent.trim(); // Ambil hanya teks dari hidden-text
        }

        // Jika tidak ada hidden-text, ambil teks tanpa elemen anak (misal tombol "Show More")
        return temp.cloneNode(true).textContent.trim();
    }

    // Fungsi untuk mengunduh Excel
    function downloadExcel() {
        // Ambil semua data dari DataTables (termasuk yang tidak ditampilkan)
        const allData = table.rows().data();

        // Buat array untuk menyimpan data
        const data = [];

        // Tambahkan header tabel ke dalam array
        const header = [];
        $('#dataTable thead th').each(function() {
            header.push($(this).text());
        });
        data.push(header);

        // Loop melalui semua data
        allData.each(function(rowData,) {
            const rowArray = [];
            for (let key in rowData) {
                // Jika kolom adalah "Keterangan", ekstrak dan bersihkan teks dari HTML
                if (/<[a-z][\s\S]*>/i.test(rowData[key])) { // Sesuaikan dengan indeks kolom "Keterangan"
                    rowArray.push(extractTextFromHTML(rowData[key]));
                } else {
                    rowArray.push(rowData[key]);
                }
            }
            data.push(rowArray);
        });

        // Buat worksheet dari data
        const ws = XLSX.utils.aoa_to_sheet(data);

        // Buat workbook dan tambahkan worksheet
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Orders");

        // Export ke file Excel
        XLSX.writeFile(wb, "orders.xlsx");
    }

    // Tambahkan event listener ke tombol download
    $('#downloadButton').on('click', downloadExcel);
});