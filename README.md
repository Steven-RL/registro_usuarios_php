# Proyecto Web - Sistema de Gestion de Usuarios con PHP y MySQL

## Descripcion general

Este proyecto es una aplicacion web que permite la gestion completa de usuarios:

- **Registro** con validacion de cedula ecuatoriana, nombre (solo letras y espacios), correo electronico unico y contrasena segura (minimo 6 caracteres, un numero y una mayuscula).
- **Inicio de sesion** con verificacion de credenciales mediante `password_verify()`.
- **Perfil de usuario** donde se puede visualizar la cedula, fecha de registro y modificar el nombre y correo (con validaciones).
- **Cambio de contrasena** solicitando la actual, validando la fortaleza de la nueva y verificando que no sea igual a la anterior.
- **Cierre de sesion** con destruccion completa de la sesion y mensaje de exito.
- **Diseno responsivo y moderno** utilizando **Bootstrap 5**, **Bootstrap Icons** y una imagen de fondo personalizada.
- **Seguridad**: contrasenas hasheadas con `password_hash()`, consultas preparadas (prepared statements) para evitar inyeccion SQL, y proteccion contra XSS con `htmlspecialchars()`.

---

## Requisitos del sistema

- **Servidor web** con PHP 7.4 o superior (recomendado 8.x).
- **Servidor de base de datos** MySQL 5.7 o superior.
- **Entorno de desarrollo** recomendado: XAMPP, WAMP, Laragon o cualquier otro que incluya Apache, PHP y MySQL.
- **Navegador** actualizado (Chrome, Firefox, Edge, etc.).
- **Extensiones de PHP** necesarias: `mysqli`, `session` (vienen activadas por defecto en la mayoria de entornos).

---

## Instalacion y configuracion local

Sigue estos pasos para poner el sistema en funcionamiento en tu maquina local.

### 1. Preparar el entorno

- Inicia el servidor Apache y MySQL desde el panel de control de XAMPP (o el que uses).
- Crea una carpeta dentro del directorio raiz del servidor (ej. `htdocs` en XAMPP) y nombrala, por ejemplo, `registro_usuarios`.

### 2. Copiar los archivos

- Coloca todos los archivos del proyecto (`.php`, `.css`, imagenes, etc.) dentro de esa carpeta.

### 3. Crear la base de datos

- Abre `phpMyAdmin` en `http://localhost/phpmyadmin`.
- Crea una nueva base de datos llamada `sistema_usuarios` (puedes cambiar el nombre, pero luego deberas actualizar `conexion.php`).
- Ejecuta la siguiente sentencia SQL para crear la tabla `usuarios`:

```sql
CREATE TABLE `usuarios` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cedula` varchar(10) NOT NULL,
    `nombre` varchar(100) NOT NULL,
    `correo` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Tambien puedes revisar el archivo `database.sql` incluido en el proyecto si deseas importar la estructura directamente.

### 4. Configurar la conexion

- Abre el archivo `conexion.php`.
- Verifica que los parametros coincidan con tu entorno local:

```php
$host       = "localhost";
$usuario    = "root";
$password   = "";
$base_datos = "sistema_usuarios";
```

- Si usas una base de datos con nombre diferente o contrasena, ajustalo aqui.

### 5. Probar el sistema

- En tu navegador, accede a `http://localhost/registro_usuarios/registro.php` (ajusta la ruta segun el nombre de tu carpeta).
- Registrate con una cedula ecuatoriana valida (10 digitos), nombre, correo y una contrasena que cumpla los requisitos (ej. `Segura123!`).
- Luego ingresa a `login.php` con esas credenciales.
- Explora el perfil, actualiza datos o cambia la contrasena.
- Cierra sesion y verifica que aparezca el mensaje de cierre exitoso.
