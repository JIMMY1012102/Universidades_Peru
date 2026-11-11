@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/undc-panel.css') }}?v=1">
@endpush

@section('content')
@php
    // Solo se muestran simulacros UNDC publicados y autorizados para este estudiante.
    $simulacrosUndc = collect($simulacros)->filter(
        fn (array $s) => str_starts_with((string) ($s['nombre'] ?? ''), 'UNDC - Admisión Ordinaria Tipo ')
    );
    $tipoA = $simulacrosUndc->first(fn (array $s) => str_contains($s['nombre'], 'Tipo A'));
    $tipoB = $simulacrosUndc->first(fn (array $s) => str_contains($s['nombre'], 'Tipo B'));
    $idsUndc = $simulacrosUndc->pluck('id')->map(fn ($id) => (int) $id)->all();
    $historialUndc = $intentos->getCollection()->filter(
        fn ($i) => in_array((int) $i->simulacro_id, $idsUndc, true)
    );
    $nombreEstudiante = trim($usuario->nombres.' '.($usuario->apellidos ?? ''));
    $logoUndc = 'https://web.undc.edu.pe/wp-content/uploads/2026/04/Logo-UNDC_Horizontal_Letras-Blancas_Fondo-Transparente-1.png';
@endphp

<div class="ud-shell">
    <aside class="ud-sidebar" aria-label="Navegación UNDC">
        <a class="ud-brand" href="{{ route('alumno.panel') }}">
            <img src="{{ $logoUndc }}" alt="Escudo Universidad Nacional de Cañete" loading="eager">
            <span><strong>UNDC</strong><small>Simulador de admisión</small></span>
        </a>

        <div class="ud-sidebar-label">PORTAL DEL POSTULANTE</div>
        <nav class="ud-nav" aria-label="Secciones del panel">
            <a href="#inicio" class="ud-active">⌂ <span>Inicio</span></a>
            <a href="#examenes">▤ <span>Exámenes Tipo A y B</span></a>
            <a href="#resultados">◷ <span>Mis resultados</span></a>
        </nav>

        <div class="ud-sidebar-bottom">
            <p>Universidad Nacional de Cañete</p>
            <small>Plataforma académica de preparación · No es un portal oficial de admisión.</small>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Cerrar sesión →</button>
            </form>
        </div>
    </aside>

    <div class="ud-main">
        <header class="ud-topbar" id="inicio">
            <div><span class="ud-eyebrow">BIENVENIDO AL SIMULADOR</span><h1>Panel del postulante</h1></div>
            <div class="ud-avatar" title="{{ $nombreEstudiante }}">{{ mb_strtoupper(mb_substr($usuario->nombres, 0, 1)) }}</div>
        </header>

        <main class="ud-content">
            @if(session('status'))
                <div class="ud-message" role="status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="ud-message ud-message-error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="ud-hero" aria-labelledby="ud-hero-title">
                <div class="ud-hero-text">
                    <span class="ud-pill">UNIVERSIDAD NACIONAL DE CAÑETE</span>
                    <h2 id="ud-hero-title">Tu camino a la universidad comienza aquí.</h2>
                    <p>¡Hola, {{ $usuario->nombres }}! Prepárate con dos modelos de examen, distribuidos por asignatura y basados en nuestro banco de preguntas de práctica.</p>
                    <a class="ud-hero-link" href="#examenes">Elegir un examen <span aria-hidden="true">→</span></a>
                </div>
                <div class="ud-hero-emblem" aria-hidden="true">
                    <img src="{{ $logoUndc }}" alt="" loading="lazy">
                    <span>UNDC</span>
                </div>
            </section>

            <div class="ud-stats" aria-label="Resumen de la preparación">
                <div><span>Modalidades disponibles</span><strong>02</strong><small>Tipo A y Tipo B</small></div>
                <div><span>Preguntas por examen</span><strong>100</strong><small>19 asignaturas por perfil</small></div>
                <div><span>Tu historial</span><strong>{{ $historialUndc->count() }}</strong><small>Intentos visibles en esta página</small></div>
            </div>

            <section id="examenes" class="ud-section" aria-labelledby="ud-examenes-title">
                <div class="ud-section-heading">
                    <div><span class="ud-eyebrow">PRÁCTICA DE ADMISIÓN</span><h2 id="ud-examenes-title">Elige tu tipo de examen</h2></div>
                    <span class="ud-count">2 perfiles</span>
                </div>
                <div class="ud-exams">
                    @foreach(['A' => $tipoA, 'B' => $tipoB] as $tipo => $examen)
                        <article class="ud-exam-card">
                            <div class="ud-exam-top"><span class="ud-exam-symbol">0{{ $tipo === 'A' ? '1' : '2' }}</span><span class="ud-exam-state">{{ $examen ? 'Disponible' : 'Pendiente de habilitar' }}</span></div>
                            <h3>Examen Tipo {{ $tipo }}</h3>
                            <p>Modelo de práctica para el proceso de admisión UNDC, con distribución por asignaturas.</p>
                            <div class="ud-exam-facts">
                                <span>▤ {{ $examen['total_preguntas'] ?? 100 }} preguntas</span>
                                <span>◷ {{ $examen['tiempo_limite'] ?? 180 }} min {{ $examen ? '' : '(previstos)' }}</span>
                                <span>◇ 5 alternativas</span>
                            </div>
                            @if($examen)
                                <form method="POST" action="{{ route('examen.iniciar', ['simulacro' => $examen['id']]) }}">
                                    @csrf
                                    <button class="ud-start" type="submit">Comenzar examen Tipo {{ $tipo }} <span aria-hidden="true">→</span></button>
                                </form>
                            @else
                                <span class="ud-disabled">El banco aún no está habilitado en esta instalación.</span>
                            @endif
                        </article>
                    @endforeach
                </div>
                <p class="ud-notice">Los ejercicios y la puntuación son de demostración educativa; no constituyen un examen oficial de la UNDC. El tiempo y las reglas deberán verificarse con las bases vigentes.</p>
            </section>

            <section id="resultados" class="ud-section" aria-labelledby="ud-resultados-title">
                <div class="ud-section-heading"><div><span class="ud-eyebrow">SEGUIMIENTO</span><h2 id="ud-resultados-title">Mis resultados</h2></div></div>
                <div class="ud-history">
                    @forelse($historialUndc as $intento)
                        <div class="ud-history-item">
                            <span><strong>Intento #{{ $intento->id }}</strong><small>{{ $intento->fecha_inicio }}</small></span>
                            <span class="ud-history-score">{{ $intento->puntaje_total === null ? 'En curso' : number_format((float) $intento->puntaje_total, 2).' pts' }}</span>
                            @if($intento->estado === 'en_curso')
                                <a href="{{ route('examen', ['intento' => $intento->id]) }}">Continuar →</a>
                            @else
                                <a href="{{ route('alumno.resultados', ['intento' => $intento->id]) }}">Ver resultado →</a>
                            @endif
                        </div>
                    @empty
                        <div class="ud-empty">Aún no tienes resultados UNDC. Comienza un examen para registrar tu primer intento.</div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</div>
@endsection
