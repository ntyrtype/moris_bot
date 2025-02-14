$(document).on('click', '.show-more', function() {
    let textContainer = $(this).prev();
    let shortText = textContainer.find('.short-text');
    let fullText = textContainer.find('.hidden-text');

    if (fullText.is(":hidden")) {
        shortText.hide();
        fullText.show();
        $(this).text("Show Less");
    } else {
        shortText.show();
        fullText.hide();
        $(this).text("Show More");
    }

    // Pastikan DataTables tetap menyesuaikan ukuran setelah teks diperluas
    $('#dataTable').DataTable().columns.adjust();
});
