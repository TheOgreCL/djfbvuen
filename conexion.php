<?php

$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "panel_empresas_db";

try {
    $conexion = new PDO("mysql:host=$host;dbname=$base_datos;charset=utf8mb4", $usuario, $contrasena);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $conexion;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        exit("Error al conectar con la base de datos.");
        }
