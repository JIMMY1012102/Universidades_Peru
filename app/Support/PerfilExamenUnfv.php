<?php

namespace App\Support;

/** Examen ordinario: Prospecto UNFV 2026, anexo 8, página 70. */
final class PerfilExamenUnfv
{
    public const FUENTE = 'https://www.unfv.edu.pe/oca/images/pregrado/2026/ProspectoAdmision2026a.pdf';

    public const MINUTOS = 90;

    public const NOMBRES = [
        'ingenierias' => 'Ingenierías y Arquitectura',
        'economicas' => 'Ciencias Económicas, Financieras y Empresariales',
        'humanidades' => 'Humanidades, Ciencias Jurídicas y Sociales',
        'salud' => 'Ciencias de la Salud, Formales y Naturales',
    ];

    // HP representa la cuota conjunta de Historia del Perú y Universal, sin un reparto inventado.
    // FL reutiliza el catálogo existente «Filosofía y Lógica» para Filosofía.
    public const CUOTAS = [
        'ingenierias' => ['AV' => 7, 'AM' => 7, 'AR' => 3, 'GE' => 3, 'AL' => 3, 'TR' => 2,
            'FI' => 3, 'QU' => 3, 'BI' => 3, 'LE' => 2, 'LI' => 2, 'PS' => 2, 'CV' => 2,
            'HP' => 2, 'GG' => 2, 'EC' => 2, 'FL' => 2],
        'economicas' => ['AV' => 7, 'AM' => 7, 'AR' => 2, 'GE' => 2, 'AL' => 2, 'TR' => 1,
            'FI' => 2, 'QU' => 2, 'BI' => 2, 'LE' => 4, 'LI' => 2, 'PS' => 3, 'CV' => 2,
            'HP' => 3, 'GG' => 2, 'EC' => 5, 'FL' => 2],
        'humanidades' => ['AV' => 7, 'AM' => 7, 'AR' => 1, 'GE' => 1, 'AL' => 1, 'TR' => 0,
            'FI' => 2, 'QU' => 1, 'BI' => 2, 'LE' => 4, 'LI' => 4, 'PS' => 3, 'CV' => 2,
            'HP' => 4, 'GG' => 4, 'EC' => 3, 'FL' => 4],
        'salud' => ['AV' => 7, 'AM' => 7, 'AR' => 2, 'GE' => 2, 'AL' => 1, 'TR' => 1,
            'FI' => 3, 'QU' => 3, 'BI' => 5, 'LE' => 3, 'LI' => 2, 'PS' => 3, 'CV' => 2,
            'HP' => 3, 'GG' => 2, 'EC' => 2, 'FL' => 2],
    ];

    public static function esUnfv(string $nombre): bool
    {
        return str_starts_with($nombre, 'Examen UNFV 2026 · ');
    }

    public static function cursos(string $prefijo): array
    {
        return $prefijo === 'HP'
            ? ['Historia del Perú', 'Historia Universal']
            : [PerfilExamenCni::CURSOS[$prefijo][0]];
    }

    public static function posicion(string $asignatura): int
    {
        foreach (array_keys(self::CUOTAS['ingenierias']) as $posicion => $prefijo) {
            if (in_array($asignatura, self::cursos($prefijo), true)) {
                return $posicion;
            }
        }

        return count(self::CUOTAS['ingenierias']);
    }
}
