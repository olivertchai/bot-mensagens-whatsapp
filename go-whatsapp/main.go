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
	"go.mau.fi/whatsmeow/types"
	"go.mau.fi/whatsmeow/proto/waE2E"
	waLog "go.mau.fi/whatsmeow/util/log"
	"google.golang.org/protobuf/proto"

	_ "github.com/lib/pq"
)

var client *whatsmeow.Client
var qrCodeString string

func main() {
	dbLog := waLog.Stdout("Database", "DEBUG", true)
	clientLog := waLog.Stdout("Client", "DEBUG", true)

	dbConn := fmt.Sprintf("host=%s port=%s user=%s password=%s dbname=%s sslmode=disable",
		os.Getenv("DB_HOST"), os.Getenv("DB_PORT"),
		os.Getenv("DB_USER"), os.Getenv("DB_PASSWORD"), os.Getenv("DB_NAME"))

	container, err := sqlstore.New(context.TODO(), "postgres", dbConn, dbLog)
	if err != nil {
		log.Fatal("Erro ao conectar no banco de dados:", err)
	}

	deviceStore, err := container.GetFirstDevice(context.TODO())
	if err != nil {
		log.Fatal("Erro ao buscar dispositivo:", err)
	}

	client = whatsmeow.NewClient(deviceStore, clientLog)
	go startWhatsAppConnection()

	// ROTA 1: Status da conexão
	http.HandleFunc("/api/status", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		status := "waiting_qr"
		if client.IsConnected() && client.IsLoggedIn() {
			status = "connected"
		}
		json.NewEncoder(w).Encode(map[string]string{
			"status":    status,
			"qr_string": qrCodeString,
		})
	})

	// ROTA 2: Disparo de Mensagem (NOVO)
	http.HandleFunc("/api/send", func(w http.ResponseWriter, r *http.Request) {
		if r.Method != http.MethodPost {
			http.Error(w, "Método não permitido", http.StatusMethodNotAllowed)
			return
		}

		// Recebe os dados em JSON (telefone e mensagem)
		var payload struct {
			Phone   string `json:"phone"`
			Message string `json:"message"`
		}
		if err := json.NewDecoder(r.Body).Decode(&payload); err != nil {
			http.Error(w, "JSON inválido", http.StatusBadRequest)
			return
		}

		// O WhatsApp identifica os números no formato: 5511999999999@s.whatsapp.net
		targetJID := types.NewJID(payload.Phone, types.DefaultUserServer)

		// Monta a estrutura de texto nativa do WhatsApp
		msg := &waE2E.Message{
			Conversation: proto.String(payload.Message),
		}

		// Faz o disparo!
		resp, err := client.SendMessage(context.Background(), targetJID, msg)
		if err != nil {
			http.Error(w, fmt.Sprintf("Erro ao enviar: %v", err), http.StatusInternalServerError)
			return
		}

		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{
			"status": "success",
			"message_id": resp.ID,
		})
	})

	porta := ":8080"
	fmt.Println("🚀 Servidor Go rodando na porta", porta)
	if err := http.ListenAndServe(porta, nil); err != nil {
		log.Fatal("Erro ao iniciar servidor Go: ", err)
	}
}

func startWhatsAppConnection() {
	if client.Store.ID == nil {
		qrChan, _ := client.GetQRChannel(context.Background())
		err := client.Connect()
		if err != nil {
			log.Fatal("Erro ao conectar o whatsmeow:", err)
		}
		for evt := range qrChan {
			if evt.Event == "code" {
				qrCodeString = evt.Code
				fmt.Println("Novo QR Code gerado!")
			}
		}
	} else {
		err := client.Connect()
		if err != nil {
			log.Fatal("Erro ao conectar sessão existente:", err)
		}
		fmt.Println("WhatsApp conectado usando a sessão do banco!")
	}
}