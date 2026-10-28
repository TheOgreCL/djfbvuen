
<?php
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/funciones.php";
require_once __DIR__ . "/../conexion.php";
require_once __DIR__ . "/../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

//Obtencion y validacion de la empresa
$empresa_id = filter_input(INPUT_GET, "empresa_id", FILTER_VALIDATE_INT);

if (!$empresa_id || $empresa_id <= 0) {
    exit("ID de empresa no válido.");
}

//Buscar la empresa del usuario que esta conectado
$sql = "SELECT razon_social, rut
        FROM empresas
        WHERE id = :id AND usuario_id = :usuario_id";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("id" => $empresa_id, "usuario_id" => $_SESSION["usuario_id"]));
$empresa = $stmt->fetch();

if (!$empresa) {
    exit("Empresa no encontrada o no tienes permiso.");
}

//Obtencion de los datos de la empresa
$sql = "SELECT nombre, precio, descripcion, imagen
        FROM productos
        WHERE empresa_id = :empresa_id
        ORDER BY nombre ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute(array("empresa_id" => $empresa_id));
$productos = $stmt->fetchAll();

//La pagina, algo sencillon
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #333333;
    }

    .encabezado {
        text-align: center;
        border-bottom: 2px solid #444444;
        padding-bottom: 12px;
        margin-bottom: 20px;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 8px;
    }

    .datos-empresa {
        font-size: 12px;
        line-height: 1.6;
    }

    .catalogo {
        width: 100%;
        border-collapse: separate;
        border-spacing: 10px;
    }

    .producto {
        width: 50%;
        border: 1px solid #cccccc;
        padding: 10px;
        vertical-align: top;
    }

    .producto img {
        width: 100%;
        height: 150px;
    }

    .nombre {
        font-size: 14px;
        font-weight: bold;
        margin-top: 8px;
        margin-bottom: 8px;
    }

    .descripcion {
        font-size: 10px;
        line-height: 1.5;
        color: #555555;
    }

    .precio {
        font-size: 15px;
        font-weight: bold;
        margin-top: 10px;
    }

    .sin-imagen {
        height: 100px;
        text-align: center;
        padding-top: 40px;
        background-color: #eeeeee;
        color: #666666;
    }

    .vacio {
        text-align: center;
        padding: 20px;
    }

    .pie {
        text-align: center;
        font-size: 9px;
        margin-top: 20px;
        color: #777777;
    }
</style>
</head>

<body>

<div class="encabezado">
    <h1>CATÁLOGO DE PRODUCTOS</h1>
    <div class="datos-empresa">
        <strong>Empresa:</strong> ' . h($empresa["razon_social"]) . '<br>
        <strong>RUT:</strong> ' . h($empresa["rut"]) . '
    </div>
</div>
';

// Mostrar mensaje si no existen productos
if (count($productos) === 0) {
    $html .= '<p class="vacio">Esta empresa no tiene productos registrados.</p>';
} else {
    $html .= '<table class="catalogo">';

    // Mostrar dos productos por fila
    foreach (array_chunk($productos, 2) as $fila) {
        $html .= '<tr>';
        foreach ($fila as $producto) {
            $html .= '<td class="producto">';





            //Arreglar para visualizar png
            // Obtener la imagen desde la carpeta img
     $nombreArchivo = ($producto["imagen"] ?? "");
    $urlImagen = "http://localhost/panel_empresas/img/" .
                ($nombreArchivo);
    if ($nombreArchivo !== "") {
        $html .= '<img src="' . htmlspecialchars($urlImagen, ENT_QUOTES, "UTF-8") . '" alt="Imagen del producto">';
    } else {
        $html .= '<div class="sin-imagen">Sin imagen</div>';
    }

            //////////////////////////////////////////////////////////////

            $html .= '
                <div class="nombre">' . h($producto["nombre"]) . '</div>
                <div class="descripcion">' .
                    nl2br(h($producto["descripcion"] ?? "")) .
                '</div>
                <div class="precio">$' .
                    number_format((int)$producto["precio"], 0, ",", ".") .
                '</div>
            </td>';
        }

        // Completar la segunda columna si queda un producto impar
        if (count($fila) === 1) {
            $html .= '<td class="producto"></td>';
        }

        $html .= '</tr>';
    }

    $html .= '</table>';
}

$html .= '
<div class="pie">
    Catálogo de productos de ' . h($empresa["razon_social"]) . '
</div>

</body>
</html>';

// Configurar y generar el PDF
$options = new Options();
$options->set("isRemoteEnabled", true);
$options->setChroot(realpath(__DIR__ . "/.."));

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, "UTF-8");
$dompdf->setPaper("A4", "portrait");
$dompdf->render();

// Mostrar el PDF en el navegador
$dompdf->stream("catalogo_productos.pdf", array(
    "Attachment" => false
));

exit;

?>