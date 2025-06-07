function abrirModal() {
    $('#modalMantenimiento').modal('show');
}

function verDetalleMantenimiento(id) {
    $.ajax({
        url: '../routes/mantenimiento.route.php?action=get_mantenimiento&id=' + id,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const detalle = response.detalle;
                
                // Crear el contenido del modal de detalles
                const modalContent = `
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del Mantenimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Cliente</h6>
                                <p><strong>Nombre:</strong> ${detalle.cliente_nombre} ${detalle.cliente_apellido}</p>
                                <p><strong>Documento:</strong> ${detalle.cliente_cedula}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Información de la Motocicleta</h6>
                                <p><strong>Modelo:</strong> ${detalle.moto_modelo_completo}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Detalles del Mantenimiento</h6>
                                <p><strong>Fecha:</strong> ${detalle.fecha_formateada}</p>
                                <p><strong>Tipo:</strong> ${detalle.tipo_mantenimiento}</p>
                                <p><strong>Costo:</strong> ${detalle.es_gratuito ? 'Gratuito' : detalle.costo_formateado + ' Bs'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Realizado por</h6>
                                <p><strong>Empleado:</strong> ${detalle.empleado_nombre} ${detalle.empleado_apellido}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6>Observaciones</h6>
                                <p>${detalle.descripcion || 'Sin observaciones adicionales'}</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                `;

                // Crear o actualizar modal de detalles
                let $detalleModal = $('#modalDetalleMantenimiento');
                if ($detalleModal.length === 0) {
                    $detalleModal = $(`
                        <div class="modal fade" id="modalDetalleMantenimiento" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    ${modalContent}
                                </div>
                            </div>
                        </div>
                    `).appendTo('body');
                } else {
                    $detalleModal.find('.modal-content').html(modalContent);
                }

                // Mostrar el modal
                new bootstrap.Modal($detalleModal[0]).show();
            } else {
                // Manejar error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'No se pudieron cargar los detalles del mantenimiento'
                });
            }
        },
        error: function(xhr) {
            console.error("Error:", xhr);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los detalles del mantenimiento'
            });
        }
    });
}


