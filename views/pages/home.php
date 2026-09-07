<?php
/**
 * ============================================================================
 * views/pages/home.php — Inicio (Paso 8 · Bioluminiscencia marina)
 * ============================================================================
 * Hero fotográfico + ken burns + onda SVG → tiles de plataforma → CTA --sol.
 * Sin cambios de negocio; solo presentación.
 * ============================================================================
 */
?>
<section
    class="hero-photo"
    aria-labelledby="hero-title"
    style="--hero-image: url('<?= e(asset('img/hero-ocean.jpg')) ?>')"
>
    <div class="hero-photo__bg" aria-hidden="true"></div>

    <div class="container hero-photo__content">
        <p class="kicker"><?= e(APP_NAME) ?></p>
        <h1 id="hero-title" class="hero-photo__title">
            Descubre, aprende y <em>protege</em> el océano
        </h1>
        <p class="hero-photo__lead"><?= e($heroText) ?></p>

        <div class="hero-photo__actions">
            <a class="btn btn--sol" href="<?= url('/educacion') ?>">Explorar biblioteca</a>
            <a class="btn btn--ghost" href="<?= url('/reportes') ?>">Reportar un problema</a>
        </div>
    </div>

    <div class="wave" aria-hidden="true">
        <svg viewBox="0 0 1440 90" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,48 C240,90 480,0 720,36 C960,72 1200,18 1440,48 L1440,90 L0,90 Z"></path>
        </svg>
    </div>
</section>

<section class="platform-zone" aria-labelledby="modulos-title">
    <div class="container">
        <div class="platform-zone__head">
            <p class="kicker">Plataforma</p>
            <h2 id="modulos-title">Aprende y actúa desde un mismo lugar</h2>
            <p class="platform-zone__lead">
                Contenidos educativos, fichas de especies, campañas comunitarias y reportes ambientales
                para convertir conocimiento marino en acciones concretas.
            </p>
        </div>

        <div class="tiles">
            <article class="tile">
                <span class="tile__num">01</span>
                <h3>Biblioteca marina</h3>
                <p>Artículos, guías y rutas de aprendizaje sobre océanos y conservación.</p>
                <a class="tile__link" href="<?= url('/educacion') ?>">Explorar educación</a>
            </article>

            <article class="tile">
                <span class="tile__num">02</span>
                <h3>Noticias ambientales</h3>
                <p>Novedades, campañas y descubrimientos para mantenerte informado.</p>
                <a class="tile__link" href="<?= url('/noticias') ?>">Ver noticias</a>
            </article>

            <article class="tile">
                <span class="tile__num">03</span>
                <h3>Especies y ecosistemas</h3>
                <p>Fichas científicas sobre biodiversidad, hábitats y conservación.</p>
                <div class="tile__links">
                    <a class="tile__link" href="<?= url('/especies') ?>">Especies</a>
                    <a class="tile__link" href="<?= url('/ecosistemas') ?>">Ecosistemas</a>
                </div>
            </article>

            <article class="tile">
                <span class="tile__num">04</span>
                <h3>Campañas y reportes</h3>
                <p>Participación ciudadana con seguimiento de casos y acciones colectivas.</p>
                <div class="tile__links">
                    <a class="tile__link" href="<?= url('/campanias') ?>">Campañas</a>
                    <a class="tile__link" href="<?= url('/reportes') ?>">Reportes</a>
                </div>
            </article>
        </div>
    </div>
</section>

<section
    class="cta-band"
    aria-labelledby="cta-title"
    style="--cta-image: url('<?= e(asset('img/band-cta.jpg')) ?>')"
>
    <div class="container">
        <h2 id="cta-title">El océano necesita tu mirada</h2>
        <p>
            Únete a campañas comunitarias o reporta un hallazgo ambiental. Cada acción cuenta.
        </p>
        <div class="hero-photo__actions">
            <a class="btn btn--sol" href="<?= url('/campanias') ?>">Ver campañas</a>
            <a class="btn btn--ghost" href="<?= url('/reportes/crear') ?>">Crear reporte</a>
        </div>
    </div>
</section>
