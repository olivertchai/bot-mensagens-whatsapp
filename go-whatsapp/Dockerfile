FROM golang:1.25-alpine

# Instalar dependências do sistema em C (necessárias para algumas compilações do Go)
RUN apk add --no-cache gcc musl-dev

WORKDIR /app

# Copia todo o código e os arquivos go.mod/go.sum para o contêiner
COPY . .

# Automação: Força o download das bibliotecas durante a construção da imagem
RUN go get go.mau.fi/whatsmeow@latest
RUN go get github.com/lib/pq@latest
RUN go mod tidy

# Compilar o binário do Go
RUN go build -o main .

# Expor a porta que a nossa API Go vai usar
EXPOSE 8080

# Comando para iniciar o servidor
CMD ["./main"]