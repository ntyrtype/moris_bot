$(document).ready(function() {
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
});
