@extends('vendor.mail.html.layout')

@section('header')
    @include('vendor.mail.html.header', ['url' => config('app.url')])
@endsection

@section('content')
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td class="content-cell">
            <h1 style="font-family: serif; color: #991b1b;">Novo contato recebido</h1>
            
            <p>Você recebeu uma nova mensagem através do formulário de Fale Conosco do site.</p>
            
            <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e5e7eb;">
                <p><strong>Nome:</strong> {{ $data['name'] }}</p>
                <p><strong>E-mail:</strong> {{ $data['email'] }}</p>
                <p><strong>Assunto:</strong> {{ $data['subject'] }}</p>
                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 15px 0;">
                <p><strong>Mensagem:</strong></p>
                <p style="white-space: pre-wrap;">{{ $data['message'] }}</p>
            </div>
            
            <p>Para responder a este contato, basta responder diretamente a este e-mail.</p>
            
            <p>Atenciosamente,<br>Equipe Catolicismo</p>
        </td>
    </tr>
</table>
@endsection

@section('footer')
    @include('vendor.mail.html.footer')
@endsection
