<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Perfil de admisión ordinaria UNDC 2026-II.
 * Fuente: https://admision.undc.edu.pe/public/docs/PREGUNTAS%202026-II.pdf
 * Solo define distribución; no asigna puntajes ni duración oficial.
 */
final class PerfilExamenUndc
{
    /** Código => [nombre de asignatura, bloque compatible con el simulador]. */
    public const CURSOS = [
        'LM' => ['Lógico Matemático', 'habilidad_logico_matematica'],
        'HV' => ['Habilidad Verbal', 'habilidad_verbal'],
        'CG' => ['Cultura General', 'conocimientos'],
        'IN' => ['Inglés', 'conocimientos'],
        'LE' => ['Lenguaje', 'conocimientos'],
        'LI' => ['Literatura', 'conocimientos'],
        'PS' => ['Personal Social (Psicología)', 'conocimientos'],
        'ECV' => ['Educación Cívica', 'conocimientos'],
        'HP' => ['Historia del Perú', 'conocimientos'],
        'HU' => ['Historia Universal', 'conocimientos'],
        'GG' => ['Geografía', 'conocimientos'],
        'EC' => ['Economía', 'conocimientos'],
        'FI' => ['Física', 'conocimientos'],
        'QU' => ['Química', 'conocimientos'],
        'BI' => ['Biología', 'conocimientos'],
        'AR' => ['Aritmética', 'conocimientos'],
        'AL' => ['Álgebra', 'conocimientos'],
        'GE' => ['Geometría', 'conocimientos'],
        'TR' => ['Trigonometría', 'conocimientos'],
    ];

    /** Cuotas oficiales de Admisión Ordinaria 2026-II por tipo y asignatura. */
    public const DISTRIBUCION = [
        'A' => [
            'LM' => 15, 'HV' => 15, 'CG' => 5, 'IN' => 5,
            'LE' => 10, 'LI' => 4, 'PS' => 4, 'ECV' => 4,
            'HP' => 3, 'HU' => 4, 'GG' => 2, 'EC' => 4,
            'FI' => 3, 'QU' => 3, 'BI' => 2, 'AR' => 6,
            'AL' => 6, 'GE' => 3, 'TR' => 2,
        ],
        'B' => [
            'LM' => 15, 'HV' => 15, 'CG' => 5, 'IN' => 5,
            'LE' => 6, 'LI' => 4, 'PS' => 4, 'ECV' => 2,
            'HP' => 3, 'HU' => 2, 'GG' => 2, 'EC' => 2,
            'FI' => 5, 'QU' => 5, 'BI' => 3, 'AR' => 6,
            'AL' => 6, 'GE' => 6, 'TR' => 4,
        ],
    ];

    /** Carreras incluidas en cada perfil según el PDF 2026-II. */
    public const CARRERAS = [
        'A' => ['Administración', 'Contabilidad', 'Derecho', 'Administración de Turismo y Hotelería'],
        'B' => ['Ingeniería de Sistemas', 'Agronomía'],
    ];

    public static function distribucion(string $tipo): array
    {
        $tipo = strtoupper(trim($tipo));
        if (!isset(self::DISTRIBUCION[$tipo])) {
            throw new InvalidArgumentException("Tipo de examen UNDC desconocido: {$tipo}");
        }

        return self::DISTRIBUCION[$tipo];
    }

    public static function total(string $tipo): int
    {
        return array_sum(self::distribucion($tipo));
    }

    /** Banco compartido mínimo: máximo exigido en cada asignatura. */
    public static function minimoBancoCompartido(): int
    {
        $total = 0;
        foreach (array_keys(self::CURSOS) as $codigo) {
            $total += max(self::DISTRIBUCION['A'][$codigo], self::DISTRIBUCION['B'][$codigo]);
        }

        return $total;
    }

    public static function validar(): void
    {
        foreach (self::DISTRIBUCION as $tipo => $cuotas) {
            if (count($cuotas) !== count(self::CURSOS)
                || array_diff(array_keys(self::CURSOS), array_keys($cuotas)) !== []
                || array_sum($cuotas) !== 100) {
                throw new InvalidArgumentException("Distribución inválida para Tipo {$tipo}");
            }
        }
    }
}
