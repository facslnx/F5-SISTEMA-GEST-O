document.addEventListener('DOMContentLoaded', function() {
    // Initialize Materialize components
    M.Modal.init(document.querySelectorAll('.modal'), {
        dismissible: false,
        endingTop: '10%'
    });
    M.Sidenav.init(document.querySelectorAll('.sidenav'));
    M.FloatingActionButton.init(document.querySelectorAll('.fixed-action-btn'));
    M.FormSelect.init(document.querySelectorAll('select'));

    // Initialize masks
    $('#telefone').mask('(00) 00000-0000');
    $('#cep').mask('00000-000');
    $('#documento').mask('00.000.000/0000-00');

    // Load initial data
    loadClients();
    loadServices();

    // Form submit handlers
    document.getElementById('addClientForm').addEventListener('submit', handleAddClient);
    document.getElementById('editClientForm').addEventListener('submit', handleEditClient);

    // CEP auto-complete
    document.getElementById('cep').addEventListener('blur', function(e) {
        const cep = e.target.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        document.getElementById('endereco').value = data.logradouro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('estado').value = data.uf;
                        M.updateTextFields();
                        M.FormSelect.init(document.getElementById('estado'));
                    }
                });
        }
    });
});

async function loadClients() {
    try {
        const response = await fetch('../api/clients.php');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'Erro ao carregar clientes');
        }
        
        const clients = result.data;
        const tableBody = document.getElementById('clientsTable');
        tableBody.innerHTML = '';
        
        clients.forEach(client => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="switch">
                        <label>
                            <input type="checkbox" ${client.status ? 'checked' : ''} 
                                   onchange="updateClientStatus(${client.id}, this.checked)">
                            <span class="lever"></span>
                        </label>
                    </div>
                </td>
                <td>${client.empresa || ''}</td>
                <td>${client.responsavel || ''}</td>
                <td>${client.telefone || ''}</td>
                <td>${client.email || ''}</td>
                <td>R$ ${parseFloat(client.valor_contrato || 0).toFixed(2)}</td>
                <td>
                    <button class="btn-small waves-effect waves-light" onclick="editClient(${client.id})">
                        <i class="material-icons">edit</i>
                    </button>
                    ${client.contrato_url ? `
                    <button class="btn-small waves-effect waves-light" onclick="viewContract('${client.contrato_url}')">
                        <i class="material-icons">description</i>
                    </button>
                    ` : ''}
                    <button class="btn-small waves-effect waves-light red" onclick="deleteClient(${client.id})">
                        <i class="material-icons">delete</i>
                    </button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error loading clients:', error);
    }
}

async function loadServices() {
    try {
        const response = await fetch('../api/services.php');
        const services = await response.json();
        
        const selectElements = document.querySelectorAll('#servicos, #editServicos');
        selectElements.forEach(select => {
            select.innerHTML = services.map(service => 
                `<option value="${service.id}">${service.name} - R$ ${parseFloat(service.value).toFixed(2)}</option>`
            ).join('');
            M.FormSelect.init(select);
        });
    } catch (error) {
        M.toast({html: 'Erro ao carregar serviços', classes: 'red'});
        console.error('Error loading services:', error);
    }
}

