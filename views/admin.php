<php?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Animate.css para algunas animaciones predefinidas -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Racing+Sans+One&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="/RepoProyectoSistemas2-JCAutoMotors/public/admin.css">
</head>
<body>
<header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="bi bi-list text-light fs-2"></i>
                    </span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="bi bi-house-door me-1"></i>Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="bi bi-speedometer2 me-1"></i>Administración
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="gestionEmpleados.php">
                                <i class="bi bi-people me-1"></i>Gestionar Empleados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="catalogo.php">
                                <i class="bi bi-bicycle me-1"></i>Catálogo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../public/logout.php">
                            <i class="bi bi-box-arrow-in-left"></i>Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <header>
        <h1>Panel de Administrador</h1>
    </header>
    <main>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="incomeChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="expensesChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="pieChart"></canvas>
        </div>
        <button onclick="generatePDF()">Generar Reporte PDF</button>
    </main>
    <script>
        // Datos de ejemplo para los gráficos
        const salesData = {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
            datasets: [{
                label: 'Ventas de Motos',
                data: [15, 20, 10, 30, 25, 40],
                backgroundColor: 'rgb(146, 13, 13)',
                borderColor: 'rgb(0, 0, 0)',
                borderWidth: 1
            }]
        };

        const incomeData = {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
            datasets: [{
                label: 'Ingresos',
                data: [5000, 7000, 8000, 6000, 9000, 10000],
                backgroundColor: 'rgb(80, 80, 80)',
                borderColor: 'rgb(0, 0, 0)',
                borderWidth: 1
            }]
        };

        const expensesData = {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'],
            datasets: [{
                label: 'Egresos',
                data: [2000, 3000, 2500, 4000, 3500, 4500],
                backgroundColor: 'rgb(219, 32, 11)',
                borderColor: 'rgb(219, 32, 11)',
                borderWidth: 1
            }]
        };

        const pieData = {
            labels: ['Ventas', 'Ingresos', 'Egresos'],
            datasets: [{
                data: [40, 35, 25],
                backgroundColor: [
                    'rgb(146, 13, 13)',
                    'rgb(80, 80, 80)',
                    'rgb(219, 32, 11)'
                ],
                borderColor: [
                    'rgb(0, 0, 0)',
                    'rgb(0, 0, 0)',
                    'rgb(0, 0, 0)'
                ],
                borderWidth: 1
            }]
        };

        // Configuración de los gráficos
        const salesConfig = {
            type: 'bar',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const incomeConfig = {
            type: 'line',
            data: incomeData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const expensesConfig = {
            type: 'line',
            data: expensesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const pieConfig = {
            type: 'pie',
            data: pieData,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        };

        // Renderizar los gráficos
        const salesChart = new Chart(document.getElementById('salesChart'), salesConfig);
        const incomeChart = new Chart(document.getElementById('incomeChart'), incomeConfig);
        const expensesChart = new Chart(document.getElementById('expensesChart'), expensesConfig);
        const pieChart = new Chart(document.getElementById('pieChart'), pieConfig);

        // Función para generar el PDF
        function generatePDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("Reporte de Gestión de Ventas", 10, 10);
            doc.addImage(salesChart.toBase64Image(), 'PNG', 10, 20, 180, 80);
            doc.addPage();
            doc.addImage(incomeChart.toBase64Image(), 'PNG', 10, 20, 180, 80);
            doc.addPage();
            doc.addImage(expensesChart.toBase64Image(), 'PNG', 10, 20, 180, 80);
            doc.addPage();
            doc.addImage(pieChart.toBase64Image(), 'PNG', 10, 20, 180, 80);
            doc.save('reporte_gestion_ventas.pdf');
        }
    </script>
    <!-- Footer -->
    <footer>
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="footer-brand">JCAutomotors</div>
                        <p>Tu concesionario de confianza con más de 15 años de experiencia en el mundo de las motocicletas.</p>
                        <div class="social-links">
                            <a href="#"><i class="bi bi-facebook"></i></a>
                            <a href="#"><i class="bi bi-twitter"></i></a>
                            <a href="#"><i class="bi bi-instagram"></i></a>
                            <a href="#"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4 footer-links">
                        <h5>Enlaces rápidos</h5>
                        <ul>
                            <li><a href="/">Inicio</a></li>
                            <li><a href="/catalogo">Catálogo</a></li>
                            <li><a href="/nosotros">Sobre nosotros</a></li>
                            <li><a href="/contacto">Contacto</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-4 footer-links">
                        <h5>Servicios</h5>
                        <ul>
                            <li><a href="/servicios/financiamiento">Financiamiento</a></li>
                            <li><a href="/servicios/mantenimiento">Mantenimiento</a></li>
                            <li><a href="/servicios/seguro">Seguros</a></li>
                            <li><a href="/servicios/accesorios">Accesorios</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-4 footer-links">
                        <h5>Contacto</h5>
                        <ul>
                            <li><i class="bi bi-geo-alt me-2"></i> Av. Tejada Sorzano entre Calles Puerto Rico y Costa Rica #855. Edif. Dica, La Paz, Bolivia</li>
                            <li><i class="bi bi-telephone me-2"></i> (591) 77530498</li>
                            <li><i class="bi bi-envelope me-2"></i> jcautomotors2@gmail.com</li>
                            <li><i class="bi bi-clock me-2"></i> Lun-Sáb: 8:00 - 18:00</li>
                            <li><i class="bi bi-clock me-2"></i> Sáb: 8:00 - 12:00</li>
                        </ul>
                    </div>
                </div>
                <div class="copyright">
                    <p>&copy; 2025 JCAutomotors. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    
        <!-- Bootstrap Bundle with Popper -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="public/index.js"></script>
        
</body>
</html>
</php>