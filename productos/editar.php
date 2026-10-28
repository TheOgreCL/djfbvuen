<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$empresa_id = filter_input(INPUT_GET, "empresa_id", FILTER_VALIDATE_INT);

if (!$id || $id < 1 || !$empresa_id || $empresa_id < 1) {
    exit("ID no válido.");
}

$sql = "SELECT p.id, p.nombre, p.precio, p.descripcion, e.razon_social
        FROM productos p
        INNER JOIN empresas e ON p.empresa_id = e.id
        WHERE p.id = :id
          AND e.id = :empresa_id
          AND e.usuario_id = :usuario_id
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute(array(
    "id" => $id,
    "empresa_id" => $empresa_id,
    "usuario_id" => $_SESSION["usuario_id"]
));

$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(404);
    exit("Producto no encontrado o sin permiso.");
}

$mensaje = "";
$nombre = $producto["nombre"];
$precio = $producto["precio"];
$descripcion = $producto["descripcion"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    if ($nombre === "" || $precio === "" || $descripcion === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif (strlen($nombre) > 200 || strlen($descripcion) > 2000) {
        $mensaje = "El nombre o la descripción son demasiado largos.";
    } elseif (!preg_match('/^[0-9]+$/', $precio) || (float)$precio <= 0) {
        $mensaje = "El precio debe ser un número entero mayor que cero.";
    } elseif (strlen($precio) > 15) {
        $mensaje = "El precio es demasiado grande.";
    } else {
        $sql = "UPDATE productos
                SET nombre = :nombre,
                    precio = :precio,
                    descripcion = :descripcion
                WHERE id = :id
                  AND empresa_id = :empresa_id
                  AND EXISTS (
                      SELECT 1 FROM empresas
                      WHERE empresas.id = productos.empresa_id
                        AND empresas.usuario_id = :usuario_id
                  )";

        $stmt = $conexion->prepare($sql);
        $stmt->execute(array(
            "nombre" => $nombre,
            "precio" => $precio,
            "descripcion" => $descripcion,
            "id" => $id,
            "empresa_id" => $empresa_id,
            "usuario_id" => $_SESSION["usuario_id"]
        ));

        header("Location: index.php?empresa_id=" . $empresa_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar producto</title>
</head>
<body>
    <h1>Editar producto</h1>

    <p>Empresa: <?php echo h($producto["razon_social"]); ?></p>
    <p><?php echo h($mensaje); ?></p>

    <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas guardar los cambios de este producto?');">

        <label>Nombre:</label><br>
        <input type="text" name="nombre" maxlength="200"
               value="<?php echo h($nombre); ?>" required>
        <br><br>

        <label>Precio en pesos chilenos:</label><br>
        <input type="number" name="precio" min="1" step="1"
               value="<?php echo h($precio); ?>" required>
        <br><br>

        <label>Descripción del producto:</label><br>
        <textarea name="descripcion" rows="4" cols="40"
                  maxlength="2000" required><?php echo h($descripcion); ?></textarea>
        <br><br>

        <button type="submit">Guardar cambios</button>
    </form>

    <p>
        <a href="index.php?empresa_id=<?php echo (int)$empresa_id; ?>">
            Volver a productos
        </a>
    </p>
</body>
</html>