<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("ID de empresa no válido.");
}

$sql = "SELECT id, razon_social, rut, giro
        FROM empresas
        WHERE id = :id AND usuario_id = :usuario_id
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("id" => $id, "usuario_id" => $_SESSION["usuario_id"]));

$empresa = $stmt->fetch();

if (!$empresa) {
    http_response_code(404);
    exit("Empresa no encontrada.");
}

$mensaje = "";

$razon_social = $empresa["razon_social"];
$rut = $empresa["rut"];
$giro = $empresa["giro"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $razon_social = trim($_POST["razon_social"]);
    $rut = trim($_POST["rut"]);
    $giro = trim($_POST["giro"]);

    if ($razon_social === "" || $rut === "" || $giro === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif (!rutValido($rut)) {
        $mensaje = "El RUT no es válido.";
    } else {
        $rut = normalizarRut($rut);
        try {

            $sql = "UPDATE empresas
                    SET razon_social = :razon_social,
                        rut = :rut,
                        giro = :giro
                    WHERE id = :id AND usuario_id = :usuario_id";

            $stmt = $conexion->prepare($sql);
            $stmt->execute(array("razon_social" => $razon_social, "rut" => $rut, "giro" => $giro, "id" => $id, "usuario_id" => $_SESSION["usuario_id"]
            ));

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == "23000") {
                $mensaje = "Ese RUT ya está registrado.";
            } else {
                error_log($e->getMessage());
                $mensaje = "No fue posible actualizar la empresa.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar empresa</title>
</head>
<body>
    <h1>Editar empresa</h1>
    <p><?php echo h($mensaje); ?></p>
    <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas guardar los cambios de esta empresa?');">
        <label>Razón social:</label><br>
        <input type="text" name="razon_social" maxlength="200" value="<?php echo h($razon_social); ?>" required>
        <br><br>
        <label>RUT:</label><br>
        <input type="text" name="rut" maxlength="20"value="<?php echo h($rut); ?>" required>
        <br><br>
        <label>Giro:</label><br>
        <input type="text" name="giro" maxlength="200"value="<?php echo h($giro); ?>" required>
        <br><br>
        <button type="submit">Guardar cambios</button>
    </form>

<p><a href="index.php">Volver</a></p>

</body>
</html>