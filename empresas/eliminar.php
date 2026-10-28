<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("ID de empresa no válido.");
}

$sql = "DELETE FROM empresas
        WHERE id = :id AND usuario_id = :usuario_id";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("id" => $id, "usuario_id" => $_SESSION["usuario_id"]));

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    exit("Empresa no encontrada o sin permiso para eliminarla.");
}

header("Location: index.php");
exit;