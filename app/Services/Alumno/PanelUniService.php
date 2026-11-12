<?php

namespace App\Services\Alumno;

use App\Models\Usuario;
use App\Support\PerfilExamenCni;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Datos reales del panel UNI: estado de cada día, intentos, historial y resumen.
 * Solo lectura; no inventa resultados ni cambia la calificación existente.
 */
class PanelUniService
{
    private const ESTADOS_CERRADOS = ['finalizado', 'enviado_auto', 'anulado'];

    public function obtener(Usuario $usuario): array
    {
        $intentos = $this->intentos($usuario);
        $dias = collect(PerfilExamenCni::UNI_DIAS)->map(function (array $config, int $dia) use ($intentos) {
            $delDia = $intentos->where('dia', $dia);
            $enCurso = $delDia->first(fn ($i) => $i->en_curso);
            $ultimoCerrado = $delDia->first(fn ($i) => $i->cerrado);

            return [
                'numero' => $dia,
                'titulo' => $config['titulo'],
                'cuotas' => collect($config['cuotas'])
                    ->map(fn (int $cantidad, string $prefijo) => [
                        'curso' => PerfilExamenCni::CURSOS[$prefijo][0], 'cantidad' => $cantidad,
                    ])->values(),
                'total' => array_sum($config['cuotas']),
                'minutos' => PerfilExamenCni::UNI_MINUTOS,
                'estado' => $enCurso ? 'en_curso' : ($ultimoCerrado ? 'finalizado' : 'disponible'),
                'en_curso' => $enCurso,
                'ultimo' => $ultimoCerrado,
                'cantidad_intentos' => $delDia->count(),
            ];
        });

        return [
            'dias' => $dias,
            'total_preguntas' => $dias->sum('total'),
            // Un día cuenta como completado si tiene al menos un intento cerrado.
            'dias_completados' => $dias->filter(fn ($d) => $d['ultimo'] !== null)->count(),
            'intentos' => $intentos,
            'historial' => $intentos->filter(fn ($i) => $i->cerrado)->values(),
            'resumen' => $this->resumen($dias),
        ];
    }

    /** Todos los intentos de simulacro del alumno (los más recientes primero), con su día UNI si lo tiene. */
    private function intentos(Usuario $usuario): Collection
    {
        $ahora = now();

        return DB::table('intentos as i')
            ->leftJoin('simulacros as s', 's.id', '=', 'i.simulacro_id')
            ->where('i.usuario_id', $usuario->id)
            ->where('i.tipo', 'simulacro')
            ->orderByDesc('i.fecha_inicio')->orderByDesc('i.id')
            ->limit(200)
            ->get([
                'i.id', 'i.simulacro_id', 'i.fecha_inicio', 'i.fecha_limite', 'i.fecha_fin', 'i.estado',
                'i.total_preguntas', 'i.puntaje_total', 'i.correctas', 'i.incorrectas', 'i.en_blanco',
                'i.doble_marca', 's.nombre as simulacro_nombre', 's.puntaje_correcta',
            ])
            ->map(function ($intento) use ($ahora) {
                $intento->dia = PerfilExamenCni::diaUniDeNombre($intento->simulacro_nombre);
                $intento->examen = $intento->dia
                    ? 'Día '.$intento->dia.' · '.PerfilExamenCni::UNI_DIAS[$intento->dia]['titulo']
                    : ($intento->simulacro_nombre ?? 'Simulacro');
                $limite = $intento->fecha_limite ? Carbon::parse($intento->fecha_limite) : null;
                // Un intento abierto pero vencido se considera cerrado; el servidor lo cierra al consultarlo.
                $vencido = $intento->estado === 'en_curso' && $limite && $ahora->gte($limite);
                $intento->en_curso = $intento->estado === 'en_curso' && ! $vencido;
                $intento->cerrado = in_array($intento->estado, self::ESTADOS_CERRADOS, true) || $vencido;
                $intento->segundos_restantes = $intento->en_curso && $limite
                    ? (int) max(0, $ahora->diffInSeconds($limite, false)) : null;
                $intento->respondidas = null;
                $intento->calificado = $intento->puntaje_total !== null;
                $intento->maximo = (int) $intento->total_preguntas * (float) ($intento->puntaje_correcta ?? 0);

                return $intento;
            })
            ->pipe(function (Collection $intentos) {
                // Respondidas reales por intento, en una sola consulta.
                $conteo = DB::table('respuestas_estudiante')
                    ->whereIn('intento_id', $intentos->pluck('id'))
                    ->whereNotNull('alternativa_id')
                    ->selectRaw('intento_id, COUNT(*) as total')->groupBy('intento_id')
                    ->pluck('total', 'intento_id');

                return $intentos->each(fn ($i) => $i->respondidas = (int) ($conteo[$i->id] ?? 0));
            });
    }

    /**
     * Resumen acumulado solo cuando los tres días tienen un intento calificado.
     * Suma el último intento calificado de cada día; es puntaje del simulador, no puntaje oficial UNI.
     */
    private function resumen(Collection $dias): ?array
    {
        $ultimos = $dias->map(fn ($d) => $d['ultimo'])->filter(fn ($i) => $i && $i->calificado);
        if ($ultimos->count() !== $dias->count()) {
            return null;
        }

        return [
            'preguntas' => (int) $ultimos->sum('total_preguntas'),
            'respondidas' => (int) $ultimos->sum('respondidas'),
            'correctas' => (int) $ultimos->sum('correctas'),
            'incorrectas' => (int) $ultimos->sum('incorrectas'),
            'puntaje' => round((float) $ultimos->sum(fn ($i) => (float) $i->puntaje_total), 3),
            'maximo' => round((float) $ultimos->sum('maximo'), 3),
        ];
    }
}
