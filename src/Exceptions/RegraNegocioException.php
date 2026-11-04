<?php

namespace Exceptions;

/**
 * Violação de uma regra de negócio (RN01..RN08) ou de validação.
 * A mensagem é sempre amigável e pode ser exibida diretamente ao usuário (RNF04).
 */
class RegraNegocioException extends \DomainException
{
}
