document.addEventListener("DOMContentLoaded", function () {
    fetch("../web/dashboard.php")
        .then(response => response.json())
        .then(data => {
            console.log("Data diterima:", data); // Debugging

            // Panggil fungsi untuk update chart
            updateProgressChart(data.progressChart);
            updateCategoryChart(data.categoryChart);
            updateProgressTypeChart(data.progressTypeChart);
        })
        .catch(error => console.error("Error fetching data:", error));
});

//Fungsi untuk Progress Chart (by Tanggal)
function updateProgressChart(data) {
    let ctx = document.getElementById("progressChart").getContext("2d");
    let labels = data.map(item => item.tanggal);
    let values = data.map(item => item.total);

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: labels,
            datasets: [{
                label: "Total Orders per Date",
                data: values,
                backgroundColor: "rgba(54, 162, 235, 0.5)",
                borderColor: "rgba(54, 162, 235, 1)",
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

//Fungsi untuk Category Chart (by Kategori)
function updateCategoryChart(data) {
    let ctx = document.getElementById("categoryChart").getContext("2d");
    let labels = data.map(item => item.Kategori);
    let values = data.map(item => item.total);

    new Chart(ctx, {
        type: "pie",
        data: {
            labels: labels,
            datasets: [{
                label: "Total Orders by Category",
                data: values,
                backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });
}

//Fungsi untuk Progress Type Chart (by Progress Order)
function updateProgressTypeChart(data) {
    let ctx = document.getElementById("progressTypeChart").getContext("2d");
    let labels = data.map(item => item.progress_order);
    let values = data.map(item => item.total);

    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: labels,
            datasets: [{
                label: "Order Progress Status",
                data: values,
                backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });
}
