# Usar a imagem oficial do Go (versão leve alpine)
FROM golang:1.22-alpine

# Instalar dependências do sistema necessárias para compilar o Go com SQLite/CGO (caso a lib precise em algum momento)
RUN apk add --no-cache gcc musl-dev

# Definir a pasta de trabalho dentro do contêiner
WORKDIR /app

# Copiar os arquivos de dependência primeiro (otimiza o cache do Docker)
# Nota: Vamos criar o go.mod no próximo passo!
COPY go.mod go.sum ./
RUN go mod download

# Copiar todo o resto do código
COPY . .

# Compilar o binário do Go
RUN go build -o main .

# Expor a porta que a nossa API Go vai usar
EXPOSE 8080

# Comando para iniciar a aplicação
CMD ["./main"]