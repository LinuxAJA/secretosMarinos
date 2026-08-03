<?php /** Ranking público Top 10 por puntos ecológicos. */ ?>
<section class="section" aria-labelledby="ranking-title">
    <div class="container">
        <p class="panel-kicker"><?= e(APP_NAME) ?></p>
        <h1 id="ranking-title" class="section__title">Ranking ecológico</h1>
        <p class="section__lead">
            Los participantes con más puntos por reportes y acciones en la plataforma.
        </p>

        <?php if (!$items): ?>
            <p class="empty-state">Aún no hay puntuaciones registradas.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table ranking-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Participante</th>
                            <th>Rol</th>
                            <th>Puntos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= e($row['nombre']) ?></strong></td>
                                <td><?= e($row['rol']) ?></td>
                                <td><?= (int) $row['puntos'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p class="panel-actions" style="margin-top: var(--space-4)">
            <a class="btn btn--secondary" href="<?= url('/insignias') ?>">Ver insignias</a>
            <a class="btn btn--primary" href="<?= url('/reportes/crear') ?>">Reportar y sumar puntos</a>
        </p>
    </div>
</section>
