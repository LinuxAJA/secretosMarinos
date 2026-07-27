<?php
/**
 * ============================================================================
 * views/pages/home.php — Vista de la página de inicio
 * ============================================================================
 * Recibe del HomeController:
 *   $heroTitle, $heroText, $pageTitle
 *
 * El layout ya abrió <main>; aquí solo va el contenido de la sección.
 * ============================================================================
 */
?>
<!-- HERO: una sola composición a pantalla completa (marca + mensaje + CTA) -->
<section class="hero" aria-labelledby="hero-title">
    <div class="hero__media" aria-hidden="true"></div>

    <div class="container hero__content">
        <p class="hero__brand"><?= e(APP_NAME) ?></p>
        <h1 id="hero-title" class="hero__title"><?= e($heroTitle) ?></h1>
        <p class="hero__text"><?= e($heroText) ?></p>

        <div class="hero__actions">
            <a class="btn btn--primary" href="<?= url('/educacion') ?>">Explorar biblioteca</a>
            <a class="btn btn--ghost" href="<?= url('/reportes') ?>">Reportar un problema</a>
        </div>
    </div>
</section>

<!-- SECCIÓN: qué ofrece la plataforma (un propósito claro) -->
<section class="section" aria-labelledby="modulos-title">
    <div class="container">
        <h2 id="modulos-title" class="section__title">Aprende y actúa desde un mismo lugar</h2>
        <p class="section__lead">
            Contenidos educativos, fichas de especies, campañas comunitarias y reportes ambientales
            para convertir conocimiento marino en acciones concretas.
        </p>

        <div class="feature-grid">
            <article class="feature">
                <h3 class="feature__title">Biblioteca marina</h3>
                <p class="feature__text">Artículos, guías y rutas de aprendizaje sobre océanos y conservación.</p>
                <p><a href="<?= url('/educacion') ?>">Explorar educación</a></p>
            </article>
            <article class="feature">
                <h3 class="feature__title">Noticias ambientales</h3>
                <p class="feature__text">Novedades, campañas y descubrimientos para mantenerte informado.</p>
                <p><a href="<?= url('/noticias') ?>">Ver noticias</a></p>
            </article>
            <article class="feature">
                <h3 class="feature__title">Especies y ecosistemas</h3>
                <p class="feature__text">Fichas científicas sobre biodiversidad, hábitats y conservación.</p>
                <p>
                    <a href="<?= url('/especies') ?>">Ver especies</a>
                    · <a href="<?= url('/ecosistemas') ?>">Ver ecosistemas</a>
                </p>
            </article>
            <article class="feature">
                <h3 class="feature__title">Campañas y reportes</h3>
                <p class="feature__text">Participación ciudadana con seguimiento de casos y acciones colectivas.</p>
                <p>
                    <a href="<?= url('/campanias') ?>">Ver campañas</a>
                    · <a href="<?= url('/reportes') ?>">Ver reportes</a>
                </p>
            </article>
        </div>
    </div>
</section>
