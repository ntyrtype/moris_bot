$(document).ready(function() {
    function downloadExcel() {
        // Ambil data dari tabel
        const table = document.getElementById("dataTable");
        const rows = table.querySelectorAll("tbody tr");

        // Buat array untuk menyimpan data
        const data = [];

        // Tambahkan header tabel ke dalam array
        const header = [];
        table.querySelectorAll("thead th").forEach(th => {
            header.push(th.innerText);
        });
        data.push(header);

        // Loop melalui setiap baris dan kolom
        rows.forEach(row => {
            const rowData = [];
            const cols = row.querySelectorAll("td");

            cols.forEach(col => {
                let hiddenText = col.querySelector(".hidden-text");
                let text = hiddenText ? hiddenText.innerText : col.innerText; 
                rowData.push(text.trim());
            });
            ;

            data.push(rowData);
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
