package main

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
)

func main() {
	// Rota de Status (Onde o Laravel vai buscar o QR Code)
	http.HandleFunc("/api/status", func(w http.ResponseWriter, r *http.Request) {
		// Define que a resposta será em formato JSON
		w.Header().Set("Content-Type", "application/json")
		
		// Simulando o que faremos com o whatsmeow no futuro
		resposta := map[string]string{
			"status":    "waiting_qr",
			"qr_string": "Esta_String_Sera_Substituida_Pelo_QR_Code_Real_Do_Whatsmeow",
		}
		
		json.NewEncoder(w).Encode(resposta)
	})

	// Configuração e inicialização do servidor HTTP
	porta := ":8080"
	fmt.Println("🚀 Servidor Go rodando na porta", porta)
	
	if err := http.ListenAndServe(porta, nil); err != nil {
		log.Fatal("Erro ao iniciar o servidor Go: ", err)
	}
}