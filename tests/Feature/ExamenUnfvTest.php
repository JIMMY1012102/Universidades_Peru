<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Services\Alumno\ExamenIndependienteService;
use App\Services\IntentoService;
use App\Support\PerfilExamenCni;
use App\Support\PerfilExamenUnfv;
use Database\Seeders\AreaPostulacionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExamenUnfvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cni.alumno_areas_enabled' => true]);
        $this->seed(AreaPostulacionSeeder::class);
    }

    private function alumno(string $codigo = 'C'): Usuario
    {
        return Usuario::create([
            'nombres' => 'Alumno', 'apellidos' => 'Prueba', 'usuario' => 'unfv_'.Usuario::count(),
            'password' => 'SoloPruebas!', 'rol' => 'estudiante', 'activo' => true,
            'area_postulacion_id' => DB::table('areas_postulacion')->where('codigo', $codigo)->value('id'),
        ]);
    }

    private function banco(): void
    {
        foreach (PerfilExamenCni::CURSOS as $prefijo => [$nombre, $bloque]) {
            $id = DB::table('areas')->insertGetId(['nombre' => $nombre, 'bloque' => $bloque, 'activo' => true]);
            $tema = DB::table('temas')->insertGetId(['area_id' => $id, 'nombre' => $nombre, 'activo' => true]);
            // Historia solo tiene UNA pregunta de Perú: una división artificial HP/HU fallaría.
            for ($i = 0; $i < ($prefijo === 'HP' ? 1 : 8); $i++) {
                $pregunta = DB::table('preguntas')->insertGetId([
                    'tema_id' => $tema, 'enunciado' => $nombre.' '.$i, 'nivel' => 'intermedio', 'solucion' => 'Solución', 'activo' => true,
                ]);
                foreach (['A', 'B', 'C', 'D', 'E'] as $letra) {
                    DB::table('alternativas')->insert([
                        'pregunta_id' => $pregunta, 'letra' => $letra, 'texto' => $letra, 'es_correcta' => $letra === 'A',
                    ]);
                }
            }
        }
    }

    public function test_cuatro_perfiles_sortean_las_cuotas_oficiales_sin_repeticiones(): void
    {
        $this->banco();
        foreach (PerfilExamenUnfv::CUOTAS as $perfil => $cuotas) {
            $alumno = $this->alumno();
            $intento = app(ExamenIndependienteService::class)->iniciar($alumno, $perfil);
            $this->actingAs($alumno);
            $this->assertNotNull($intento);
            $this->assertSame(50, (int) $intento->total_preguntas);
            $this->assertEquals(Carbon::parse($intento->fecha_inicio)->addMinutes(90), Carbon::parse($intento->fecha_limite));
            $preguntas = app(IntentoService::class)->preguntas($intento->id);
            $this->assertCount(50, $preguntas->pluck('id')->unique());
            foreach ($cuotas as $prefijo => $cantidad) {
                $this->assertCount($cantidad, $preguntas->whereIn('area', PerfilExamenUnfv::cursos($prefijo)));
            }
            $orden = $preguntas->map(fn ($p) => PerfilExamenUnfv::posicion($p->area))->all();
            $ordenado = $orden;
            sort($ordenado);
            $this->assertSame($ordenado, $orden);
            $this->assertDatabaseHas('area_postulacion_simulacro', [
                'simulacro_id' => $intento->simulacro_id, 'area_postulacion_id' => $alumno->area_postulacion_id,
            ]);
            $this->get(route('examen', $intento->id))->assertOk();
        }
        $this->assertDatabaseCount('simulacros', 4);
    }

    public function test_humanidades_no_exige_trigonometria_y_acepta_solo_historia_universal(): void
    {
        $this->banco();
        DB::table('areas')->whereIn('nombre', ['Trigonometría', 'Historia del Perú'])->update(['activo' => false]);
        $intento = app(ExamenIndependienteService::class)->iniciar($this->alumno(), 'humanidades');
        $preguntas = app(IntentoService::class)->preguntas($intento->id);
        $this->assertCount(50, $preguntas);
        $this->assertCount(4, $preguntas->where('area', 'Historia Universal'));
        $this->assertCount(0, $preguntas->where('area', 'Trigonometría'));
    }

    public function test_reanuda_el_mismo_perfil_y_otro_perfil_usa_otra_plantilla(): void
    {
        $this->banco();
        $alumno = $this->alumno();
        $servicio = app(ExamenIndependienteService::class);
        $primero = $servicio->iniciar($alumno, 'salud');
        $ids = app(IntentoService::class)->preguntas($primero->id)->pluck('id')->all();
        $this->travel(2)->minutes();
        $reanudado = $servicio->iniciar($alumno, 'salud');
        $this->assertSame($primero->id, $reanudado->id);
        $this->assertSame($primero->fecha_limite, $reanudado->fecha_limite);
        $this->assertSame($ids, app(IntentoService::class)->preguntas($reanudado->id)->pluck('id')->all());
        $otro = $servicio->iniciar($alumno, 'humanidades');
        $this->assertNotSame($primero->simulacro_id, $otro->simulacro_id);
        $this->assertSame($ids, app(IntentoService::class)->preguntas($primero->id)->pluck('id')->all());
    }

    public function test_la_ruta_general_ignora_perfiles_y_cantidades_enviados_por_el_navegador(): void
    {
        $this->banco();
        $alumno = $this->alumno();
        $this->actingAs($alumno)->post(route('examen.independiente'), [
            'perfil_unfv' => ['inventado'], 'total_preguntas' => 999, 'area_id' => 999,
        ])->assertRedirect();
        $intento = DB::table('intentos')->where('usuario_id', $alumno->id)->first();
        $this->assertSame(50, (int) $intento->total_preguntas);
        $preguntas = app(IntentoService::class)->preguntas($intento->id);
        foreach (PerfilExamenUnfv::CUOTAS['ingenierias'] as $prefijo => $cantidad) {
            $this->assertCount($cantidad, $preguntas->whereIn('area', PerfilExamenUnfv::cursos($prefijo)));
        }
    }

    public function test_banco_insuficiente_informa_el_curso_sin_crear_un_examen_parcial(): void
    {
        $this->banco();
        DB::table('areas')->where('nombre', 'Biología')->update(['activo' => false]);
        $this->actingAs($this->alumno())->post(route('examen.independiente'), ['perfil_unfv' => 'salud'])
            ->assertRedirect(route('alumno.panel'))->assertSessionHasErrors('preguntas');
        $this->get(route('alumno.panel'))->assertOk()->assertSee('Biología');
        $this->assertDatabaseCount('simulacros', 0);
        $this->assertDatabaseCount('intentos', 0);
        $this->assertDatabaseCount('respuestas_estudiante', 0);
    }

    public function test_villarreal_ofrece_examen_general_sin_selector_de_carrera_o_area(): void
    {
        $this->actingAs($this->alumno())->get(route('alumno.panel'))->assertOk()
            ->assertSee('50')->assertSee('90')->assertSee('examen general')
            ->assertSee('form="examen-independiente"', false)->assertDontSee('name="perfil_unfv"', false)
            ->assertDontSee('Área académica de la Villarreal');
        $this->actingAs($this->alumno('E'))->get(route('alumno.panel'))->assertOk()
            ->assertSee('100 preguntas')->assertDontSee('Área académica de la Villarreal');
    }
}
