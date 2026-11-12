<section class="unfv-hero" aria-labelledby="bienvenida-titulo">
    <div class="unfv-hero-copy">
        <span class="unfv-eyebrow"><span aria-hidden="true"></span> TU ESPACIO DE PREPARACIÓN</span>
        <h1 id="bienvenida-titulo">¡Hola, {{ $primeraPalabra }}!<br><span>Vamos por tu ingreso.</span></h1>
        <h2>Bienvenido a Villarreal</h2>
        <p>{{ $temaArea['descripcion'] }}</p>
        <div class="unfv-hero-facts" aria-label="Formato del examen">
            <div><strong>50</strong><span>preguntas</span></div>
            <div><strong>90 <small>min</small></strong><span>por intento</span></div>
            <div><strong>01</strong><span>examen general</span></div>
        </div>
    </div>
    <figure class="unfv-emblem">
        <img src="{{ asset($rutaMascota).'?v='.filemtime(public_path($rutaMascota)) }}" width="200" height="300" alt="Emblema de la Universidad Nacional Federico Villarreal">
        <figcaption>FEDERICO VILLARREAL <span>Tu objetivo, más cerca.</span></figcaption>
    </figure>
</section>

<div class="unfv-profile-grid">
    <section class="unfv-exam-card" aria-labelledby="unfv-exam-title">
        <div class="unfv-card-heading">
            <span class="unfv-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 3h8l3 3v15H5V3h3Zm7 0v5h4M8 12h8M8 16h5"/></svg></span>
            <div><span class="unfv-eyebrow">TU SIGUIENTE PASO</span><h2 id="unfv-exam-title">Prepara tu examen</h2></div>
            <span class="unfv-year">2026</span>
        </div>
        <p class="unfv-card-description">Tu examen general de práctica reúne habilidades y conocimientos. Concéntrate en resolverlo; nosotros preparamos las preguntas.</p>
        <div class="unfv-exam-format" aria-label="Formato del examen general">
            <span><strong>50</strong> preguntas</span>
            <span><strong>90</strong> minutos</span>
            <span>Preguntas aleatorias</span>
        </div>
        <p id="unfv-exam-help" class="unfv-help">Entra directamente al examen. Si tienes un intento en curso, puedes reanudarlo mientras su tiempo siga vigente.</p>
        <button class="unfv-start" type="submit" form="examen-independiente" @disabled($esVistaPrevia)>Comenzar examen <span aria-hidden="true">→</span></button>
        <a class="unfv-source" href="{{ \App\Support\PerfilExamenUnfv::FUENTE }}" target="_blank" rel="noopener noreferrer">Ver prospecto UNFV 2026 <span aria-hidden="true">↗</span><span class="unfv-sr-only"> (PDF, abre en otra pestaña)</span></a>
    </section>

    <section class="unfv-summary-card" aria-labelledby="unfv-summary-title">
        <div class="unfv-card-heading"><span class="unfv-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V5m0 14h16M8 15l4-5 4 2 4-7"/></svg></span><h2 id="unfv-summary-title">Tu preparación</h2></div>
        <div class="unfv-summary-number"><strong>{{ $progreso['cantidad'] }}</strong><span>{{ $progreso['cantidad'] === 1 ? 'simulacro calificado' : 'simulacros calificados' }}<small>{{ $progreso['ultimo'] ? 'Cada intento cuenta en tu progreso.' : 'Tu primer paso empieza hoy.' }}</small></span></div>
        @if($progreso['ultimo'])
            <div class="unfv-last-result"><span>Último resultado</span><strong>{{ number_format($progreso['ultimo']['porcentaje'], 1, ',', '.') }}% <small>del puntaje máximo</small></strong></div>
        @else
            <p class="unfv-summary-empty">Completa tu primer simulacro para ver cómo avanzas. Aquí aparecerán tus resultados reales.</p>
        @endif
        <a href="#progreso" data-go-panel="#progreso" class="unfv-summary-link">Ver mi progreso <span aria-hidden="true">↗</span></a>
    </section>
</div>

<section class="cni-alumno-datos unfv-student-card" aria-labelledby="datos-alumno-titulo">
    <div class="unfv-card-heading"><span class="unfv-card-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></span><h2 id="datos-alumno-titulo">Datos del alumno</h2></div>
    <dl class="cni-alumno-datos-grid">
        <div><dt>Nombre completo</dt><dd>{{ $nombreCompleto ?: 'No registrado' }}</dd></div>
        <div><dt>Nombre de usuario</dt><dd>{{ $usuario->usuario ?: 'No registrado' }}</dd></div>
        <div><dt>Grado</dt><dd>{{ $seccionAlumno?->grado ?? 'No asignado' }}</dd></div>
        <div><dt>Sección</dt><dd>{{ $seccionAlumno?->nombre ?? 'No asignada' }}</dd></div>
    </dl>
</section>
