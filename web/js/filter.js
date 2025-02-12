$(document).ready(function () {
    const transaksi = $("#transaksi");
    const kategori = $("#kategori");
    const filterDate = $("#filter_date");

    function applyFilter() {
        let url = new URL(window.location.href);
        let params = url.searchParams;

        if (transaksi.val()) {
            params.set("transaksi", transaksi.val());
        } else {
            params.delete("transaksi");
        }

        if (kategori.val()) {
            params.set("kategori", kategori.val());
        } else {
            params.delete("kategori");
        }

        if (filterDate.val()) {
            params.set("filter_date", filterDate.val());
        } else {
            params.delete("filter_date");
        }

        window.location.href = url.toString();
    }

    transaksi.change(applyFilter);
    kategori.change(applyFilter);
    filterDate.change(applyFilter);
});