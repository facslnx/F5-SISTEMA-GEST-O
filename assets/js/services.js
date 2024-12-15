document.addEventListener('DOMContentLoaded', function() {
    // Initialize Materialize components
    M.Modal.init(document.querySelectorAll('.modal'));
    M.Sidenav.init(document.querySelectorAll('.sidenav'));
    M.FloatingActionButton.init(document.querySelectorAll('.fixed-action-btn'));

    // Load services on page load
    loadServices();

    // Form submit handlers
    document.getElementById('addServiceForm').addEventListener('submit', handleAddService);
    document.getElementById('editServiceForm').addEventListener('submit', handleEditService);
});

async function loadServices() {
    try {
        const response = await fetch('../api/services.php');
        const services = await response.json();
        
        const tableBody = document.getElementById('servicesTable');
        tableBody.innerHTML = '';
        
        services.forEach(service => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${service.name}</td>
                <td>${service.description}</td>
                <td>R$ ${parseFloat(service.value).toFixed(2)}</td>
                <td>
                    <div class="switch">
                        <label>
                            <input type="checkbox" ${service.active ? 'checked' : ''} 
                                   onchange="updateServiceStatus(${service.id}, this.checked)">
                            <span class="lever"></span>
                        </label>
                    </div>
                </td>
                <td>
                    <button class="btn-small waves-effect waves-light" onclick="editService(${service.id})">
                        <i class="material-icons">edit</i>
                    </button>
                    <button class="btn-small waves-effect waves-light red" onclick="deleteService(${service.id})">
                        <i class="material-icons">delete</i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    } catch (error) {
        M.toast({html: 'Erro ao carregar serviços', classes: 'red'});
        console.error('Error loading services:', error);
    }
}

async function handleAddService(event) {
    event.preventDefault();
    
    const formData = {
        name: document.getElementById('serviceName').value,
        description: document.getElementById('serviceDescription').value,
        value: document.getElementById('serviceValue').value,
        active: document.getElementById('serviceStatus').checked
    };

    try {
        const response = await fetch('../api/services.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (response.ok) {
            M.Modal.getInstance(document.getElementById('addServiceModal')).close();
            document.getElementById('addServiceForm').reset();
            loadServices();
            M.toast({html: 'Serviço adicionado com sucesso', classes: 'green'});
        } else {
            throw new Error('Erro ao adicionar serviço');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error adding service:', error);
    }
}

async function handleEditService(event) {
    event.preventDefault();
    
    const serviceId = document.getElementById('editServiceId').value;
    const formData = {
        name: document.getElementById('editServiceName').value,
        description: document.getElementById('editServiceDescription').value,
        value: document.getElementById('editServiceValue').value,
        active: document.getElementById('editServiceStatus').checked
    };

    try {
        const response = await fetch(`../api/services.php?id=${serviceId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        if (response.ok) {
            M.Modal.getInstance(document.getElementById('editServiceModal')).close();
            loadServices();
            M.toast({html: 'Serviço atualizado com sucesso', classes: 'green'});
        } else {
            throw new Error('Erro ao atualizar serviço');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error updating service:', error);
    }
}

async function editService(id) {
    try {
        const response = await fetch(`../api/services.php?id=${id}`);
        const service = await response.json();

        document.getElementById('editServiceId').value = service.id;
        document.getElementById('editServiceName').value = service.name;
        document.getElementById('editServiceDescription').value = service.description;
        document.getElementById('editServiceValue').value = service.value;
        document.getElementById('editServiceStatus').checked = service.active;

        M.updateTextFields();
        M.textareaAutoResize(document.getElementById('editServiceDescription'));
        M.Modal.getInstance(document.getElementById('editServiceModal')).open();
    } catch (error) {
        M.toast({html: 'Erro ao carregar dados do serviço', classes: 'red'});
        console.error('Error loading service details:', error);
    }
}

async function deleteService(id) {
    if (!confirm('Tem certeza que deseja excluir este serviço?')) {
        return;
    }

    try {
        const response = await fetch(`../api/services.php?id=${id}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            loadServices();
            M.toast({html: 'Serviço excluído com sucesso', classes: 'green'});
        } else {
            throw new Error('Erro ao excluir serviço');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error deleting service:', error);
    }
}

async function updateServiceStatus(id, status) {
    try {
        const response = await fetch(`../api/services.php?id=${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ active: status })
        });

        if (response.ok) {
            M.toast({html: 'Status atualizado com sucesso', classes: 'green'});
        } else {
            throw new Error('Erro ao atualizar status');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error updating service status:', error);
    }
}
