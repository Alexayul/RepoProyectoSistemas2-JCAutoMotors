        // Modal functions
        function abrirModal() {
            var modal = new bootstrap.Modal(document.getElementById('modalVenta'));
            modal.show();
        }

        function cerrarModal() {
            var modal = bootstrap.Modal.getInstance(document.getElementById('modalVenta'));
            modal.hide();
        }

        // Habilitar/deshabilitar inputs de cantidad según checkbox
        $(document).on('change', '.producto-check', function() {
            const productoId = $(this).val();
            const cantidadInput = $(`input[data-producto="${productoId}"]`);
            
            if ($(this).is(':checked')) {
                cantidadInput.prop('disabled', false);
            } else {
                cantidadInput.prop('disabled', true);
                cantidadInput.val(1);
            }
            
            actualizarResumen();
        });

        // Actualizar resumen de venta
        function actualizarResumen() {
            let productosSeleccionados = [];
            let total = 0;
            
            $('.producto-check:checked').each(function() {
                const productoId = $(this).val();
                const nombre = $(this).data('nombre');
                const precio = parseFloat($(this).data('precio'));
                const cantidad = parseInt($(`input[data-producto="${productoId}"]`).val());
                const subtotal = precio * cantidad;
                const tipo = $(this).data('tipo');
                
                productosSeleccionados.push({
                    nombre: nombre,
                    cantidad: cantidad,
                    precio: precio,
                    subtotal: subtotal,
                    tipo: tipo
                });
                
                total += subtotal;
            });
            
            // Actualizar el resumen
            if (productosSeleccionados.length > 0) {
                let html = '<table class="table table-sm">';
                html += '<thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>';
                html += '<tbody>';
                
                productosSeleccionados.forEach(producto => {
                    html += `<tr>
                        <td>${producto.nombre} <span class="badge bg-${producto.tipo === 'motocicleta' ? 'primary' : 'info'}">${producto.tipo}</span></td>
                        <td>${producto.cantidad}</td>
                        <td>Bs. ${producto.precio.toFixed(2)}</td>
                        <td>Bs. ${producto.subtotal.toFixed(2)}</td>
                    </tr>`;
                });
                
                html += '</tbody></table>';
                $('#resumenVenta').html(html);
            } else {
                $('#resumenVenta').html('<p class="text-muted">Seleccione productos para ver el resumen</p>');
            }
            
            // Actualizar total
            $('#totalVenta').text(`Bs. ${total.toFixed(2)}`);
        }

        // Actualizar resumen cuando cambia la cantidad
        $(document).on('change', '.cant-input', function() {
            actualizarResumen();
        });

        // Validar formulario antes de enviar
        $('#formVenta').on('submit', function(e) {
            if ($('.producto-check:checked').length === 0) {
                e.preventDefault();
                alert('Debe seleccionar al menos un producto');
                return false;
            }
            
            const adelanto = parseFloat($('input[name="adelanto"]').val());
            const total = parseFloat($('#totalVenta').text().replace('Bs. ', ''));
            
            if (isNaN(adelanto) || adelanto < 0) {
                e.preventDefault();
                alert('El adelanto debe ser un número positivo');
                return false;
            }
            
            if (adelanto > total) {
                e.preventDefault();
                alert('El adelanto no puede ser mayor al total');
                return false;
            }
            
            return true;
        });

        // Filtro de búsqueda en la tabla de ventas
        $("#filtro").on("keyup", function() {
            const value = $(this).val().toLowerCase();
            $("#tablaVentas tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Filtrar por estado
        $("#filtroEstado").on("change", function() {
            const estado = $(this).val();
            
            if (estado === "") {
                $("#tablaVentas tr").show();
            } else {
                $("#tablaVentas tr").each(function() {
                    const estadoFila = $(this).find("td:eq(7)").text().trim();
                    $(this).toggle(estadoFila === estado);
                });
            }
        });

        // Ordenar tabla
        $("#ordenarPor").on("change", function() {
            const orden = $(this).val();
            const filas = $("#tablaVentas tr").get();
            
            filas.sort(function(a, b) {
                let valorA, valorB;
                
                switch(orden) {
                    case "fecha_desc":
                        valorA = new Date($(a).find("td:eq(1)").text().split('/').reverse().join('-'));
                        valorB = new Date($(b).find("td:eq(1)").text().split('/').reverse().join('-'));
                        return valorB - valorA;
                    case "fecha_asc":
                        valorA = new Date($(a).find("td:eq(1)").text().split('/').reverse().join('-'));
                        valorB = new Date($(b).find("td:eq(1)").text().split('/').reverse().join('-'));
                        return valorA - valorB;
                    case "monto_desc":
                        valorA = parseFloat($(a).find("td:eq(4)").text().replace('Bs. ', '').replace(',', ''));
                        valorB = parseFloat($(b).find("td:eq(4)").text().replace('Bs. ', '').replace(',', ''));
                        return valorB - valorA;
                    case "monto_asc":
                        valorA = parseFloat($(a).find("td:eq(4)").text().replace('Bs. ', '').replace(',', ''));
                        valorB = parseFloat($(b).find("td:eq(4)").text().replace('Bs. ', '').replace(',', ''));
                        return valorA - valorB;
                    default:
                        return 0;
                }
            });
            
            $.each(filas, function(index, fila) {
                $("#tablaVentas").append(fila);
            });
        });

        function verDetalle(idVenta) {
            // Hacer una petición AJAX para obtener los detalles
            $.ajax({
                url: 'ventasE.php',
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
        
        // Función para completar venta pendiente
        function completarVenta(idVenta) {
            if (confirm('¿Está seguro de marcar esta venta como COMPLETADA? Esto actualizará el estado y no se podrá revertir.')) {
                $.ajax({
                    url: 'ventasE.php',
                    method: 'POST',
                    data: { id_venta: idVenta },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Venta marcada como completada exitosamente');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            }
        }