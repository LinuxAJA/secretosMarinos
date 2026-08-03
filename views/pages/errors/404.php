<?php
/**
 * Vista de error 404 (ruta no encontrada).
 * Se carga desde Router::abort() cuando no hay coincidencia.
 */
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 | Misterios Del Mar</title>
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">
</head>
<body class="error-page">
    <main class="container error-page__box">
        <h1>404</h1>
        <p>No encontramos esa página en Misterios Del Mar.</p>
        <a class="btn btn--primary" href="<?= url('/') ?>">Volver al inicio</a>
    </main>
</body>
</html>
