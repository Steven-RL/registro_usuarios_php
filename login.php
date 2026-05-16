<!-- 
    * Pagina: login.php
    * Proposito: Autenticar usuarios y crear sesion.
    * Proceso:
        * 1. Verifica si ya hay una sesion activa; de ser asi redirige a perfil.php.
        * 2. Si se envia el formulario (POST):
        *    - Valida que los campos no estén vacios.
        *    - Valida el formato del correo.
        *    - Busca el usuario por correo en la base de datos.
        *    - Verifica la contraseña usando password_verify().
        *    - Si es correcto, crea variables de sesión y redirige a perfil.php.
        * 3. Si llegan parametros GET (logout, registro), muestra mensajes de éxito/información.
        * 4. Muestra el formulario de login o el mensaje de cierre de sesion.
-->
<?php

session_start(); // Inicia o reanuda la sesion actual.
require_once 'conexion.php'; 

// ======= 1. Redireccion si ya esta autenticado =========
// Si ya existe una sesion activa(usuario logeado), lo redirigimos al perfil
if (isset($_SESSION['id'])) {
    header("Location: perfil.php");
    exit();
}

$error = ""; 

// ====== 2. Procesa el formulario de login ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe y limpia datos
    $correo   = trim($_POST['correo']);
    $password = trim($_POST['password']);
    
    // Validaciones que no tenga campos vacios
    if (empty($correo) || empty($password)) {
        $error = "Todos los campos son obligatorios.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo no es válido.";

    } else {
        // Buscar usuario por su correo
        $stmt = $conexion->prepare("SELECT id, nombre, correo, password FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result(); // Obtiene el conjunto de resultados

        if ($resultado->num_rows === 0) {
            $error = "Credenciales incorrectas.";
        } else {
            // $resultado->fetch_assoc(): metodo de MySQL toma el resultado de una consulta y devuelve la fila como array.
            $usuario = $resultado->fetch_assoc();
            if (!password_verify($password, $usuario['password'])) {
                $error = "Credenciales incorrectas.";
            } else {
                // Guardamos la identida en la sesion (como un carnet de identidad temporal)
                $_SESSION['id'] = $usuario['id']; // Almacena el identificador unico del usuario en la sesion
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['correo'] = $usuario['correo'];
                header("Location: perfil.php");
                exit();
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <main class="d-flex align-items-center min-vh-100 py-5">
        <div class="container">
            <div class="row g-5 justify-content-center align-items-center">

                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'exitoso'): ?>
                <!-- Bloque de cierre de sesion -->
                <!-- Aparece cuando el usuario acaba de cerrar sesion (parametro logout=exitos) -->
                <div class="col-12 d-flex justify-content-center">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="card bg-dark text-white border-success shadow-lg rounded-4 text-center p-4">
                            <i class="bi bi-check-circle-fill fs-2 text-success mb-2"></i>
                            <h4 class="fw-bold mb-1">Sesión cerrada correctamente</h4>
                            <p class="text-white-50 small mb-3">Serás redirigido en 3 segundos...</p>
                            <a href="login.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Iniciar sesión ahora</a>
                        </div>
                    </div>
                </div>
                <script>
                    // Mostrar mensaje de sesion cerrada correctamente y redirigir automáticamente después de 3 segundos
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 3000);
                    // Limpiar la URL para que no quede el parámetro 'logout'
                    if (window.history.replaceState) {
                        const url = new URL(window.location.href);
                        url.searchParams.delete('logout');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                </script>

                <?php else: ?>
                    <!-- Modo normal: mostrar todo el contenido (texto + formulario) -->
                    <!-- Tarjeta: texto del proyecto -->
                    <div class="col-12 col-md-6 col-lg-5 d-flex">
                        <div class="hero-text p-5 text-white shadow-lg rounded-4 w-100">
                            <div class="text-center">
                                <h2 class="fw-bold fs-4 lh-sm">
                                    Proyecto de Desarrollo Web:<br>
                                    Sistema de Perfil de Usuario y Cambio de Contraseña con PHP y MySQL
                                </h2>
                            </div>
                            <div class="mt-4 text-info text-center">
                                <i class="bi bi-shield-lock-fill me-3 fs-5"></i>
                                <i class="bi bi-person-badge me-3 fs-5"></i>
                                <i class="bi bi-key-fill fs-5"></i>
                            </div>
                            <p class="mt-2 text-white">
                                Ingresa con tu correo y contraseña para acceder a tu perfil, gestionar tus datos y cambiar tu contraseña de forma segura.
                            </p>
                        </div>
                    </div>

                    <!-- Tarjeta derecha: formulario de login -->
                    <div class="col-12 col-md-6 col-lg-5 d-flex">
                        <div class="card bg-dark text-white border-secondary shadow-lg rounded-4 w-100">
                            <div class="card-body p-4 p-xl-5">
                            <!-- Cabecera del formulario -->
                                <div class="text-center mb-4">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                        <div class="p-2 rounded-circle bg-primary bg-opacity-25">
                                            <i class="bi bi-key-fill fs-2 text-primary"></i>
                                        </div>
                                        <h1 class="fw-bold text-white fs-2 mb-0">Iniciar<span class="text-primary"> Sesión</span></h1>
                                    </div>
                                    <p class="text-white-50 small mb-0">Accede a tu cuenta</p>
                                </div>
                                <!-- Mostrar mensaje de error si existe -->
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-4 py-2 small" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span><?php echo htmlspecialchars($error); ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Mostrar mensaje de exito al venir del registro -->
                                <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
                                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-4 py-2 small" role="alert">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>Registro exitoso. ¡Ya puedes iniciar sesión!</span>
                                    </div>

                                    <script>
                                        // Eliminar el parámetro 'registro' de la URL sin recargar
                                        if (window.history.replaceState) {
                                            const url = new URL(window.location.href);
                                            url.searchParams.delete('registro');
                                            window.history.replaceState({}, document.title, url.toString());
                                        }
                                    </script>
                                <?php endif; ?>
                                <!-- Formulario de login -->
                                <form method="POST" action="">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                                            <input type="email" class="form-control bg-light" name="correo"
                                                placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($correo ?? ''); ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold"><i class="bi bi-lock me-1"></i> Contraseña</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-key text-primary"></i></span>
                                                <input type="password" class="form-control bg-light" name="password" placeholder="Tu contraseña" required>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-3">
                                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold">
                                                Ingresar <i class="bi bi-arrow-right ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <hr class="my-4 bg-secondary">
                                <div class="text-center">
                                    <p class="small mb-0 text-white-50">
                                        ¿No tienes cuenta?&nbsp <a href="registro.php" class="link-primary fw-semibold text-decoration-underline">Regístrate aquí</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <!-- Incluir footer común -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>