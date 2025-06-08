    function generarCamposPagos(idVenta, saldoPendiente) {
        var numPagos = document.getElementById('numPagos' + idVenta).value;
        var contenedor = document.getElementById('camposPagos' + idVenta);
        contenedor.innerHTML = '';
        if (!numPagos || numPagos < 1) {
            document.getElementById('btnGuardarPagos' + idVenta).style.display = 'none';
            return;
        }
        for (var i = 1; i <= numPagos; i++) {
            contenedor.innerHTML += `
                <div class="row mb-2 align-items-end">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="form-label">Fecha del pago ${i}:</label>
                        <input type="date" name="fechas_pago[]" class="form-control fecha-input" required>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="form-label">Monto del pago ${i}:</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="montos_pago[]" class="form-control monto-input" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex justify-content-center align-items-center">
                        <span class="badge bg-secondary">${i}</span>
                    </div>
                </div>
            `;
        }
        document.getElementById('btnGuardarPagos' + idVenta).style.display = 'inline-block';
    }

    // Función para distribuir equitativamente el saldo pendiente entre los pagos
    function distribuirEquitativamente(idVenta, saldoPendiente) {
        var numPagos = document.getElementById('numPagos' + idVenta).value;
        if (!numPagos || numPagos < 1) return;

        // Asegurarse de que los campos existen antes de distribuir
        var inputs = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="montos_pago[]"]');
        if (inputs.length !== parseInt(numPagos)) {
            generarCamposPagos(idVenta, saldoPendiente);
            inputs = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="montos_pago[]"]');
        }

        var montoBase = Math.floor((saldoPendiente / numPagos) * 100) / 100;
        var montos = [];
        var total = 0;
        for (var i = 0; i < numPagos - 1; i++) {
            montos.push(montoBase);
            total += montoBase;
        }
        // El último pago ajusta para cubrir el total exacto
        montos.push(Math.round((saldoPendiente - total) * 100) / 100);

        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = montos[i];
        }
    }

    // Autocompletar fechas mensuales y dividir montos equitativamente
    function autocompletarFechas(idVenta) {
        var inputsFecha = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="fechas_pago[]"]');
        var inputsMonto = document.querySelectorAll('#camposPagos' + idVenta + ' input[name="montos_pago[]"]');
        if (inputsFecha.length === 0) return;
        var primeraFecha = inputsFecha[0].value;
        if (!primeraFecha) return;
        var fecha = new Date(primeraFecha);
        for (var i = 0; i < inputsFecha.length; i++) {
            var nuevaFecha = new Date(fecha);
            nuevaFecha.setMonth(fecha.getMonth() + i);
            // Ajuste para meses con menos días (ej: 31 de febrero)
            var dia = fecha.getDate();
            nuevaFecha.setDate(Math.min(dia, daysInMonth(nuevaFecha.getFullYear(), nuevaFecha.getMonth() + 1)));
            inputsFecha[i].value = nuevaFecha.toISOString().slice(0, 10);
        }
        // También dividir montos automáticamente
        var saldoPendiente = 0;
        // Buscar el saldo pendiente desde el input oculto
        var saldoInput = document.querySelector('#formProgramarPagos' + idVenta + ' input[name="saldo_pendiente"]');
        if (saldoInput) saldoPendiente = parseFloat(saldoInput.value);
        var numPagos = inputsMonto.length;
        if (numPagos > 0 && saldoPendiente > 0) {
            var montoBase = Math.floor((saldoPendiente / numPagos) * 100) / 100;
            var montos = [];
            var total = 0;
            for (var i = 0; i < numPagos - 1; i++) {
                montos.push(montoBase);
                total += montoBase;
            }
            montos.push(Math.round((saldoPendiente - total) * 100) / 100);
            for (var i = 0; i < inputsMonto.length; i++) {
                inputsMonto[i].value = montos[i];
            }
        }
    }

    // Helper para obtener días en un mes
    function daysInMonth(year, month) {
        return new Date(year, month, 0).getDate();
    }
    document.getElementById('fecha_venta').addEventListener('change', function() {
    document.getElementById('rango-fechas-container').style.display = 
        this.value === 'rango' ? 'block' : 'none';
});

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('fecha_venta').value === 'rango') {
        document.getElementById('rango-fechas-container').style.display = 'block';
    }
    
    // Limpiar filtros
    document.querySelector('a[href="creditosE.php"]').addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'creditosE.php?action=filter&limpiar=1';
    });
});