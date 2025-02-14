$(document).on('click', '.show-more', function() {
    let shortText = $(this).prev().prev();
    let fullText = $(this).prev();

    if (fullText.is(":hidden")) {
        shortText.hide();
        fullText.css({
            "display": "block",
            "max-width": "300px", // Pastikan lebar tetap
            "max-height": "150px", // Pastikan teks tetap dalam batas
            "overflow-y": "auto" // Tambahkan scroll jika teks panjang
        }); 
        $(this).text("Show Less");
    } else {
        shortText.show();
        fullText.hide();
        $(this).text("Show More");
    }
});