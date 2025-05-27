// Función para abrir el modal de nueva venta
function abrirModal() {
    var modal = new bootstrap.Modal(document.getElementById('modalVenta'));
    modal.show();
}
function verDetalle(idVenta) {
    // Hacer una petición AJAX para obtener los detalles
    $.ajax({
        url: 'ventasE.php',
        method: 'GET',
        data: { 
            id_venta: idVenta,
            action: 'get_details'
        },
        dataType: 'json',
        success: function(response) {
            let modalHTML = `
                <div class="modal fade" id="modalDetalleVenta" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-receipt me-2"></i> Detalle de Venta #${idVenta}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="bi bi-currency-exchange"></i> Tipo de cambio: 1 USD = 7 Bs.
                                </div>
                                
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
                                                <td>
                                                    $ ${parseFloat(response.venta.monto_total).toFixed(2)}<br>
                                                    <small class="text-muted">Bs. ${(parseFloat(response.venta.monto_total)*7).toFixed(2)}</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Adelanto:</th>
                                                <td>
                                                    $ ${parseFloat(response.venta.adelanto).toFixed(2)}<br>
                                                    <small class="text-muted">Bs. ${(parseFloat(response.venta.adelanto)*7).toFixed(2)}</small>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Saldo Pendiente:</th>
                                                <td>
                                                    $ ${parseFloat(response.venta.saldo_pendiente).toFixed(2)}<br>
                                                    <small class="text-muted">Bs. ${(parseFloat(response.venta.saldo_pendiente)*7).toFixed(2)}</small>
                                                </td>
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
                const precioBs = (parseFloat(producto.precio_unitario)*7).toFixed(2);
                const subtotalBs = (parseFloat(producto.subtotal)*7).toFixed(2);
                
                modalHTML += `
                    <tr>
                        <td>${producto.nombre_producto}</td>
                        <td><span class="badge bg-${producto.tipo_producto == 'motocicleta' ? 'primary' : 'info'}">${producto.tipo_producto}</span></td>
                        <td>
                            $ ${parseFloat(producto.precio_unitario).toFixed(2)}<br>
                            <small class="text-muted">Bs. ${precioBs}</small>
                        </td>
                        <td>${producto.cantidad}</td>
                        <td>
                            $ ${parseFloat(producto.subtotal).toFixed(2)}<br>
                            <small class="text-muted">Bs. ${subtotalBs}</small>
                        </td>
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
    if (!confirm('¿Está seguro que desea marcar esta venta como Completada?')) {
        return;
    }

    $.ajax({
        url: 'ventasA.php',
        type: 'POST',
        data: { 
            id_venta: idVenta, 
            action: 'completar_venta'
        },
        dataType: 'json',
        success: function(response) {
            if (response && response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + (response && response.message ? response.message : 'Respuesta inesperada del servidor'));
            }
        },
        error: function(xhr, status, error) {
            
            try {
                // Intentamos parsear la respuesta como JSON por si acaso
                const jsonResponse = JSON.parse(xhr.responseText);
                errorMsg += jsonResponse.message || 'Error desconocido';
            } catch (e) {
                // Si no es JSON, mostramos el error crudo
                if (xhr.responseText.includes('Sesión expirada')) {
                    errorMsg += 'Tu sesión ha expirado. Por favor, vuelve a iniciar sesión.';
                } else {
                    alert('Completado: La venta ha sido completada correctamente.');
                }
            }
            location.href = 'ventasA.php';
        }
    });
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
$(document).ready(function() {
    const TIPO_CAMBIO = 7; // 1 USD = 7 Bs.
const DESCUENTO_CONTADO_USD = 100;
const DESCUENTO_CONTADO_BS = DESCUENTO_CONTADO_USD * TIPO_CAMBIO;
const ADELANTO_FINANCIAMIENTO_USD = 100;
const ADELANTO_FINANCIAMIENTO_BS = ADELANTO_FINANCIAMIENTO_USD * TIPO_CAMBIO;

// Main calculation function
function calcularTotal() {
let subtotalUSD = 0;
let subtotalBS = 0;
const productosSeleccionados = [];

// Calculate subtotal from selected products
$('.producto-check:checked').each(function() {
    const precioUSD = parseFloat($(this).data('precio-usd'));
    const precioBS = precioUSD * TIPO_CAMBIO;
    const cantidad = parseInt($(this).closest('.card-body').find('.cant-input').val());
    
    const totalProductoUSD = precioUSD * cantidad;
    const totalProductoBS = precioBS * cantidad;
    
    subtotalUSD += totalProductoUSD;
    subtotalBS += totalProductoBS;
    
    productosSeleccionados.push({
        id: $(this).val(),
        nombre: $(this).data('nombre'),
        tipo: $(this).data('tipo'),
        precioUSD: precioUSD,
        precioBS: precioBS,
        cantidad: cantidad,
        totalUSD: totalProductoUSD,
        totalBS: totalProductoBS
    });
});

// Payment type and discount calculation
const tipoPago = $('#tipoPago').val();
const esAlContado = tipoPago === 'Al contado';

// Apply discount ONLY for cash payment
const descuentoUSD = esAlContado ? DESCUENTO_CONTADO_USD : 0;
const descuentoBS = esAlContado ? DESCUENTO_CONTADO_BS : 0;

const totalConDescuentoUSD = Math.max(0, subtotalUSD - descuentoUSD);
const totalConDescuentoBS = Math.max(0, subtotalBS - descuentoBS);

// Calculate payment amounts
let adelantoUSD = 0;
let adelantoBS = 0;
let saldoUSD = 0;
let saldoBS = 0;

if (esAlContado) {
    // Pago al contado: adelanto = total con descuento, saldo = 0
    adelantoUSD = totalConDescuentoUSD;
    adelantoBS = totalConDescuentoBS;
    saldoUSD = 0;
    saldoBS = 0;
    
    // Forzar el campo de adelanto a mostrar el total con descuento
    $('#adelanto').val(adelantoBS.toFixed(2));
} else if (tipoPago === 'Financiamiento bancario') {
    adelantoUSD = ADELANTO_FINANCIAMIENTO_USD;
    adelantoBS = ADELANTO_FINANCIAMIENTO_BS;
    saldoUSD = subtotalUSD - adelantoUSD; // Sin descuento
    saldoBS = subtotalBS - adelantoBS;   // Sin descuento
} else if (tipoPago === 'Crédito Directo') {
    adelantoUSD = subtotalUSD / 2;       // Sin descuento
    adelantoBS = subtotalBS / 2;         // Sin descuento
    saldoUSD = subtotalUSD - adelantoUSD; // Sin descuento
    saldoBS = subtotalBS - adelantoBS;   // Sin descuento
}

// Update UI fields
actualizarCampos(
    subtotalUSD, subtotalBS,
    totalConDescuentoUSD, totalConDescuentoBS,
    adelantoUSD, adelantoBS,
    saldoUSD, saldoBS,
    descuentoUSD, descuentoBS
);

// Update summary table
actualizarResumen(productosSeleccionados, subtotalUSD, subtotalBS, descuentoUSD, descuentoBS);
}

// Update form fields with calculated values
function actualizarCampos(subtotalUSD, subtotalBS, totalUSD, totalBS, adelantoUSD, adelantoBS, saldoUSD, saldoBS, descuentoUSD, descuentoBS) {
$('#subtotalVenta').text('$ ' + subtotalUSD.toFixed(2));
$('#subtotalVentaBs').text('Bs. ' + subtotalBS.toFixed(2));

$('#adelanto').val(adelantoBS.toFixed(2));
$('#adelantoUSD').text('$ ' + adelantoUSD.toFixed(2));

$('#adelantoResumen').text('Bs. ' + adelantoBS.toFixed(2));
$('#adelantoResumenUSD').text('$ ' + adelantoUSD.toFixed(2));

// Asegurarse que el saldo sea 0 para pagos al contado
const esAlContado = $('#tipoPago').val() === 'Al contado';
const saldoFinalBS = esAlContado ? 0 : saldoBS;
const saldoFinalUSD = esAlContado ? 0 : saldoUSD;

$('#saldoResumen').text('Bs. ' + saldoFinalBS.toFixed(2));
$('#saldoResumenUSD').text('$ ' + saldoFinalUSD.toFixed(2));

$('#totalVenta').text('$ ' + totalUSD.toFixed(2));
$('#totalVentaBs').text('Bs. ' + totalBS.toFixed(2));

// Show/hide discount section
if (descuentoUSD > 0) {
    $('#descuentoContainer').show();
    $('#descuentoVenta').text('- $' + descuentoUSD.toFixed(2));
    $('#descuentoVentaBs').text('- Bs.' + descuentoBS.toFixed(2));
} else {
    $('#descuentoContainer').hide();
}

// Update hidden field for form submission
$('#inputDescuento').val(descuentoUSD);
}


    // Update summary table with products and totals
    function actualizarResumen(productos, subtotalUSD, subtotalBS, descuentoUSD, descuentoBS) {
        let html = '';
        
        if (productos.length > 0) {
            html += '<table class="table table-sm">';
            html += '<thead><tr><th>Producto</th><th class="text-end">Precio ($)</th><th class="text-end">Precio (Bs.)</th><th class="text-end">Cantidad</th><th class="text-end">Total ($)</th><th class="text-end">Total (Bs.)</th></tr></thead>';
            html += '<tbody>';
            
            productos.forEach(p => {
                html += `<tr>
                    <td>${p.nombre} <span class="badge bg-${p.tipo === 'motocicleta' ? 'primary' : 'info'}">${p.tipo}</span></td>
                    <td class="text-end">$ ${p.precioUSD.toFixed(2)}</td>
                    <td class="text-end">Bs. ${p.precioBS.toFixed(2)}</td>
                    <td class="text-end">${p.cantidad}</td>
                    <td class="text-end">$ ${p.totalUSD.toFixed(2)}</td>
                    <td class="text-end">Bs. ${p.totalBS.toFixed(2)}</td>
                </tr>`;
            });
            
            // Add discount row ONLY if there's a discount (cash payment)
            if (descuentoUSD > 0) {
                html += `<tr class="table-success">
                    <td colspan="4" class="text-end fw-bold">Descuento al contado</td>
                    <td class="text-end fw-bold">- $ ${descuentoUSD.toFixed(2)}</td>
                    <td class="text-end fw-bold">- Bs. ${descuentoBS.toFixed(2)}</td>
                </tr>`;
            }
            
            // Add final total (with discount if applies)
            const totalFinalUSD = subtotalUSD - descuentoUSD;
            const totalFinalBS = subtotalBS - descuentoBS;
            
            html += `<tr class="table-primary">
                <td colspan="4" class="text-end fw-bold">Total a pagar</td>
                <td class="text-end fw-bold">$ ${totalFinalUSD.toFixed(2)}</td>
                <td class="text-end fw-bold">Bs. ${totalFinalBS.toFixed(2)}</td>
            </tr>`;
            
            html += '</tbody></table>';
        } else {
            html = '<div class="alert alert-info mb-0"><i class="bi bi-info-circle"></i> Seleccione productos para ver el resumen</div>';
        }
        
        $('#resumenVenta').html(html);
    }

    // Event handlers
    $('#tipoPago').change(calcularTotal);
    
    $('.producto-check').change(function() {
        const inputCantidad = $(this).closest('.card-body').find('.cant-input');
        inputCantidad.prop('disabled', !this.checked);
        
        if (!this.checked) {
            inputCantidad.val(1);
        }
        
        calcularTotal();
    });
    
    $(document).on('change', '.cant-input', function() {
        const max = parseInt($(this).attr('max'));
        const value = parseInt($(this).val());
        
        if (value > max) {
            $(this).val(max);
            alert(`No hay suficiente stock. Máximo disponible: ${max}`);
        } else if (value < 1) {
            $(this).val(1);
        }
        
        calcularTotal();
    });
    
    // Product search and filtering
    $('#buscadorProductos').keyup(function() {
        const searchText = $(this).val().toLowerCase();
        
        $('.producto-item').each(function() {
            const nombre = $(this).data('nombre').toLowerCase();
            const tipo = $(this).data('tipo').toLowerCase();
            $(this).toggle(nombre.includes(searchText) || tipo.includes(searchText));
        });
    });
    
    $('#btnFiltrarMotocicletas').click(function() {
        $('.producto-item').hide();
        $('.producto-item[data-tipo="motocicleta"]').show();
        $('#buscadorProductos').val('');
    });
    
    $('#btnFiltrarAccesorios').click(function() {
        $('.producto-item').hide();
        $('.producto-item[data-tipo="accesorio"]').show();
        $('#buscadorProductos').val('');
    });
    
    // Initialize Select2 for client search
    $('#selectCliente').select2({
        placeholder: "Buscar cliente...",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalVenta')
    });

    // Initial calculation
    calcularTotal();
});
        
        $(document).on('change', '.cant-input', function() {
            const max = parseInt($(this).attr('max'));
            const value = parseInt($(this).val());
            
            if (value > max) {
                $(this).val(max);
                alert(`No hay suficiente stock. Máximo disponible: ${max}`);
            } else if (value < 1) {
                $(this).val(1);
            }
            
            calcularTotal();
        });
        
        // Product search and filtering
        $('#buscadorProductos').keyup(function() {
            const searchText = $(this).val().toLowerCase();
            
            $('.producto-item').each(function() {
                const nombre = $(this).data('nombre');
                const tipo = $(this).data('tipo');
                $(this).toggle(nombre.includes(searchText) || tipo.includes(searchText));
            });
        });
