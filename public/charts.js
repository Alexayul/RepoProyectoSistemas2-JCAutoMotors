const salesData = {
    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
    datasets: [{
        label: 'Ventas de Motos',
        data: [15, 20, 10, 30, 25, 40],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const incomeData = {
    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
    datasets: [{
        label: 'Ingresos',
        data: [5000, 7000, 8000, 6000, 9000, 10000],
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
    }]
};

const expensesData = {
    labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
    datasets: [{
        label: 'Egresos',
        data: [2000, 3000, 2500, 4000, 3500, 4500],
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
    }]
};

const pieData = {
    labels: ['Ventas', 'Ingresos', 'Egresos'],
    datasets: [{
        data: [40, 35, 25],
        backgroundColor: [
            'rgba(54, 162, 235, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(255, 99, 132, 0.2)'
        ],
        borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 99, 132, 1)'
        ],
        borderWidth: 1
    }]
};

// Renderizar los gráficos
new Chart(document.getElementById('salesChart'), { type: 'bar', data: salesData, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
new Chart(document.getElementById('incomeChart'), { type: 'line', data: incomeData, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
new Chart(document.getElementById('expensesChart'), { type: 'line', data: expensesData, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } } });
new Chart(document.getElementById('pieChart'), { type: 'pie', data: pieData, options: { responsive: true, maintainAspectRatio: false } });
