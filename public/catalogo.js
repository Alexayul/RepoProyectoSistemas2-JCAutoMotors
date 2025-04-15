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
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('motorcycleModal');
    
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const modalImage = document.getElementById('modalImage');
        
        // Obtener datos de la moto
        const marca = button.dataset.marca;
        const modelo = button.dataset.modelo;
        const precio = button.dataset.precio;
        
        // Establecer el título del modal
        document.getElementById('modalTitle').textContent = `${marca} ${modelo}`;
        
        // Configurar el enlace de WhatsApp - CON VALIDACIÓN
        const whatsappButton = document.getElementById('whatsappButton');
        if (whatsappButton) {
            const whatsappMessage = `Hola, estoy interesado en la motocicleta ${marca} ${modelo} que vi en su catálogo (Precio: $${precio}). ¿Podrían darme más información?`;
            const encodedMessage = encodeURIComponent(whatsappMessage);
            whatsappButton.href = `https://wa.me/59177530498?text=${encodedMessage}`;
            
            // Verificar en consola
            console.log('WhatsApp href:', whatsappButton.href);
        } else {
            console.error('No se encontró el botón de WhatsApp');
        }
        
        // Resto del código para imagen y especificaciones...
        const imagePathFromDB = button.dataset.imagen || '';

        if(imagePathFromDB) {
            modalImage.src = '../' + imagePathFromDB;
            modalImage.onerror = function() {
                this.src = `https://via.placeholder.com/600x400?text=${encodeURIComponent(marca)}+${encodeURIComponent(modelo)}`;
            };
        } else {
            const modeloLower = modelo.toLowerCase().replace(/\s+/g, '');
            const extensions = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
            
            function tryImageExtension(index) {
                if (index >= extensions.length) {
                    modalImage.src = `https://via.placeholder.com/600x400?text=${encodeURIComponent(marca)}+${encodeURIComponent(modelo)}`;
                    return;
                }
                
                const ext = extensions[index];
                const testImage = new Image();
                testImage.src = `../imagenes/motos/${modeloLower}.${ext}`;
                
                testImage.onload = function() {
                    if (this.width > 0) {
                        modalImage.src = this.src;
                    }
                };
                
                testImage.onerror = function() {
                    tryImageExtension(index + 1);
                };
            }
            
            tryImageExtension(0);
        }
        
        const precioDolares = parseFloat(precio.replace(/,/g, '')) || 0;
        const precioBolivianos = precioDolares * 7;
        
        document.getElementById('modalBrand').textContent = marca;
        document.getElementById('modalModel').textContent = modelo;
        document.getElementById('modalCilindrada').textContent = `${button.dataset.cilindrada} cc`;
        document.getElementById('modalColor').textContent = button.dataset.color;
        document.getElementById('modalPrice').textContent = precio;
        document.getElementById('modalPrice2').textContent = precioBolivianos.toLocaleString('es-BO');
    });
});