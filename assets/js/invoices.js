document.addEventListener('DOMContentLoaded', function() {
    // Initialize Materialize components
    M.Sidenav.init(document.querySelectorAll('.sidenav'));
    M.Modal.init(document.querySelectorAll('.modal'));
    M.FormSelect.init(document.querySelectorAll('select'));
    M.updateTextFields();

    // Load initial data
    loadClients();
    loadInvoices();

    // Event listeners
    document.getElementById('addInvoiceForm').addEventListener('submit', handleAddInvoice);
    document.getElementById('statusFilter').addEventListener('change', loadInvoices);
    document.getElementById('client_id').addEventListener('change', loadClientServices);
    document.getElementById('desconto').addEventListener('input', updateTotal);
});

// Load clients for select dropdown
async function loadClients() {
    try {
        const response = await fetch('../api/clients.php?action=list');
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('client_id');
            select.innerHTML = '<option value="" disabled selected>Selecione o Cliente</option>';
            
            data.clients.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.empresa;
                select.appendChild(option);
            });
            
            M.FormSelect.init(select);
        }
    } catch (error) {
        M.toast({html: 'Erro ao carregar clientes', classes: 'red'});
    }
}

// Load client services when client is selected
async function loadClientServices() {
    const clientId = document.getElementById('client_id').value;
    if (!clientId) return;

    try {
        const response = await fetch(`../api/clients.php?action=get_services&client_id=${clientId}`);
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('servicesContainer');
            container.innerHTML = '';
            
            data.services.forEach(service => {
                container.innerHTML += `
                    <p>
                        <label>
                            <input type="checkbox" class="filled-in service-checkbox" 
                                   data-id="${service.id}" 
                                   data-valor="${service.valor}" 
                                   onchange="updateTotal()"/>
                            <span>${service.nome} - R$ ${parseFloat(service.valor).toFixed(2)}</span>
                        </label>
                    </p>
                `;
            });
        }
    } catch (error) {
        M.toast({html: 'Erro ao carregar serviços', classes: 'red'});
    }
}

// Calculate and update total value
function updateTotal() {
    const checkboxes = document.querySelectorAll('.service-checkbox:checked');
    let total = 0;
    
    checkboxes.forEach(checkbox => {
        total += parseFloat(checkbox.dataset.valor);
    });
    
    const desconto = parseFloat(document.getElementById('desconto').value) || 0;
    total = Math.max(0, total - desconto);
    
    document.getElementById('valor_total').value = total.toFixed(2);
    M.updateTextFields();
}

