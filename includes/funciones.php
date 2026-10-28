<?php

//Su propósito es convertir caracteres especiales en entidades HTML 
//Para que el navegador los muestre como texto, en lugar de interpretarlos como código HTML o JavaScript
function h($texto) {
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        "UTF-8"
    );
}

//Esta función elimina puntos y guiones sobrantes del formato original y 
//Convierte la letra k en mayúscula
function normalizarRut($rut) {
    $rut = strtoupper(trim($rut));
    $rut = str_replace(".", "", $rut);
    $rut = str_replace("-", "", $rut);
    $rut = str_replace(" ", "", $rut);

    if (strlen($rut) < 2) {
        return "";
    }

    $cuerpo = substr($rut, 0, -1);
    $dv = substr($rut, -1);

    if (!ctype_digit($cuerpo)) {
        return "";
    }

    if (!ctype_digit($dv) && $dv !== "K") {
        return "";
    }

    return $cuerpo . "-" . $dv;
}


//Modulo 11 para calcular el digito verificador y pues, verificar que el rut sea un rut valido
function rutValido($rut) {
    $rut = normalizarRut($rut);

    if ($rut === "") {
        return false;
    }

    $partes = explode("-", $rut);

    if (count($partes) !== 2) {
        return false;
    }

    $cuerpo = $partes[0];
    $dvIngresado = $partes[1];

    if ((int)$cuerpo <= 0 || strlen($cuerpo) > 8) {
        return false;
    }

    $suma = 0;
    $multiplicador = 2;

    for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
        $suma += (int)$cuerpo[$i] * $multiplicador;
        $multiplicador++;
        if ($multiplicador > 7) {
            $multiplicador = 2;
        }
    }

    $resto = 11 - ($suma % 11);

    if ($resto === 11) {
        $dvCalculado = "0";
    } elseif ($resto === 10) {
        $dvCalculado = "K";
    } else {
        $dvCalculado = (string)$resto;
    }

    return $dvIngresado === $dvCalculado;
}