<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
</head>
<body>
<h2>Editar Cliente</h2>
<form method="post" action="/clients/<?= htmlspecialchars($cliente->getUuid()) ?>">
    <label for="clientuuid">UUID del Cliente:</label>
    <input type="text" id="clientuuid" name="clientuuid" value="<?= htmlspecialchars($cliente->getUuid()) ?>" readonly><br><br>

    <label for="clientname">Nombre:</label>
    <input type="text" id="clientname" name="clientname" value="<?= htmlspecialchars($cliente->getNombre()) ?>" required><br><br>

    <label for="clientaddress">Dirección:</label>
    <input type="text" id="clientaddress" name="clientaddress" value="<?= htmlspecialchars($cliente->getDireccion()) ?>"><br><br>

    <label for="clientisopen">¿Está Abierto?:</label>
    <select id="clientisopen" name="clientisopen">
        <option value="1" <?= $cliente->isAbierto() ? 'selected' : '' ?>>Sí</option>
        <option value="0" <?= !$cliente->isAbierto() ? 'selected' : '' ?>>No</option>
    </select><br><br>

    <label for="clientcost">Costo:</label>
    <input type="number" id="clientcost" name="clientcost" value="<?= htmlspecialchars($cliente->getCoste()) ?>" step="0.01" required><br><br>

    <button type="submit">Guardar Cambios</button>
</form>
</body>
</html>
