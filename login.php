<?php
//Las tablas fueron creadas con conexiones por asi decirlo, 
//para modificar y solo visualizar cosas asociadas a ese rut

session_start();

require_once __DIR__ . "/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    $sql = "SELECT id, nombre, correo, contrasena
            FROM usuarios
            WHERE correo = :correo
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute(array("correo" => $correo));
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($contrasena, $usuario["contrasena"])) {

        session_regenerate_id(true);

        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nombre"] = $usuario["nombre"];

        header("Location:index.php");
        exit;

    } else {
        $mensaje = "Correo o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
</head>
<body>
    <h1>Iniciar sesión</h1>
    <p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
    <form method="POST">
        <label>Correo:</label><br>
        <input type="email" name="correo" required>
        <br><br>
        <label>Contraseña:</label><br>
        <input type="password" name="contrasena" required>
        <br><br>
        <button type="submit">Entrar</button>
    </form>

<p><a href="registro.php">Crear una cuenta</a></p>

</body>
</html>