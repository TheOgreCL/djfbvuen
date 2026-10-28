<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$empresa_id = filter_input(INPUT_POST, "empresa_id", FILTER_VALIDATE_INT);

if (!$id || $id < 1 || !$empresa_id || $empresa_id < 1) {
    exit("ID no válido.");
}

$sql = "DELETE productos
        FROM productos
        INNER JOIN empresas ON productos.empresa_id = empresas.id
        WHERE productos.id = :id
          AND empresas.id = :empresa_id
          AND empresas.usuario_id = :usuario_id";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("id" => $id, "empresa_id" => $empresa_id, "usuario_id" => $_SESSION["usuario_id"]));

if ($stmt->rowCount() === 0){http_response_code(404);
    exit("Producto no encontrado o sin permiso para eliminarlo.");
}

header("Location: index.php?empresa_id=" . $empresa_id);
exit;