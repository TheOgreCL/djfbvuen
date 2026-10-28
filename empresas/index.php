<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$sql = "SELECT id, razon_social, rut, giro
        FROM empresas
        WHERE usuario_id = :usuario_id
        ORDER BY id DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("usuario_id" => $_SESSION["usuario_id"]));
$empresas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis empresas</title>
</head>
<body>
    <h1>Mis empresas</h1>
    <p><a href="../index.php">Volver al panel</a></p>
    <p><a href="crear.php">Registrar empresa</a></p>
    
    <?php if (count($empresas) === 0) { ?>
    <p>No hay empresas registradas.</p>
    
    <?php } else { ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Razón social</th>
            <th>RUT</th>
            <th>Giro</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($empresas as $empresa) { ?>
            <tr>
                <td><?php echo (int)$empresa["id"]; ?></td>
                <td><?php echo h($empresa["razon_social"]); ?></td>
                <td><?php echo h($empresa["rut"]); ?></td>
                <td><?php echo h($empresa["giro"]); ?></td>

                <td>
                     <a href="../productos/pdf.php?empresa_id=<?php echo (int)$empresa["id"]; ?>"
       target="_blank">
        Generar PDF
    </a>
                    <a href="editar.php?id=<?php echo (int)$empresa["id"]; ?>">Editar</a>
                    <a href="../productos/index.php?empresa_id=<?php echo (int)$empresa["id"]; ?>">Productos</a>

                    <form method="POST" action="eliminar.php" style="display:inline"
                          onsubmit="return confirm('¿Eliminar esta empresa y sus productos?');">
                        <input type="hidden" name="id" value="<?php echo (int)$empresa["id"]; ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>

        <?php } ?>

    </table>

<?php } ?>

</body>
</html>