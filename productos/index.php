<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";


$empresa_id = filter_input(INPUT_GET, "empresa_id", FILTER_VALIDATE_INT);

if (!$empresa_id || $empresa_id < 1) {
    exit("ID de empresa no válido.");
}

$sql = "SELECT id, razon_social
        FROM empresas
        WHERE id = :id AND usuario_id = :usuario_id
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("id" => $empresa_id, "usuario_id" => $_SESSION["usuario_id"]));
$empresa = $stmt->fetch();

if (!$empresa) {
    http_response_code(404);
    exit("Empresa no encontrada o sin permiso.");
}

$sql = "SELECT id, nombre, precio
        FROM productos
        WHERE empresa_id = :empresa_id
        ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("empresa_id" => $empresa_id));
$productos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos</title>
</head>
<body>
    <h1>Productos de <?php echo h($empresa["razon_social"]); ?></h1>
    <p><a href="../index.php">Volver al panel</a></p>
    <p><a href="../empresas/index.php">Volver a empresas</a></p>
    <p>
        <a href="crear.php?empresa_id=<?php echo (int)$empresa_id; ?>">
        Registrar producto</a>
    </p>

<?php if (count($productos) === 0) { ?>
<p>Esta empresa todavía no tiene productos.</p>

<?php } else { ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio (CLP)</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($productos as $producto) { ?>
            <tr>
                <td><?php echo (int)$producto["id"]; ?></td>
                <td><?php echo h($producto["nombre"]); ?></td>
                <td><?php echo number_format((float)$producto["precio"], 0, ",", "."); ?></td>

                <td>
                    <a href="editar.php?id=<?php echo (int)$producto["id"]; ?>&empresa_id=<?php echo (int)$empresa_id; ?>">Editar</a>
                    <form method="POST" action="eliminar.php" style="display:inline"
                          onsubmit="return confirm('¿Eliminar este producto?');">
                          <input type="hidden" name="id" value="<?php echo (int)$producto["id"]; ?>">
                          <input type="hidden" name="empresa_id" value="<?php echo (int)$empresa_id; ?>">
                          <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        
    </table>

<?php } ?>

</body>
</html>