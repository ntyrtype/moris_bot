document.addEventListener("DOMContentLoaded", function () {
    fetch("http://localhost/moris_bot/web/dashboard.php?ajax=true")
        .then(response => response.json())
        .then(data => {
            console.log("Data diterima:", data);
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
        type: "line",
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
            plugins: {
                title: {
                    display: true,
                    text: 'Total Orders per Date',
                    padding: {
                      bottom: 20
                    },
                    font: {
                      size: 17,
                      color: 'black'
                    }
                }
            },
            responsive: true
        }
    });
}

//Fungsi untuk Category Chart (by Kategori)
function updateCategoryChart(data) {
    let ctx = document.getElementById("categoryChart").getContext("2d");
    let labels = data.map(item => item.Kategori);
    let values = data.map(item => item.total);

    new Chart(ctx, {
        type: "bar",
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
            plugins: {
                title: {
                    display: true,
                    text: 'Orders by Category',
                    padding: {
                      bottom: 20
                    },
                    font: {
                      size: 17,
                      color: 'black'
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

//Fungsi untuk Progress Type Chart (by Progress Order)
function updateProgressTypeChart(data) {
    let ctx = document.getElementById("progressTypeChart").getContext("2d");
    let labels = data.map(item => item.progress_order);
    let values = data.map(item => item.total);

    new Chart(ctx, {
        type: "pie",
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
            plugins: {
                title: {
                    display: true,
                    text: 'Type of Progress',
                    padding: {
                      bottom: 20
                    },
                    font: {
                      size: 17,
                      color: 'black'
                    }
                }
            },
            responsive: true,
            maintainAspectRatio: false
        }
    });
}