async function handleAddClient(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('empresa', document.getElementById('empresa').value);
    formData.append('documento', document.getElementById('documento').value);
    formData.append('endereco', document.getElementById('endereco').value);
    formData.append('estado', document.getElementById('estado').value);
    formData.append('cidade', document.getElementById('cidade').value);
    formData.append('cep', document.getElementById('cep').value);
    formData.append('responsavel', document.getElementById('responsavel').value);
    formData.append('telefone', document.getElementById('telefone').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('valor_contrato', document.getElementById('valor_contrato').value);
    formData.append('status', document.getElementById('status').checked);

    const servicos = M.FormSelect.getInstance(document.getElementById('servicos')).getSelectedValues();
    formData.append('servicos', JSON.stringify(servicos));

    const contratoFile = document.getElementById('contrato').files[0];
    if (contratoFile) {
        formData.append('contrato', contratoFile);
    }

    try {
        const response = await fetch('../api/clients.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            M.toast({html: 'Cliente criado com sucesso!', classes: 'green'});
            document.getElementById('addClientForm').reset();
            M.Modal.getInstance(document.getElementById('addClientModal')).close();
            loadClients();
        } else {
            throw new Error(data.error || 'Erro ao criar cliente');
        }
    } catch (error) {
        console.error('Erro:', error);
        M.toast({html: error.message, classes: 'red'});
    }
}

async function handleEditClient(event) {
    event.preventDefault();
    
    const clientId = document.getElementById('editClientId').value;
    const formData = new FormData();
    
    formData.append('empresa', document.getElementById('editEmpresa').value);
    formData.append('documento', document.getElementById('editDocumento').value);
    formData.append('endereco', document.getElementById('editEndereco').value);
    formData.append('estado', document.getElementById('editEstado').value);
    formData.append('cidade', document.getElementById('editCidade').value);
    formData.append('cep', document.getElementById('editCep').value);
    formData.append('responsavel', document.getElementById('editResponsavel').value);
    formData.append('telefone', document.getElementById('editTelefone').value);
    formData.append('email', document.getElementById('editEmail').value);
    formData.append('valor_contrato', document.getElementById('editValorContrato').value);
    formData.append('status', document.getElementById('editStatus').checked);

    const servicos = M.FormSelect.getInstance(document.getElementById('editServicos')).getSelectedValues();
    formData.append('servicos', JSON.stringify(servicos));

    const contratoFile = document.getElementById('editContrato').files[0];
    if (contratoFile) {
        formData.append('contrato', contratoFile);
    }

    try {
        const response = await fetch(`../api/clients.php?id=${clientId}`, {
            method: 'PUT',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            M.Modal.getInstance(document.getElementById('editClientModal')).close();
            loadClients();
            M.toast({html: 'Cliente atualizado com sucesso', classes: 'green'});
        } else {
            throw new Error(data.error || 'Erro ao atualizar cliente');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error updating client:', error);
    }
}

async function editClient(id) {
    try {
        const response = await fetch(`../api/clients.php?id=${id}`);
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Erro ao carregar dados do cliente');
        }
        
        const client = data.data;

        // Fill form fields
        document.getElementById('editClientId').value = client.id;
        document.getElementById('editEmpresa').value = client.empresa;
        document.getElementById('editDocumento').value = client.documento;
        document.getElementById('editEndereco').value = client.endereco;
        document.getElementById('editEstado').value = client.estado;
        document.getElementById('editCidade').value = client.cidade;
        document.getElementById('editCep').value = client.cep;
        document.getElementById('editResponsavel').value = client.responsavel;
        document.getElementById('editTelefone').value = client.telefone;
        document.getElementById('editEmail').value = client.email;
        document.getElementById('editValorContrato').value = client.valor_contrato;
        document.getElementById('editStatus').checked = client.status;

        // Update select for services
        if (client.client_services) {
            const selectedServices = client.client_services.map(s => s.service_id.toString());
            const selectInstance = M.FormSelect.getInstance(document.getElementById('editServicos'));
            selectInstance.destroy();
            document.getElementById('editServicos').value = selectedServices;
            M.FormSelect.init(document.getElementById('editServicos'));
        }

        M.updateTextFields();
        M.Modal.getInstance(document.getElementById('editClientModal')).open();
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error loading client details:', error);
    }
}

async function deleteClient(id) {
    if (!confirm('Tem certeza que deseja excluir este cliente?')) {
        return;
    }

    try {
        const response = await fetch(`../api/clients.php?id=${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadClients();
            M.toast({html: 'Cliente excluído com sucesso', classes: 'green'});
        } else {
            throw new Error(data.error || 'Erro ao excluir cliente');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error deleting client:', error);
    }
}

async function updateClientStatus(id, status) {
    try {
        const response = await fetch(`../api/clients.php?id=${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status })
        });

        const data = await response.json();

        if (data.success) {
            M.toast({html: 'Status atualizado com sucesso', classes: 'green'});
        } else {
            throw new Error(data.error || 'Erro ao atualizar status');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
        console.error('Error updating client status:', error);
    }
}

function viewContract(url) {
    const preview = document.getElementById('contractPreview');
    const downloadBtn = document.getElementById('downloadContract');
    
    // Clear previous content
    preview.innerHTML = '';
    
    // Check file type
    if (url.match(/\.(jpg|jpeg|png)$/i)) {
        // Image preview
        preview.innerHTML = `<img src="${url}" style="max-width: 100%;">`;
    } else if (url.match(/\.pdf$/i)) {
        // PDF preview
        preview.innerHTML = `<embed src="${url}" type="application/pdf" width="100%" height="600px">`;
    }
    
    // Set download link
    downloadBtn.href = url;
    
    // Open modal
    M.Modal.getInstance(document.getElementById('viewContractModal')).open();
}
