<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Novo Usuário Cadastrado</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #8b0000; font-size: 22px; margin-bottom: 20px; border-bottom: 2px solid #8b0000; padding-bottom: 10px; }
        p { font-size: 16px; line-height: 1.5; margin-bottom: 10px; }
        .data-box { background-color: #f1f3f5; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .data-box p { margin: 5px 0; }
        .footer { margin-top: 30px; font-size: 14px; text-align: center; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Novo Cadastro Realizado</h1>
        <p>Um novo usuário acabou de se cadastrar no site da <strong>Revista Catolicismo</strong>.</p>
        
        <div class="data-box">
            <p><strong>Nome:</strong> {{ $user->name }}</p>
            <p><strong>E-mail:</strong> {{ $user->email }}</p>
            <p><strong>Data de Cadastro:</strong> {{ $user->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
        
        <div class="footer">
            <p>Este é um aviso automático do sistema.</p>
        </div>
    </div>
</body>
</html>
