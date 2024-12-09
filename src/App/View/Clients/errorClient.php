<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en Cliente</title>
</head>
<body>
<h2>Ha ocurrido un error</h2>
<ul>
    <?php if (!empty($errores)): ?>
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    <?php else: ?>
        <li>No se encontraron errores.</li>
    <?php endif; ?>
</ul>
</body>
</html>
