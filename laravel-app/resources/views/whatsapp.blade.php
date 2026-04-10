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
        <h1 class="text-2xl font-bold mb-2 text-gray-800">Conectar WhatsApp</h1>

        <div id="status-message" class="text-sm font-semibold text-gray-500 mb-6 px-4 py-2 rounded-full bg-gray-100">
            Iniciando verificação...
        </div>

        <div id="qrcode" class="mb-4 min-h-[256px] flex justify-center items-center"></div>

        <p id="instruction" class="text-sm text-gray-600 hidden mt-2">
            Abra o WhatsApp no seu celular, vá em <b>Aparelhos Conectados</b> e escaneie o código acima.
        </p>
    </div>

    <script>
        const statusMessage = document.getElementById('status-message');
        const qrCodeContainer = document.getElementById('qrcode');
        const instruction = document.getElementById('instruction');

        // Configuração inicial do gerador de QR Code
        const qrCode = new QRCode(qrCodeContainer, {
            width: 256,
            height: 256,
            colorDark : "#0f172a", // Um cinza bem escuro/azulado
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });

        let lastQrString = "";

        function checkStatus() {
            // Pergunta para a nossa ponte no Laravel
            fetch('/api/whatsapp-status')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'connected') {
                        statusMessage.innerHTML = "<span class='text-green-600'>WhatsApp Conectado com Sucesso! ✅</span>";
                        statusMessage.classList.replace('bg-gray-100', 'bg-green-100');
                        qrCodeContainer.style.display = 'none';
                        instruction.style.display = 'none';
                    } else if (data.status === 'waiting_qr' && data.qr_string) {
                        statusMessage.innerHTML = "<span class='text-yellow-600'>Aguardando leitura do QR Code...</span>";
                        statusMessage.classList.replace('bg-gray-100', 'bg-yellow-50');
                        instruction.style.display = 'block';

                        // Só atualiza a imagem se o texto do QR mudar (evita a tela ficar piscando)
                        if (data.qr_string !== lastQrString) {
                            qrCode.clear();
                            qrCode.makeCode(data.qr_string);
                            lastQrString = data.qr_string;
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    statusMessage.innerHTML = "<span class='text-red-600'>Buscando status da API...</span>";
                });
        }

        // Executa a primeira vez
        checkStatus();
        // Fica perguntando o status a cada 3 segundos
        setInterval(checkStatus, 3000);
    </script>
</body>
</html>