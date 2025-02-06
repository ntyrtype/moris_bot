$(document).ready(function() {
    // Set filter_date to today's date when the page loads
    let today = new Date().toISOString().split('T')[0];
    $('#filter_date').val(today);

    // Trigger the filter automatically when the page loads
    $('#filter').click();

    $('#filter').click(function() {
        let filter_date = $('#filter_date').val();

        $.ajax({
            url: "dashboard.php",
            type: "GET",
            data: { ajax: "true", filter_date: filter_date },
            success: function(response) {
                console.log(response); // Debugging untuk melihat data yang diterima

                // Update order, pickup, and close counts
                $('#order_count').text((response.orders_count.Order || 0) + " Order");
                $('#pickup_count').text((response.orders_count.Pickup || 0) + " Pickup");
                $('#close_count').text((response.orders_count.Close || 0) + " Close");

                // Update transaksi counts
                let transaksiHtml = '<table border="1"><tr><th>Transaksi</th><th>Order</th><th>Pickup</th><th>Close</th></tr>';
                for (let transaksi in response.transaksi_count) {
                    transaksiHtml += `<tr>
                        <td>${transaksi}</td>
                        <td>${response.transaksi_count[transaksi].Order || 0}</td>
                        <td>${response.transaksi_count[transaksi].Pickup || 0}</td>
                        <td>${response.transaksi_count[transaksi].Close || 0}</td>
                    </tr>`;
                }
                transaksiHtml += '</table>';
                $('#transaksi_table').html(transaksiHtml);
            },
            error: function() {
                alert("Terjadi kesalahan saat mengambil data.");
            }
        });
    });

    setTimeout(() => {
        $('#filter').trigger('click');
    }, 100);
    
});