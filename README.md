# Sistema de Gestão F5

Sistema web desenvolvido em PHP para gestão de clientes e serviços.

## Funcionalidades

- Gestão de Clientes
- Gestão de Serviços
- Sistema de Autenticação
- Geração de Faturas
- Upload de Arquivos
- API REST

## Requisitos

- PHP 7.4 ou superior
- MySQL/MariaDB
- Servidor Web (Apache/Nginx)

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/F5-SISTEMA-NOVO.git
```

2. Configure o banco de dados:
- Crie um banco de dados MySQL
- Importe o arquivo `database/tables.sql`
- Copie `config/database.example.php` para `config/database.php`
- Configure as credenciais do banco no arquivo `config/database.php`

3. Configure o servidor web:
- Configure o documento root para a pasta do projeto
- Certifique-se que o mod_rewrite está habilitado (Apache)

4. Permissões:
```bash
chmod 755 -R /seu-diretorio/F5-SISTEMA-NOVO
chmod 777 -R /seu-diretorio/F5-SISTEMA-NOVO/uploads
chmod 777 -R /seu-diretorio/F5-SISTEMA-NOVO/logs
```

## Estrutura do Projeto

```
F5-SISTEMA-NOVO/
├── api/            # Endpoints da API
├── assets/         # Arquivos estáticos (CSS, JS, imagens)
├── auth/           # Sistema de autenticação
├── clients/        # Módulo de clientes
├── components/     # Componentes reutilizáveis
├── config/         # Configurações
├── database/       # Scripts SQL
├── includes/       # Arquivos incluídos
├── services/       # Módulo de serviços
├── uploads/        # Arquivos enviados
├── users/          # Gestão de usuários
└── utils/          # Funções utilitárias
```

## Contribuição

1. Faça um Fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.
