<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Redirecionando para PagBank...</title>
    <style>
        body {
            text-align: center;
            padding: 50px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redirecionando para PagBank...</h2>
        <p>Aguarde, você será redirecionado automaticamente.</p>
        <a href="{{ $pagbank_url }}" rel="noopener noreferrer" class="btn" id="pagbank-link" style="display: none;">Ir para PagBank</a>
        <p style="margin-top: 30px; font-size: 14px; color: #666;">
            Se não for redirecionado, <a href="{{ $pagbank_url }}" rel="noopener noreferrer" style="color: #dc2626;">clique aqui</a>
        </p>
    </div>
    
    <script>
        // Redireciona automaticamente assim que a página carrega
        // Usa link click para garantir que o referer seja removido
        (function() {
            const url = '{{ $pagbank_url }}';
            const link = document.getElementById('pagbank-link');
            
            // Função para fazer o redirect
            function doRedirect() {
                if (link) {
                    // Clica no link - abre na mesma aba (sem target="_blank")
                    link.click();
                } else {
                    // Fallback: redireciona diretamente
                    window.location.href = url;
                }
            }
            
            // Tenta redirecionar imediatamente
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', doRedirect);
            } else {
                // Página já carregou, aguarda 100ms e redireciona
                setTimeout(doRedirect, 100);
            }
        })();
    </script>
</body>
</html>
