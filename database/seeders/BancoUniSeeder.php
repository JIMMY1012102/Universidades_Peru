<?php

namespace Database\Seeders;

use App\Support\PerfilExamenCni;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Banco base del examen UNI (tres días). Solo agrega lo que falta:
 * una pregunta con el mismo código (UNI_AM_01, …) nunca se modifica ni se duplica,
 * así no se alteran intentos ni respuestas ya registradas.
 */
class BancoUniSeeder extends Seeder
{
    private const LETRAS = ['A', 'B', 'C', 'D', 'E'];

    public function run(): void
    {
        $this->call(CatalogoBancoCniSeeder::class);
        $banco = require __DIR__.'/data/banco_uni.php';
        $creadas = 0;

        DB::transaction(function () use ($banco, &$creadas) {
            $ahora = now();
            foreach ($banco as $prefijo => $preguntas) {
                $areaId = DB::table('areas')->where('nombre', PerfilExamenCni::CURSOS[$prefijo][0])->value('id');
                $temaId = DB::table('temas')->where('area_id', $areaId)->orderBy('id')->value('id');

                foreach (array_values($preguntas) as $i => [$enunciado, $correcta, $distractores, $solucion]) {
                    $codigo = sprintf('UNI_%s_%02d', $prefijo, $i + 1);
                    if (DB::table('preguntas')->where('codigo', $codigo)->exists()) {
                        continue;
                    }
                    $preguntaId = DB::table('preguntas')->insertGetId([
                        'codigo' => $codigo, 'tema_id' => $temaId, 'enunciado' => $enunciado,
                        'nivel' => 'intermedio', 'solucion' => $solucion, 'activo' => true,
                        'created_at' => $ahora, 'updated_at' => $ahora,
                    ]);
                    // La clave rota entre A–E de forma fija para que no se concentre en una letra.
                    $posicion = ($i * 3 + strlen($prefijo) + ord($prefijo[0])) % 5;
                    $textos = $distractores;
                    array_splice($textos, $posicion, 0, [$correcta]);
                    foreach ($textos as $j => $texto) {
                        DB::table('alternativas')->insert([
                            'pregunta_id' => $preguntaId, 'letra' => self::LETRAS[$j], 'texto' => $texto,
                            'es_correcta' => $j === $posicion, 'created_at' => $ahora, 'updated_at' => $ahora,
                        ]);
                    }
                    $creadas++;
                }
            }
        });

        $this->command?->info("Banco UNI: {$creadas} preguntas nuevas.");
    }
}
