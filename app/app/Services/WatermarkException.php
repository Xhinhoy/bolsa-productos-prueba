<?php

namespace App\Services;

use RuntimeException;

/**
 * Fallo del marcado que ya trae un mensaje apto para mostrar al usuario.
 */
class WatermarkException extends RuntimeException {}
