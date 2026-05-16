<!-- 
    * Pagina: conexion.php
    * Proposito: Establecer la conexion con la base de datos MySQL usando MySQLi.
    * Proceso:
        * 1. Definir los parametros de conexion (host, usuario, contraseña, base de datos).
        * 2. Crear un objeto mysqli con esos parametros.
        * 3. Verificar si hubo error de conexion; si es asi, detener el script y mostrar error.
        * 4. Establecer el juego de caracteres a UTF-8 para manejar correctamente acentos y eñes.
-->
<?php
    // Parametros de conexion
    $host = "localhost"; // Servidor de bd 
    $usuario = "root"; // Usuario de MySQL por defecto root
    $password = ""; // Contraseña de MySQL
    $base_datos = "sistema_usuarios"; // Nombre de mi bd

    // Crear conexion usando MySQLi
    $conexion = new mysqli($host, $usuario, $password, $base_datos);

    // Verificar si hubo error en la conexion
    if ($conexion->connect_error) {
        // Detener la ejecucion y mostrar el error
        die("Error de conexión: " . $conexion->connect_error);
    }

    // Establece conjunto de caracteres a UTF-8 para evitar problemas con tildes, ñ, etc.
    $conexion->set_charset("utf8");
?>