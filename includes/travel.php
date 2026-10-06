<?php
declare(strict_types=1);

/** Safe HTML escaping for server-rendered views. */
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/** Render the accessible notification used by the travel page. */
function notificacionEmergente(string $mensaje, string $tipo = 'oferta'): string
{
    return sprintf(
        '<aside id="notification" class="notification show notification--%s" role="status" aria-live="polite">%s</aside>',
        e($tipo),
        e($mensaje)
    );
}

/** Search criteria submitted through the GET form. */
final class FiltroViaje
{
    public function __construct(
        public readonly string $nombreHotel = '',
        public readonly string $ciudad = '',
        public readonly string $pais = '',
        public readonly string $fechaViaje = '',
        public readonly int $duracionViaje = 0,
    ) {}

    public static function desdeFormulario(array $datos): self
    {
        $texto = static fn (string $campo): string => trim((string) ($datos[$campo] ?? ''));
        $duracion = filter_var($datos['duracion_viaje'] ?? 0, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);

        return new self(
            $texto('nombre_hotel'),
            $texto('ciudad'),
            $texto('pais'),
            $texto('fecha_viaje'),
            $duracion === false ? 0 : $duracion,
        );
    }

    public function coincideCon(array $paquete): bool
    {
        $contiene = static fn (string $valor, string $busqueda): bool =>
            $busqueda === '' || stripos($valor, $busqueda) !== false;

        return $contiene($paquete['hotel'], $this->nombreHotel)
            && $contiene($paquete['ciudad'], $this->ciudad)
            && $contiene($paquete['pais'], $this->pais)
            && ($this->fechaViaje === '' || $paquete['fecha'] === $this->fechaViaje)
            && ($this->duracionViaje === 0 || $paquete['duracion'] === $this->duracionViaje)
            && $paquete['cupos'] > 0;
    }
}

/** Demo package catalog for the PHP storefront. */
function catalogoPaquetes(): array
{
    return [
        ['id' => 'cancun', 'hotel' => 'Costa Azul Resort', 'ciudad' => 'Cancún', 'pais' => 'México', 'fecha' => '2026-10-15', 'duracion' => 7, 'precio' => 850000, 'cupos' => 5, 'oferta' => true],
        ['id' => 'buenos-aires', 'hotel' => 'Hotel Plaza', 'ciudad' => 'Buenos Aires', 'pais' => 'Argentina', 'fecha' => '2026-11-05', 'duracion' => 4, 'precio' => 230000, 'cupos' => 8, 'oferta' => false],
        ['id' => 'rio', 'hotel' => 'Copacabana Palace', 'ciudad' => 'Río de Janeiro', 'pais' => 'Brasil', 'fecha' => '2026-12-20', 'duracion' => 6, 'precio' => 990000, 'cupos' => 3, 'oferta' => true],
        ['id' => 'santiago', 'hotel' => 'Hotel Andes', 'ciudad' => 'Santiago', 'pais' => 'Chile', 'fecha' => '2026-10-10', 'duracion' => 3, 'precio' => 180000, 'cupos' => 12, 'oferta' => false],
    ];
}
