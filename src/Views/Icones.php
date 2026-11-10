<?php

namespace Views;

class Icones
{
    private const CAMINHOS = [
        'aviao'      => '<path d="M10.5 3.5a1.5 1.5 0 0 1 3 0V9l7 4v2l-7-2v5l2 1.5V21l-3.5-1L8.5 21v-1.5l2-1.5v-5l-7 2v-2l7-4z"/>',
        'onibus'     => '<path d="M8 6v6M15 6v6M2 12h19.6M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3M9 18h5"/><circle cx="7" cy="18" r="2"/><circle cx="16" cy="18" r="2"/>',
        'cama'       => '<path d="M3 5v14M3 15h18M21 19v-6a3 3 0 0 0-3-3h-7v5"/><circle cx="7" cy="11.5" r="2"/>',
        'guia'       => '<circle cx="12" cy="7.5" r="3.5"/><path d="M5 21c0-4 3.1-6.5 7-6.5s7 2.5 7 6.5"/>',
        'ingresso'   => '<path d="M3 8a2 2 0 0 0 2-2h14a2 2 0 0 0 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 0-2 2H5a2 2 0 0 0-2-2v-2a2 2 0 0 0 0-4z"/><path d="M14 6v2M14 11v2M14 16v2"/>',
        'calendario' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'lixeira'    => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'link'       => '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/>',
        'relogio'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pessoas'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 3-5.5 6.5-5.5s6.5 2 6.5 5.5"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.8c2 .6 3.5 2.3 3.5 5.2"/>',
        'telefone'   => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        'pin'        => '<path d="M12 21s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="9" r="2.5"/>',
        'copiar'     => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/>',
        'mais'       => '<path d="M12 5v14M5 12h14"/>',
        'olho'       => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'lapis'      => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'estrela'    => '<path fill="currentColor" stroke="none" d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
        'mapa'       => '<path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2z"/><path d="M9 4v14M15 6v14"/>',
        'casa'       => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'bussola'    => '<circle cx="12" cy="12" r="9"/><path d="M15.5 8.5l-2 5-5 2 2-5z"/>',
        'lista'      => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'seta'       => '<path d="M4 12h15M14 6l6 6-6 6"/>',
        'baixo'      => '<path d="M6 9l6 6 6-6"/>',
        'check'      => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'alerta'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5.5M12 16.2v.3"/>',
        'carteira'   => '<path d="M19 7V5a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-4a2 2 0 0 0 0 4h4v2a1 1 0 0 1-1 1H5a2 2 0 0 1-2-2V6"/><path d="M17 15h.01"/>',
        'escudo'     => '<path d="M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/>',
        'carro'      => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2M9 17h6"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'externo'    => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'arvore'     => '<path d="M12 3l6 8h-3l4 6H5l4-6H6zM12 17v4"/>',
        'chave'      => '<circle cx="8" cy="15" r="4"/><path d="M11 12l8-8M16 7l3 3M14 9l2 2"/>',
        'busca'      => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
        'fechar'     => '<path d="M6 6l12 12M18 6L6 18"/>',
        'camera'     => '<path d="M4 8h3l2-3h6l2 3h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13.5" r="3.5"/>',
    ];

    public static function get(string $nome, string $classes = 'h-5 w-5'): string
    {
        return '<svg class="' . $classes . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
             . (self::CAMINHOS[$nome] ?? '')
             . '</svg>';
    }
}
