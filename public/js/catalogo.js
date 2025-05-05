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
        
        // Configurar WhatsApp button
        const setupWhatsAppButton = (marca = 'su motocicleta', modelo = '', precio = '') => {
            const whatsappNumber = "59177206162";
            let mensaje = `¡Hola! Estoy interesado(a) en la motocicleta ${marca}`;
            if (modelo) mensaje += ` modelo ${modelo}`;
            if (precio) mensaje += `, vi que tiene un precio de $${precio}`;
            mensaje += `.\n\n¿Podrían brindarme más información sobre:\n- Disponibilidad\n- Formas de pago\n- Tiempos de entrega\n- Posibles descuentos?\n\n¡Muchas gracias!`;
            const encodedMessage = encodeURIComponent(mensaje);
            const url = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
            document.getElementById('whatsappButton').setAttribute('href', url);
        };
        setupWhatsAppButton(marca, modelo, precio);
        
        // Manejo de la imagen
        const imagenBase64 = button.dataset.imagen;
        const defaultImage = `https://via.placeholder.com/600x400?text=${encodeURIComponent(marca)}+${encodeURIComponent(modelo)}`;
        
        if(imagenBase64) {
            modalImage.src = `data:image/jpeg;base64,${imagenBase64}`;
            modalImage.onerror = function() {
                this.src = defaultImage;
            };
        } else {
            modalImage.src = defaultImage;
        }
        
        // Resto de la configuración del modal
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