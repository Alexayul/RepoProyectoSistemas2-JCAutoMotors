function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Reporte de Gestión de Ventas", 10, 10);
    
    // Agregar imágenes de los gráficos al PDF
    doc.addImage(document.getElementById('salesChart').toDataURL('image/png'), 'PNG', 10, 20, 180, 80);
    doc.addPage();
    doc.addImage(document.getElementById('incomeChart').toDataURL('image/png'), 'PNG', 10, 20, 180, 80);
    doc.addPage();
    doc.addImage(document.getElementById('expensesChart').toDataURL('image/png'), 'PNG', 10, 20, 180, 80);
    doc.addPage();
    doc.addImage(document.getElementById('pieChart').toDataURL('image/png'), 'PNG', 10, 20, 180, 80);
    
    doc.save('reporte_gestion_ventas.pdf');
}