$(document).ready(function() {
    // Función para validar el costo
    function validarCosto(costo) {
        // Convertir a número y quitar negativos
        const valorCosto = Math.abs(parseFloat(costo));
        
        // Verificar que sea un número válido
        if (isNaN(valorCosto)) {
            return {
                valido: false,
                mensaje: 'Por favor, ingrese un valor numérico válido'
            };
        }
        
        // Verificar rango
        if (valorCosto < 0) {
            return {
                valido: false,
                mensaje: 'El costo no puede ser negativo'
            };
        }
        
        if (valorCosto > 10000) {
            return {
                valido: false,
                mensaje: 'El costo no puede superar los 10,000 Bs'
            };
        }
        
        return {
            valido: true,
            valor: valorCosto
        };
    }

    // Evento de input para prevenir valores negativos en tiempo real
    $('#costo_bs').on('input', function() {
        const input = $(this);
        const validacion = validarCosto(input.val());
        
        if (!validacion.valido) {
            input.addClass('is-invalid');
            input.val(''); // Limpiar valor inválido
        } else {
            input.removeClass('is-invalid');
        }
    });

    $('#formMantenimiento').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        // Validación personalizada para el costo
        const costoInput = $('#costo_bs');
        const esGratuito = $('#es_gratuito').val() === '1';
        
        // Validar costo si no es gratuito
        if (!esGratuito) {
            const validacion = validarCosto(costoInput.val());
            
            if (!validacion.valido) {
                costoInput.addClass('is-invalid');
                
                // Mostrar mensaje de error con SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Validación',
                    text: validacion.mensaje
                });
                
                return;
            }
        }
        
        // Quitar cualquier validación previa
        costoInput.removeClass('is-invalid');

        if (!form[0].checkValidity()) {
            form.addClass('was-validated');
            return;
        }

        const submitBtn = form.find('[type="submit"]');
        submitBtn.prop('disabled', true)
            .html('<i class="bi bi-arrow-repeat spinner"></i> Procesando...');

        // Preparar datos del formulario
        const formData = new FormData(form[0]);
        
        // Asegurarse de que el costo sea 0 si es gratuito
        if (esGratuito) {
            formData.set('costo_bs', '0');
        }

        // Primero verificar si el cliente ya tiene un mantenimiento gratuito
        $.ajax({
            url: '../routes/mantenimiento.route.php?action=check_mantenimiento_gratuito',
            method: 'GET',
            data: { cliente_id: formData.get('cliente') },
            dataType: 'json',
            success: function(checkResponse) {
                if (checkResponse.success) {
                    if (checkResponse.tiene_mantenimiento_gratuito && esGratuito) {
                        // Si ya tiene un mantenimiento gratuito y intenta hacer otro
                        Swal.fire({
                            icon: 'warning',
                            title: 'Mantenimiento Gratuito',
                            text: 'Este cliente ya ha utilizado su mantenimiento gratuito anteriormente.',
                            confirmButtonText: 'Entendido'
                        });
                        
                        submitBtn.prop('disabled', false)
                            .html('<i class="bi bi-save me-1"></i> Guardar Mantenimiento');
                        return;
                    }

                    // Continuar con el envío si no hay problema
                    enviarMantenimiento(formData, submitBtn);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: checkResponse.message
                    });
                    
                    submitBtn.prop('disabled', false)
                        .html('<i class="bi bi-save me-1"></i> Guardar Mantenimiento');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo verificar el mantenimiento gratuito'
                });
                
                submitBtn.prop('disabled', false)
                    .html('<i class="bi bi-save me-1"></i> Guardar Mantenimiento');
            }
        });
    });

    // Función separada para enviar mantenimiento
    function enviarMantenimiento(formData, submitBtn) {
        $.ajax({
            url: '../routes/mantenimiento.route.php?action=create',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Animación de carga antes del SweetAlert
                    Swal.fire({
                        title: 'Procesando...',
                        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Simular tiempo de procesamiento
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Cerrar el modal
                            $('#modalMantenimiento').modal('hide');
                            // Recargar la página
                            location.reload();
                        });
                    }, 1500); // Tiempo de animación
                } else {
                    // Manejar específicamente el error de mantenimiento gratuito previo
                    if (response.error_code === 'MANTENIMIENTO_GRATUITO_PREVIO') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atención',
                            text: response.message,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        // Manejo de otros errores
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                }
            },
            error: function(xhr) {
                // Manejo de errores de conexión
                let errorMsg = 'Error en la conexión con el servidor';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) {
                    errorMsg = xhr.statusText || errorMsg;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false)
                    .html('<i class="bi bi-save me-1"></i> Guardar Mantenimiento');
            }
        });
    }

    // Manejar cambio en select de gratuito
    $('#es_gratuito').change(function() {
        const esGratuito = $(this).val() === '1';
        const costoInput = $('#costo_bs');
        
        costoInput.prop('disabled', esGratuito);
        
        if (esGratuito) {
            costoInput.val('0');
        } else {
            // Si no es gratuito y el costo es 0, limpiar
            if (costoInput.val() === '0') {
                costoInput.val('');
            }
        }
    });

    // Campo gratuito toggle costo
    const selectGratuito = document.getElementById("es_gratuito");
    const inputCostoBs = document.getElementById("costo_bs");

    function toggleCosto() {
        const gratuito = selectGratuito.value === "1";
        if (gratuito) {
            inputCostoBs.value = 0;
            inputCostoBs.setAttribute("disabled", true);
        } else {
            inputCostoBs.removeAttribute("disabled");
        }
    }

    selectGratuito.addEventListener("change", toggleCosto);

    $('#modalMantenimiento').on('shown.bs.modal', function () {
        toggleCosto();
    });

    // Chequear si cliente ya tuvo mantenimiento al cambiar select cliente
    $('#cliente_id').on('change', function() {
        const clienteId = $(this).val();
        if (!clienteId) return;

        $.ajax({
            url: '../routes/mantenimiento.route.php?action=check_cliente_mantenimiento',
            type: 'GET',
            data: { cliente_id: clienteId },
            dataType: 'json',
            success: function(data) {
                if (data.tuvo_mantenimiento) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Información',
                        text: 'Este cliente ya ha recibido mantenimiento anteriormente.',
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al verificar mantenimiento del cliente.'
                });
            }
        });
    });
});
