$(document).ready(function() {
    // Inicializar DataTables
    $('.datatable').DataTable({
        language: {
            url: `${APP_URL}/public/js/datatables-${currentLang}.json`
        },
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: `${APP_URL}/admin/api/${currentSection}`,
            type: 'GET'
        }
    });

    // Manejar eliminación de registros
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        if (confirm(I18n.t('admin.confirmDelete'))) {
            $.ajax({
                url: `${APP_URL}/admin/api/${type}/${id}`,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        $('.datatable').DataTable().ajax.reload();
                    } else {
                        showNotification('error', response.message);
                    }
                },
                error: function() {
                    showNotification('error', I18n.t('admin.errorDelete'));
                }
            });
        }
    });

    // Manejar edición de registros
    $(document).on('click', '.edit-btn', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const type = $(this).data('type');
        
        $.ajax({
            url: `${APP_URL}/admin/api/${type}/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    showEditModal(response.data);
                } else {
                    showNotification('error', response.message);
                }
            }
        });
    });

    // Manejar formularios de edición
    $(document).on('submit', '#editForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const type = $(this).data('type');
        const id = $(this).data('id');
        
        $.ajax({
            url: `${APP_URL}/admin/api/${type}/${id}`,
            type: 'PUT',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    $('#editModal').modal('hide');
                    $('.datatable').DataTable().ajax.reload();
                } else {
                    showNotification('error', response.message);
                }
            }
        });
    });

    // Función para mostrar notificaciones
    function showNotification(type, message) {
        const toast = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        $('.toast-container').append(toast);
        $('.toast').toast('show');
        
        // Eliminar el toast después de 5 segundos
        setTimeout(() => {
            $('.toast').remove();
        }, 5000);
    }

    // Función para mostrar el modal de edición
    function showEditModal(data) {
        const modal = $('#editModal');
        const form = $('#editForm');
        
        // Llenar el formulario con los datos
        Object.keys(data).forEach(key => {
            const input = form.find(`[name="${key}"]`);
            if (input.length) {
                input.val(data[key]);
            }
        });
        
        modal.modal('show');
    }

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Inicializar popovers
    $('[data-bs-toggle="popover"]').popover();
}); 