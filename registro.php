<?php

session_start();

require_once __DIR__ . "/conexion.php";

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $contrasena = $_POST["contrasena"];

    if ($nombre === "" || $correo === "" || $contrasena === "") {
        $mensaje = "Todos los campos son obligatorios.";
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El correo no es válido.";
            } elseif (strlen($contrasena) < 8) {
                $mensaje = "La contraseña debe tener al menos 8 caracteres.";
                } else {
                    try {
                        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO usuarios (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)";
                        $stmt = $conexion->prepare($sql);
                        $stmt->execute(array("nombre" => $nombre, "correo" => $correo, "contrasena" => $hash
                        ));

                        header("Location: login.php");
                        exit;
                        } catch (PDOException $e) {
                            if ($e->getCode() == "23000") {
                                $mensaje = "Ese correo ya está registrado.";
                                } else {
                                    error_log($e->getMessage());
                                    $mensaje = "No fue posible registrar el usuario.";
                                }
                            }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
</head>
<body>
    <h1>Crear cuenta</h1>
    <p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></p>
    <form method="POST">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required maxlength="100">
        <br><br>
        <label>Correo:</label><br>
        <input type="email" name="correo" required maxlength="255">
        <br><br>
        <label>Contraseña (mínimo 8 caracteres):</label><br>
        <input type="password" name="contrasena" required minlength="8">
        <br><br>
        <button type="submit">Registrarme</button>
    </form>
    <p><a href="login.php">Ya tengo una cuenta</a></p>

</body>
</html>