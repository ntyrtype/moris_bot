$(document).ready(function() {
    // Set default date to today
    let today = new Date().toISOString().split('T')[0];
    $('#start_date').val(today);
    $('#end_date').val(today);

    // Trigger the filter automatically when the page loads
    fetchData();

    // Event listener for filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault(); // Prevent form from submitting the traditional way
        fetchData();
    });

    function fetchData() {
        let transaksi = $('#transaksi').val();
        let kategori = $('#kategori').val();
        let start_date = $('#start_date').val();
        let end_date = $('#end_date').val();

        $.ajax({
            url: "dashboard.php",
            type: "GET",
            data: {
                ajax: "true",
                transaksi: transaksi,
                kategori: kategori,
                start_date: start_date,
                end_date: end_date
            },
            success: function(response) {
                console.log(response); // Debugging to see the received data

                // Update order, pickup, and close counts
                $('#order_count').text((response.orders_count.Order || 0) + " Order");
                $('#pickup_count').text((response.orders_count.Pickup || 0) + " Pickup");
                $('#close_count').text((response.orders_count.Close || 0) + " Close");
            },
            error: function() {
                alert("Terjadi kesalahan saat mengambil data.");
            }
        });
    }
});