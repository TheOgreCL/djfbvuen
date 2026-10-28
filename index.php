<?php

require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/funciones.php";
require_once __DIR__ . "/conexion.php";

$sql = "SELECT id, razon_social, rut, giro
        FROM empresas
        WHERE usuario_id = :usuario_id
        ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("usuario_id" => $_SESSION["usuario_id"]
));
$empresas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel administrativo</title>
</head>
<body>
    <h1>Panel administrativo</h1>
    <p>Bienvenido, <?php echo h($_SESSION["usuario_nombre"]); ?></p>
    <p><a href="cerrar_sesion.php" onclick="return confirm('¿Estás seguro de que deseas cerrar sesión?');">Cerrar sesión</a></p>
    <hr>
    <h2>Empresas</h2>
    <p><a href="empresas/crear.php">Registrar empresa</a></p>
    <p><a href="empresas/index.php">Administrar empresas</a></p>
    <h2>Mis empresas</h2>

<?php if (count($empresas) === 0) { ?>
    <p>Aún no tienes empresas registradas.</p>

<?php } else { ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Razón social</th>
            <th>RUT</th>
            <th>Giro</th>
            <th>Productos</th>
        </tr>
        <?php foreach ($empresas as $empresa) { ?>

        <tr>
            <td><?php echo h($empresa["razon_social"]); ?></td>
            <td><?php echo h($empresa["rut"]); ?></td>
            <td><?php echo h($empresa["giro"]); ?></td>
            <td>
                <a href="productos/index.php?empresa_id=<?php echo (int)$empresa["id"]; ?>">
                Ver productos
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>
<?php } ?>

</body>
</html>