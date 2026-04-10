package main

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"

	"go.mau.fi/whatsmeow"
	"go.mau.fi/whatsmeow/store/sqlstore"
	waLog "go.mau.fi/whatsmeow/util/log"

	_ "github.com/lib/pq" // Driver do PostgreSQL
)

var client *whatsmeow.Client
var qrCodeString string

func main() {
	// 1. Configurar loggers para o whatsmeow
	dbLog := waLog.Stdout("Database", "DEBUG", true)
	clientLog := waLog.Stdout("Client", "DEBUG", true)

	// 2. Montar a string de conexão com o banco pegando do docker-compose
	dbConn := fmt.Sprintf("host=%s port=%s user=%s password=%s dbname=%s sslmode=disable",
		os.Getenv("DB_HOST"), os.Getenv("DB_PORT"),
		os.Getenv("DB_USER"), os.Getenv("DB_PASSWORD"), os.Getenv("DB_NAME"))

	// 3. Conectar ao banco de dados para armazenar a sessão do WhatsApp
	// ATUALIZAÇÃO: Agora exige context.TODO() no início
	container, err := sqlstore.New(context.TODO(), "postgres", dbConn, dbLog)
	if err != nil {
		log.Fatal("Erro ao conectar no banco de dados:", err)
	}

	// ATUALIZAÇÃO: Agora exige context.TODO() também aqui
	deviceStore, err := container.GetFirstDevice(context.TODO())
	if err != nil {
		log.Fatal("Erro ao buscar dispositivo:", err)
	}

	// 4. Criar o cliente do whatsmeow
	client = whatsmeow.NewClient(deviceStore, clientLog)

	// 5. Iniciar o processo de conexão/QR Code em segundo plano (Goroutine)
	go startWhatsAppConnection()

	// 6. Criar a Rota da API para o Laravel consultar o Status
	http.HandleFunc("/api/status", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")

		status := "waiting_qr"
		if client.IsConnected() && client.IsLoggedIn() {
			status = "connected"
		}

		resposta := map[string]string{
			"status":    status,
			"qr_string": qrCodeString,
		}

		json.NewEncoder(w).Encode(resposta)
	})

	porta := ":8080"
	fmt.Println("🚀 Servidor Go rodando na porta", porta)
	if err := http.ListenAndServe(porta, nil); err != nil {
		log.Fatal("Erro ao iniciar o servidor Go: ", err)
	}
}

func startWhatsAppConnection() {
	if client.Store.ID == nil {
		// Se for a primeira vez (não tem sessão), ele pede o QR Code
		qrChan, _ := client.GetQRChannel(context.Background())
		err := client.Connect()
		if err != nil {
			log.Fatal("Erro ao conectar o whatsmeow:", err)
		}
		
		// Fica escutando o canal para receber as atualizações do QR Code
		for evt := range qrChan {
			if evt.Event == "code" {
				qrCodeString = evt.Code // Atualiza a string global
				fmt.Println("Novo QR Code gerado!")
			} else {
				fmt.Println("Evento de Login:", evt.Event)
			}
		}
	} else {
		// Se já tem sessão salva no banco, apenas conecta
		err := client.Connect()
		if err != nil {
			log.Fatal("Erro ao conectar sessão existente:", err)
		}
		fmt.Println("WhatsApp já estava logado e conectado com sucesso!")
	}
}