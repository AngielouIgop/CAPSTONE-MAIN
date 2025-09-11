
    document.addEventListener("DOMContentLoaded", function() {
        const wastePerMaterial = <?= json_encode($wastePerMaterial) ?>;
        const labels = wastePerMaterial.map(item => item.materialType);
        const dataValues = wastePerMaterial.map(item => item.totalQuantity);

        const ctx = document.getElementById('contributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Quantity',
                    data: dataValues,
                    backgroundColor: ['#4cafef', '#81c784', '#ffb74d'],
                    borderColor: ['#1e88e5', '#388e3c', '#f57c00'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    });
</script>