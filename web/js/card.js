$(document).ready(function() {
    // Set filter_date ke tanggal hari ini
    let today = new Date().toISOString().split('T')[0];
    $('#filter_date').val(today);

    // Event listener untuk filter
    $('#filter').click(function() {
        let filter_date = $('#filter_date').val();

        $.ajax({
            url: "dashboard.php",
            type: "GET",
            data: { ajax: "true", filter_date: filter_date },
            success: function(response) {
                console.log(response); // Debugging untuk melihat data yang diterima

                $('#order_count').text((response.Order || 0) + " Order");
                $('#pickup_count').text((response.Pickup || 0) + " Pickup");
                $('#close_count').text((response.Close || 0) + " Close");
            },
            error: function() {
                alert("Terjadi kesalahan saat mengambil data.");
            }
        });
    });

    // Pastikan klik pertama hanya terjadi setelah event listener dipasang
    setTimeout(() => {
        $('#filter').trigger('click');
    }, 100);
});
