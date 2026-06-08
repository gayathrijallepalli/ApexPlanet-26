(function () {
    const chartOptions = {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    };

    fetch(SHP_BASE_URL + '/ajax/admin_chart_data.php')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const d = data.charts;

            if (document.getElementById('userGrowthChart')) {
                new Chart(document.getElementById('userGrowthChart'), {
                    type: 'line',
                    data: { labels: d.userGrowth.labels, datasets: [{ data: d.userGrowth.data, borderColor: '#2563EB', tension: 0.3, fill: false }] },
                    options: chartOptions
                });
            }
            if (document.getElementById('appsChart')) {
                new Chart(document.getElementById('appsChart'), {
                    type: 'bar',
                    data: { labels: d.applicationsPerMonth.labels, datasets: [{ data: d.applicationsPerMonth.data, backgroundColor: '#14B8A6' }] },
                    options: chartOptions
                });
            }
            if (document.getElementById('jobsChart')) {
                new Chart(document.getElementById('jobsChart'), {
                    type: 'bar',
                    data: { labels: d.jobsPerMonth.labels, datasets: [{ data: d.jobsPerMonth.data, backgroundColor: '#2563EB' }] },
                    options: chartOptions
                });
            }
            if (document.getElementById('topCompaniesChart')) {
                new Chart(document.getElementById('topCompaniesChart'), {
                    type: 'bar',
                    data: { labels: d.topCompanies.labels, datasets: [{ data: d.topCompanies.data, backgroundColor: '#0F172A' }] },
                    options: { ...chartOptions, indexAxis: 'y' }
                });
            }
        });
})();
