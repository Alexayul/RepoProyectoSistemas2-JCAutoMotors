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
        fetch(`?get_employee_data=1&id=${employeeId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Llenar el formulario con los datos recibidos
                document.getElementById('edit_employee_id').value = data.employee._id || data.employee.id;
                document.getElementById('edit_nombre').value = data.employee.nombre || '';
                document.getElementById('edit_apellido').value = data.employee.apellido || '';
                document.getElementById('edit_documento_identidad').value = data.employee.documento_identidad || '';
                document.getElementById('edit_telefono').value = data.employee.telefono || '';
                document.getElementById('edit_email').value = data.employee.email || '';
                document.getElementById('edit_cargo').value = data.employee.cargo || '';
                document.getElementById('edit_salario').value = data.employee.salario || '';
                document.getElementById('edit_id_rol').value = data.employee.id_rol || '2';
                document.getElementById('edit_usuario').value = data.employee.usuario || '';
                document.getElementById('foto_actual').value = data.employee.imagen || data.employee.foto || 'https://cdn-icons-png.flaticon.com/512/17320/17320345.png';
                document.getElementById('edit-foto-preview').src = data.employee.imagen || data.employee.foto || 'https://cdn-icons-png.flaticon.com/512/17320/17320345.png';
            } else {
                alert('Error al cargar los datos del empleado');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al comunicarse con el servidor');
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