<?php

namespace Services;

use BD\TabelaJson;

/**
 * Simula o envio de e-mails: em vez de usar um servidor SMTP, a mensagem é
 * gravada na "caixa de saída" src/BD/table_email.json e no log do servidor PHP.
 */
class EmailSimulado
{
    public static function enviar(string $destinatario, string $assunto, string $mensagem, string $link = ''): void
    {
        (new TabelaJson('email', 'idEmail'))->inserir([
            'para'      => $destinatario,
            'assunto'   => $assunto,
            'mensagem'  => $mensagem,
            'link'      => $link,
            'dataEnvio' => date('Y-m-d H:i:s')
        ]);

        error_log(sprintf('[Mundear] E-mail simulado para %s: %s %s', $destinatario, $assunto, $link));
    }
}
