<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cliente</title>
</head>
<body>
<h2>Crear Nuevo Cliente</h2>
<form method="post" action="/clients">
    <label for="uuid">UUID:</label>
    <input type="hidden" name="clientuuid" id="uuid" value="<?= $uuid ?>"><br>
    <label for="nombre">Nombre:</label>
    <input type="text" name="clientname" id="nombre" required><br>
    <label for="direccion">Dirección:</label>
    <input type="text" name="clientaddress" id="direccion"><br>
    <label for="coste">Coste:</label>
    <input type="number" name="clientcost" id="coste" step="0.01"><br>
    <label for="abierto">¿Abierto?:</label>
    <select name="clientisopen" id="abierto">
        <option value="1">Sí</option>
        <option value="0">No</option>
    </select><br>
    <input type="submit" value="Crear Cliente">
</form>

</body>
</html>
