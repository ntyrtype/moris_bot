$(document).on('click', '.show-more', function() {
    let shortText = $(this).prev().prev();
    let fullText = $(this).prev();

    if (fullText.is(":hidden")) {
        shortText.hide();
        fullText.show();
        $(this).text("Show Less");
    } else {
        shortText.show();
        fullText.hide();
        $(this).text("Show More");
    }
});