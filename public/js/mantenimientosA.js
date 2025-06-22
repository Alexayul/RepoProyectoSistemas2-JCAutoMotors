function verDetalleMantenimiento(id) {
    console.log("Abriendo modal", id); // <-- Agrega esto
    // Muestra un spinner mientras carga
    $('#modalDetalleMantenimiento .modal-content').html(`
        <div class="modal-body text-center">
            <div class="spinner-border text-primary" role="status"></div>
            <div>Cargando...</div>
        </div>
    `);

    // Abre el modal usando Bootstrap 5
    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleMantenimiento'));
    modal.show();

    $.ajax({
        url: '../routes/mantenimiento.route.php',
        method: 'GET',
        data: { action: 'get_mantenimiento_admin', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.detalle) {
                const m = response.detalle;
                const modalContent = `
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="bi bi-tools me-2"></i> Detalles del Mantenimiento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Cliente</h6>
                                <p><strong>Nombre:</strong> ${m.nombre_cliente ?? ''}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Motocicleta</h6>
                                <p><strong>Modelo:</strong> ${m.modelo_motocicleta ?? ''}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Detalles del Mantenimiento</h6>
                                <p><strong>Fecha:</strong> ${m.fecha ? (new Date(m.fecha)).toLocaleString('es-BO') : ''}</p>
                                <p><strong>Tipo:</strong> ${m.tipo ?? ''}</p>
                                <p><strong>Costo:</strong> ${m.es_gratuito == 1 ? 'Gratuito' : (m.costo ? m.costo + ' Bs' : '')}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Realizado por</h6>
                                <p><strong>Empleado:</strong> ${m.nombre_empleado ?? ''}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6>Observaciones</h6>
                                <p>${m.observaciones ? m.observaciones : '<em>Sin observaciones adicionales</em>'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                `;
                $('#modalDetalleMantenimiento .modal-content').html(modalContent);
            } else {
                $('#modalDetalleMantenimiento .modal-content').html(`
                    <div class="modal-body">
                        <div class="alert alert-danger">No se pudo cargar el detalle.</div>
                    </div>
                `);
            }
        },
        error: function() {
            $('#modalDetalleMantenimiento .modal-content').html(`
                <div class="modal-body">
                    <div class="alert alert-danger">Error al cargar el detalle.</div>
                </div>
            `);
        }
    });
}
