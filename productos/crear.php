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


$mensaje = "";
$nombre = "";
$precio = "";
$descripcion = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");

    if ($nombre === "" || $precio === "" || $descripcion === "") {
        $mensaje = "Todos los campos son obligatorios.";
    } elseif (!preg_match('/^[0-9]+$/', $precio) || (float)$precio <= 0) {
        $mensaje = "El precio debe ser un número entero mayor que cero.";
    } elseif (strlen($precio) > 15) {
        $mensaje = "El precio es demasiado grande.";
    } elseif (
        !isset($_FILES["imagen"]) ||
        $_FILES["imagen"]["error"] !== UPLOAD_ERR_OK
    ) {
        $mensaje = "Debes seleccionar una imagen válida.";
    } else {
        $archivo = $_FILES["imagen"];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipo = $finfo->file($archivo["tmp_name"]);

        $tiposPermitidos = array(
            "image/jpeg" => "jpg",
            "image/png" => "png",
        );

        if (!isset($tiposPermitidos[$tipo])) {
            $mensaje = "Solo se permiten imágenes JPG, PNG o WebP.";
        } else {
            $nombreImagen = bin2hex(random_bytes(16)) . "." . $tiposPermitidos[$tipo];
            $destino = __DIR__ . "/../img/" . $nombreImagen;

            if (move_uploaded_file($archivo["tmp_name"], $destino)) {
                try {
                    $sql = "INSERT INTO productos
                            (empresa_id, nombre, precio, descripcion, imagen)
                            VALUES
                            (:empresa_id, :nombre, :precio, :descripcion, :imagen)";

                    $stmt = $conexion->prepare($sql);
                    $stmt->execute(array(
                        "empresa_id" => $empresa_id,
                        "nombre" => $nombre,
                        "precio" => $precio,
                        "descripcion" => $descripcion,
                        "imagen" => $nombreImagen
                    ));

                    header("Location: index.php?empresa_id=" . (int)$empresa_id);
                    exit;
                } catch (PDOException $e) {
                    if (file_exists($destino)) {
                        unlink($destino);
                    }

                    error_log($e->getMessage());
                    $mensaje = "No se pudo registrar el producto.";
                }
            } else {
                $mensaje = "No se pudo guardar la imagen.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear producto</title>
</head>
<body>
    <h1>Registrar producto</h1>
    <p>Empresa: <?php echo h($empresa["razon_social"]); ?></p>
    <p><?php echo h($mensaje); ?></p>
    
    <form method="POST" enctype="multipart/form-data">
        <label>Nombre del producto:</label><br>
        <input type="text" name="nombre" maxlength="200" value="<?php echo h($nombre); ?>" required>
        <br><br>
        <label>Precio en pesos chilenos, sin decimales:</label><br>
        <input type="number" name="precio" min="1" step="1" value="<?php echo h($precio); ?>" required>
        <br><br>
        <label>Descripción del producto:</label><br>
        <textarea name="descripcion" rows="4" cols="40" maxlength="2000" required
        ><?php echo h($descripcion ?? ""); ?></textarea>
        <br><br>
        <label>Imagen del producto:</label><br>
        <input type="file" name="imagen" accept="image/jpeg,image/png" required>
        <br><br>
        <button type="submit">Guardar producto</button>
    </form>

    <p>
        <a href="index.php?empresa_id=<?php echo (int)$empresa_id; ?>"> Volver a productos</a>
    </p>

</body>
</html>