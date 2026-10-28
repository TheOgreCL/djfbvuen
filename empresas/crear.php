<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";

$mensaje = "";

$razon_social = "";
$rut = "";
$giro = "";

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

            $sql = "INSERT INTO empresas
                    (usuario_id, razon_social, rut, giro)
                    VALUES (:usuario_id, :razon_social, :rut, :giro)";

            $stmt = $conexion->prepare($sql);

            $stmt->execute(array("usuario_id" => $_SESSION["usuario_id"], "razon_social" => $razon_social, "rut" => $rut, "giro" => $giro));

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == "23000") {
                $mensaje = "Este RUT ya está registrado.";
            } else {
                error_log($e->getMessage());
                $mensaje = "No fue posible registrar la empresa.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar empresa</title>
</head>
<body>

    <h1>Registrar empresa</h1>
    <p><?php echo h($mensaje); ?></p>

    <form method="POST">
        <label>Razón social:</label><br>
        <input type="text" name="razon_social" maxlength="200"value="<?php echo h($razon_social); ?>" required>
        <br><br>
        <label>RUT (con o sin puntos):</label><br>
        <input type="text" name="rut" maxlength="20"placeholder="12345678-5"value="<?php echo h($rut); ?>" required>
        <br><br>
        <label>Giro:</label><br>
        <input type="text" name="giro" maxlength="200"value="<?php echo h($giro); ?>" required>
        <br><br>
        <button type="submit">Guardar empresa</button>
    </form>

    <p><a href="index.php">Volver</a></p>
</body>
</html>