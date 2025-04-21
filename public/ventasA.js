// Función para abrir el modal de nueva venta
function abrirModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalVenta'));
    modal.show();
}

function verDetalle(idVenta) {
    // Hacer una petición AJAX para obtener los detalles
    $.ajax({
        url: 'ventasA.php',
        method: 'GET',
        data: { 
    id_venta: idVenta,
    action: 'get_details' // Parámetro adicional para identificar la acción
},
        dataType: 'json',
        success: function(response) {
            let modalHTML = `
                <div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-receipt me-2"></i> Detalle de Venta #${idVenta}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6>Información de la Venta</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Fecha:</th>
                                                <td>${response.venta.fecha_venta}</td>
                                            </tr>
                                            <tr>
                                                <th>Cliente:</th>
                                                <td>${response.venta.nombre_cliente}</td>
                                            </tr>
                                            <tr>
                                                <th>Tipo Pago:</th>
                                                <td>${response.venta.tipo_pago}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Resumen Financiero</h6>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Monto Total:</th>
                                                <td>Bs. ${parseFloat(response.venta.monto_total).toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <th>Adelanto:</th>
                                                <td>Bs. ${parseFloat(response.venta.adelanto).toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <th>Saldo Pendiente:</th>
                                                <td>Bs. ${parseFloat(response.venta.saldo_pendiente).toFixed(2)}</td>
                                            </tr>
                                            <tr>
                                                <th>Estado:</th>
                                                <td><span class="badge bg-${response.venta.estado == 'COMPLETADA' ? 'success' : 'warning'}">${response.venta.estado}</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <h6>Productos Vendidos</h6>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Tipo</th>
                                            <th>Precio Unitario</th>
                                            <th>Cantidad</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
            
            response.productos.forEach(producto => {
                modalHTML += `
                    <tr>
                        <td>${producto.nombre_producto}</td>
                        <td><span class="badge bg-${producto.tipo_producto == 'motocicleta' ? 'primary' : 'info'}">${producto.tipo_producto}</span></td>
                        <td>Bs. ${parseFloat(producto.precio_unitario).toFixed(2)}</td>
                        <td>${producto.cantidad}</td>
                        <td>Bs. ${parseFloat(producto.subtotal).toFixed(2)}</td>
                    </tr>`;
            });
            
            modalHTML += `
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle me-1"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            
            // Agregar el modal al DOM y mostrarlo
            $('body').append(modalHTML);
            const modal = new bootstrap.Modal(document.getElementById('modalDetalleVenta'));
            modal.show();
            
            // Eliminar el modal cuando se cierre
            $('#modalDetalleVenta').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        },
        error: function() {
            alert('Error al obtener los detalles de la venta');
        }
    });
}


// Función para completar una venta pendiente
function completarVenta(idVenta) {
    if (confirm('¿Está seguro que desea marcar esta venta como COMPLETADA?')) {
        $.ajax({
            url: 'ventasA.php',
            type: 'POST',
            data: { id_venta: idVenta },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error al completar la venta: ' + error);
            }
        });
    }
}

// Habilitar campos de cantidad cuando se selecciona un producto
$(document).on('change', '.producto-check', function() {
    const productoId = $(this).val();
    const cantidadInput = $(`input[name="cantidad[${productoId}]"]`);
    
    if ($(this).is(':checked')) {
        cantidadInput.prop('disabled', false);
    } else {
        cantidadInput.prop('disabled', true).val(1);
    }
    
    actualizarResumen();
});

// Actualizar resumen cuando cambia la cantidad
$(document).on('change', '.cant-input', function() {
    actualizarResumen();
});

// Función para actualizar el resumen de la venta
function actualizarResumen() {
    let html = '';
    let total = 0;
    
    $('.producto-check:checked').each(function() {
        const productoId = $(this).val();
        const nombre = $(this).data('nombre');
        const precio = parseFloat($(this).data('precio'));
        const cantidad = parseInt($(`input[name="cantidad[${productoId}]"]`).val());
        const subtotal = precio * cantidad;
        total += subtotal;
        
        html += `
            <div class="d-flex justify-content-between mb-2">
                <span>${nombre} (x${cantidad})</span>
                <strong>Bs. ${subtotal.toFixed(2)}</strong>
            </div>`;
    });
    
    if (html === '') {
        html = '<p class="text-muted">Seleccione productos para ver el resumen</p>';
    }
    
    $('#resumenVenta').html(html);
    $('#totalVenta').text('Bs. ' + total.toFixed(2));
}