// Handle form submission for new invoice
async function handleAddInvoice(event) {
    event.preventDefault();
    
    const selectedServices = Array.from(document.querySelectorAll('.service-checkbox:checked'))
        .map(checkbox => ({
            id: checkbox.dataset.id,
            valor: checkbox.dataset.valor
        }));
    
    if (selectedServices.length === 0) {
        M.toast({html: 'Selecione pelo menos um serviço', classes: 'red'});
        return;
    }
    
    const formData = {
        client_id: document.getElementById('client_id').value,
        data_fatura: document.getElementById('data_fatura').value,
        vencimento: document.getElementById('vencimento').value,
        periodo: document.getElementById('periodo').value,
        status: document.getElementById('status').value,
        desconto: document.getElementById('desconto').value,
        valor_total: document.getElementById('valor_total').value,
        descricao: document.getElementById('descricao').value,
        services: selectedServices
    };
    
    try {
        const response = await fetch('../api/invoices.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            M.toast({html: 'Fatura criada com sucesso', classes: 'green'});
            document.getElementById('addInvoiceForm').reset();
            document.getElementById('servicesContainer').innerHTML = '';
            M.Modal.getInstance(document.getElementById('addInvoiceModal')).close();
            loadInvoices();
        } else {
            throw new Error(data.message || 'Erro ao criar fatura');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
    }
}

// Load invoices table
async function loadInvoices() {
    const statusFilter = document.getElementById('statusFilter').value;
    
    try {
        const response = await fetch(`../api/invoices.php?status=${statusFilter}`);
        if (!response.ok) {
            throw new Error('Erro ao carregar faturas');
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar faturas');
        }
        
        const tbody = document.getElementById('invoicesTable');
        tbody.innerHTML = '';
        
        if (!data.invoices || data.invoices.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="center-align">Nenhuma fatura encontrada</td>
                </tr>
            `;
            return;
        }
        
        data.invoices.forEach(invoice => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <span class="new badge ${getStatusColor(invoice.status)}" data-badge-caption="">
                        ${formatStatus(invoice.status)}
                    </span>
                </td>
                <td>${invoice.clients ? invoice.clients.empresa : 'N/A'}</td>
                <td>${formatDate(invoice.data_fatura)}</td>
                <td>${formatDate(invoice.vencimento)}</td>
                <td>${formatPeriodo(invoice.periodo)}</td>
                <td>R$ ${parseFloat(invoice.valor_total).toFixed(2)}</td>
                <td>
                    <a href="#!" class="btn-small waves-effect waves-light" onclick="viewInvoice(${invoice.id})">
                        <i class="material-icons">visibility</i>
                    </a>
                    <a href="#!" class="btn-small waves-effect waves-light red" onclick="deleteInvoice(${invoice.id})">
                        <i class="material-icons">delete</i>
                    </a>
                </td>
            `;
            tbody.appendChild(row);
        });
    } catch (error) {
        console.error('Erro:', error);
        M.toast({html: error.message, classes: 'red'});
        
        const tbody = document.getElementById('invoicesTable');
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="center-align red-text">Erro ao carregar faturas</td>
            </tr>
        `;
    }
}

// View invoice details
async function viewInvoice(id) {
    try {
        const response = await fetch(`../api/invoices.php?action=view&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const details = document.getElementById('invoiceDetails');
            const invoice = data.invoice;
            
            details.innerHTML = `
                <div class="row">
                    <div class="col s12">
                        <h5>Cliente: ${invoice.clients ? invoice.clients.empresa : 'N/A'}</h5>
                        <p><strong>Status:</strong> ${formatStatus(invoice.status)}</p>
                        <p><strong>Data da Fatura:</strong> ${formatDate(invoice.data_fatura)}</p>
                        <p><strong>Vencimento:</strong> ${formatDate(invoice.vencimento)}</p>
                        <p><strong>Período:</strong> ${formatPeriodo(invoice.periodo)}</p>
                        <p><strong>Descrição:</strong> ${invoice.descricao || '-'}</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col s12">
                        <h5>Serviços</h5>
                        <table class="striped">
                            <thead>
                                <tr>
                                    <th>Serviço</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${invoice.services.map(service => `
                                    <tr>
                                        <td>${service.nome}</td>
                                        <td>R$ ${parseFloat(service.valor).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Desconto</strong></td>
                                    <td>R$ ${parseFloat(invoice.desconto).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td><strong>R$ ${parseFloat(invoice.valor_total).toFixed(2)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            `;
            
            M.Modal.getInstance(document.getElementById('viewInvoiceModal')).open();
        }
    } catch (error) {
        M.toast({html: 'Erro ao carregar detalhes da fatura', classes: 'red'});
    }
}

// Delete invoice
async function deleteInvoice(id) {
    if (!confirm('Tem certeza que deseja excluir esta fatura?')) return;
    
    try {
        const response = await fetch('../api/invoices.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            M.toast({html: 'Fatura excluída com sucesso', classes: 'green'});
            loadInvoices();
        } else {
            throw new Error(data.message || 'Erro ao excluir fatura');
        }
    } catch (error) {
        M.toast({html: error.message, classes: 'red'});
    }
}

// Helper functions
function getStatusColor(status) {
    const colors = {
        'pendente': 'orange',
        'perto_de_vencer': 'yellow darken-2',
        'pago': 'green'
    };
    return colors[status] || 'grey';
}

function formatStatus(status) {
    const labels = {
        'pendente': 'Pendente',
        'perto_de_vencer': 'Perto de Vencer',
        'pago': 'Pago'
    };
    return labels[status] || status;
}

function formatPeriodo(periodo) {
    const labels = {
        'avulso': 'Avulso',
        'mensal': 'Mensal',
        'trimestral': 'Trimestral',
        'semestral': 'Semestral',
        'anual': 'Anual'
    };
    return labels[periodo] || periodo;
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('pt-BR');
}

// Print invoice
document.getElementById('printInvoice').addEventListener('click', function() {
    const content = document.getElementById('invoiceDetails').innerHTML;
    const win = window.open('', '', 'height=700,width=700');
    win.document.write(`
        <html>
            <head>
                <title>Fatura</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
                <style>
                    body { padding: 20px; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h4>F5 GESTÃO - Fatura</h4>
                    ${content}
                    <div class="no-print">
                        <button onclick="window.print()" class="btn waves-effect waves-light">
                            Imprimir
                        </button>
                    </div>
                </div>
            </body>
        </html>
    `);
});
