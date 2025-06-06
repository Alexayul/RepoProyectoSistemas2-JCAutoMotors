function abrirModal() {
    const modal = new bootstrap.Modal(document.getElementById('modalMantenimiento'));
    modal.show();
}

function verDetalleMantenimiento(id) {
    $.ajax({
        url: 'mantenimientosE.php',
        method: 'GET',
        data: { 
            action: 'get_details', 
            id_mantenimiento: id 
        },
        success: function(response) {
        }
    });
}

$(document).ready(function() {
});
