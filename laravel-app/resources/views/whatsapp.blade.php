<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Bot - Conexão</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    
    <div class="bg-white p-8 rounded-xl shadow-lg w-[400px] text-center flex flex-col items-center">
        <h1 class="text-2xl font-bold mb-2 text-gray-800">Painel WhatsApp</h1>

        <div id="status-message" class="text-sm font-semibold text-gray-500 mb-6 px-4 py-2 rounded-full bg-gray-100">
            Iniciando verificação...
        </div>

        <div id="qrcode" class="mb-4 min-h-[256px] flex justify-center items-center"></div>

        <div id="send-form" class="w-full flex-col gap-3 hidden mt-2">
            <input type="text" id="phone" placeholder="Ex: 5511999999999" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none text-sm">
            <textarea id="message" placeholder="Escreva sua mensagem aqui..." class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none text-sm resize-none"></textarea>
            <button onclick="sendMessage()" class="w-full bg-green-600 hover:bg-green-700 transition text-white p-2 rounded font-bold text-sm">Disparar Mensagem</button>
            <p id="send-status" class="text-xs text-center font-semibold mt-1"></p>
        </div>
    </div>

    <script>
        const statusMessage = document.getElementById('status-message');
        const qrCodeContainer = document.getElementById('qrcode');
        const sendForm = document.getElementById('send-form');

        const qrCode = new QRCode(qrCodeContainer, { width: 256, height: 256, colorDark : "#0f172a", colorLight : "#ffffff" });
        let lastQrString = "";

        function checkStatus() {
            fetch('/api/whatsapp-status')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'connected') {
                        statusMessage.innerHTML = "<span class='text-green-700'>WhatsApp Conectado com Sucesso! ✅</span>";
                        statusMessage.classList.replace('bg-gray-100', 'bg-green-100');
                        statusMessage.classList.replace('bg-yellow-50', 'bg-green-100');
                        qrCodeContainer.style.display = 'none';
                        
                        // Mostra o formulário de disparo!
                        sendForm.classList.remove('hidden');
                        sendForm.classList.add('flex');
                    } else if (data.status === 'waiting_qr' && data.qr_string) {
                        statusMessage.innerHTML = "<span class='text-yellow-600'>Aguardando leitura do QR Code...</span>";
                        statusMessage.classList.replace('bg-gray-100', 'bg-yellow-50');
                        if (data.qr_string !== lastQrString) {
                            qrCode.clear();
                            qrCode.makeCode(data.qr_string);
                            lastQrString = data.qr_string;
                        }
                    }
                });
        }

        function sendMessage() {
            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;
            const statusText = document.getElementById('send-status');

            if(!phone || !message) {
                statusText.innerText = "Preencha número e mensagem!";
                statusText.className = "text-xs text-center font-semibold mt-1 text-red-500";
                return;
            }

            statusText.innerText = "Enviando...";
            statusText.className = "text-xs text-center font-semibold mt-1 text-gray-500";

            fetch('/api/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone: phone, message: message })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    statusText.innerText = "Mensagem entregue! 🚀";
                    statusText.className = "text-xs text-center font-semibold mt-1 text-green-600";
                    document.getElementById('message').value = ""; // Limpa o campo
                } else {
                    statusText.innerText = "Erro ao enviar: " + data.message;
                    statusText.className = "text-xs text-center font-semibold mt-1 text-red-500";
                }
            });
        }

        checkStatus();
        setInterval(checkStatus, 3000);
    </script>
</body>
</html>