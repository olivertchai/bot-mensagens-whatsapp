# 📱 WhatsApp Bot - Arquitetura de Microsserviços (Go + Laravel)

Este projeto é uma plataforma de envio e recebimento de mensagens do WhatsApp. Ele utiliza uma arquitetura baseada em microsserviços via Docker, separando o motor de conexão do WhatsApp (escrito em Go) da lógica de negócios e interface (escrita em PHP/Laravel), utilizando um banco de dados compartilhado.

## 🛠️ Tecnologias Utilizadas

* **Motor WhatsApp:** [Go (Golang)](https://go.dev/) + [Whatsmeow](https://github.com/tulir/whatsmeow)
* **Backend / Painel:** [PHP 8.2](https://www.php.net/) + [Laravel](https://laravel.com/)
* **Banco de Dados:** [PostgreSQL 15](https://www.postgresql.org/)
* **Infraestrutura:** [Docker](https://www.docker.com/) & Docker Compose

## 📁 Estrutura do Projeto

```text
/
├── docker-compose.yml      # Orquestração dos contêineres
├── go-whatsapp/            # Microsserviço da API do WhatsApp (Go)
└── laravel-app/            # Aplicação principal e painel (PHP)
```
## Acesse
```
Para testar o Laravel: Acesse http://localhost:8000 (Você deve ver a tela inicial do Laravel).

Para testar o Go: Acesse http://localhost:8080/api/status (Você deve ver o JSON {"status":"waiting_qr","qr_string":"..."}).
```

<img width="913" height="787" alt="image" src="https://github.com/user-attachments/assets/a7ebcf4d-fad4-4597-bcab-48cd8d81804c" />
