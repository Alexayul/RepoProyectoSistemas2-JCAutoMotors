// Selecciona todos los botones con la clase "ver-detalles"
const botonesVerDetalles = document.querySelectorAll('.ver-detalles');

// Itera sobre cada botón
botonesVerDetalles.forEach((boton) => {
    // Escucha el evento "click" en cada botón
    boton.addEventListener('click', () => {
        // Obtén el ID del modal desde el atributo "data-modal-id"
        const modalId = boton.getAttribute('data-modal-id');

        // Selecciona el modal correspondiente
        const modal = new bootstrap.Modal(document.getElementById(modalId));

        // Abre el modal
        modal.show();
    });
});