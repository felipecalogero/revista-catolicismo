<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bem-vindo(a) à Revista Catolicismo</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #8b0000; font-size: 24px; margin-bottom: 20px; text-align: center; }
        p { font-size: 16px; line-height: 1.5; margin-bottom: 15px; }
        .footer { margin-top: 30px; font-size: 14px; text-align: center; color: #666; border-top: 1px solid #eee; padding-top: 20px; }
        .btn { display: inline-block; background-color: #8b0000; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bem-vindo(a), {{ $user->name }}!</h1>
        <p>Agradecemos por se cadastrar na <strong>Revista Catolicismo</strong>.</p>
        <p>Seu cadastro foi realizado com sucesso. Agora você tem acesso aos conteúdos conforme seu plano de assinatura e pode gerenciar sua conta a qualquer momento.</p>
        <p>Para acessar sua conta, clique no botão abaixo:</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('login') }}" class="btn">Acessar Minha Conta</a>
        </p>
        <p>Se você tiver alguma dúvida, nossa equipe está sempre à disposição.</p>
        <p>Atenciosamente,<br>Equipe Catolicismo</p>
        
        <div class="footer">
            <p>Este é um e-mail automático, por favor não responda.</p>
            <p>&copy; {{ date('Y') }} Revista Catolicismo. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
