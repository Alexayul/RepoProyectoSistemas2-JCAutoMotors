document.addEventListener('DOMContentLoaded', function() {
    // Employee search functionality
    const searchInput = document.getElementById('employeeSearch');
    const employeeCards = document.querySelectorAll('.employee-card');
    
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        
        employeeCards.forEach(card => {
            const container = card.closest('.col-md-6');
            const employeeName = card.querySelector('.employee-name').textContent.toLowerCase();
            const employeePosition = card.querySelectorAll('.employee-detail span')[0]?.textContent.toLowerCase() || '';
            
            if (employeeName.includes(searchTerm) || employeePosition.includes(searchTerm)) {
                container.style.display = '';
            } else {
                container.style.display = 'none';
            }
        });
    });
    
    // Preview de la imagen al agregar empleado
    const inputFoto = document.getElementById('foto');
    const previewFoto = document.getElementById('foto-preview');
    
    if (inputFoto && previewFoto) {
        inputFoto.addEventListener('change', function() {
            previewFile(this, previewFoto);
        });
    }
    
    // Preview de la imagen al editar empleado
    const editInputFoto = document.getElementById('edit_foto');
    const editPreviewFoto = document.getElementById('edit-foto-preview');
    
    if (editInputFoto && editPreviewFoto) {
        editInputFoto.addEventListener('change', function() {
            previewFile(this, editPreviewFoto);
        });
    }
    
    // Función para previsualizar archivos
    function previewFile(input, img) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                img.src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Evento para abrir modal de edición y cargar datos
    const editModalElem = document.getElementById('editEmployeeModal');
    if (editModalElem) {
        editModalElem.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const employeeId = button.getAttribute('data-employee-id');
            fetchEmployeeData(employeeId);
        });
    }
    
    // Función para obtener datos del empleado mediante AJAX
    function fetchEmployeeData(employeeId) {
        fetch(`gestionEmpleados.php?get_employee_data=1&id=${employeeId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Primero verifica si la respuesta es OK
            if (!response.ok) {
                throw new Error(`Error HTTP! estado: ${response.status}`);
            }
            
            // Verifica el contenido de la respuesta
            return response.text().then(text => {
                if (!text.trim()) {
                    throw new Error("La respuesta está vacía");
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Texto de respuesta no válido:", text);
                    throw new Error("La respuesta no es JSON válido");
                }
            });
        })
    .then(data => {
        if (data.success) {
            console.log("Datos recibidos:", data); // Para depuración
            
            // Llenar el formulario con los datos recibidos
            const emp = data.employee;
            document.getElementById('edit_employee_id').value = emp._id || emp.id;
            document.getElementById('edit_nombre').value = emp.nombre || '';
            document.getElementById('edit_apellido').value = emp.apellido || '';
            document.getElementById('edit_documento_identidad').value = emp.documento_identidad || '';
            document.getElementById('edit_telefono').value = emp.telefono || '';
            document.getElementById('edit_email').value = emp.email || '';
            document.getElementById('edit_cargo').value = emp.cargo || '';
            document.getElementById('edit_salario').value = emp.salario || '';
            document.getElementById('edit_id_rol').value = emp.id_rol || '2';
            document.getElementById('edit_usuario').value = emp.usuario || '';
            
            // Manejo de la imagen
            const previewImg = document.getElementById('edit-foto-preview');
            if (previewImg) {
                if (emp.imagen && (emp.imagen.startsWith('data:image') || emp.imagen === DEFAULT_AVATAR)) {
                    previewImg.src = emp.imagen;
                    document.getElementById('foto_actual').value = emp.imagen;
                } else {
                    // Si hay problema con la imagen, usar la predeterminada
                    previewImg.src = DEFAULT_AVATAR;
                    document.getElementById('foto_actual').value = DEFAULT_AVATAR;
                }
            }
        } else {
            const errorMsg = data.error || 'Error desconocido del servidor';
            console.error('Error del servidor:', errorMsg);
            alert(`Error al cargar datos: ${errorMsg}`);
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        alert(`Error de conexión: ${error.message}`);
    });
}
    
    // Si hay una notificación, ocultarla después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    }
});