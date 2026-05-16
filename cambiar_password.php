<!-- 
    * Pagina: cambiar_password.php
    * Proposito: Permitir al usuario autenticado cambiar su contraseña.
    * Proceso:
        * 1. Verifica que haya una sesión activa; si no, redirige al login.
        * 2. Recibe la contraseña actual, la nueva y su confirmacion.
        * 3. Valida que todos los campos esten llenos y que la nueva coincida con su confirmacion.
        * 4. Valida la fortaleza de la nueva contraseña usando validarPassword().
        * 5. Consulta el hash de la contraseña actual del usuario en la base de datos.
        * 6. Verifica que la contraseña actual ingresada sea correcta (password_verify).
        * 7. Verifica que la nueva contraseña no sea igual a la actual.
        * 8. Hashea la nueva contraseña y la actualiza en la BD.
        * 9. Muestra mensajes de error o éxito.
-->

<?php
session_start();
require_once 'conexion.php';
require_once 'validaciones.php'; // Incluye función validarPassword()

// 1. Verificar sesión activa
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$exito = "";
$id    = $_SESSION['id']; // id del usuario logueado

// 2. Procesa formulario metodo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual    = trim($_POST['actual']);
    $nueva     = trim($_POST['nueva']);
    $confirmar = trim($_POST['confirmar']);

    // Validaciones
    if (empty($actual) || empty($nueva) || empty($confirmar)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($nueva !== $confirmar) {
        $error = "La nueva contraseña y su confirmación no coinciden.";
    } else {
        // 3. Validar fortaleza de la nueva contraseña usando la función existente
        $validador = validarPassword($nueva);
        if ($validador !== true) {
            $error = $validador; // Mensaje: mínimo 6 caracteres, número y mayúscula
        } else {
            // Obtener hash contraseña actual
            $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario   = $resultado->fetch_assoc();
            $stmt->close();

            // 5. Verificar contraseña actual
            if (!password_verify($actual, $usuario['password'])) {
                $error = "La contraseña actual es incorrecta.";
            
            // 6. Verificar que la nueva contraseña sea diferente a la actual    
            } elseif (password_verify($nueva, $usuario['password'])) {
                $error = "La nueva contraseña debe ser diferente a la actual.";

            } else {
                // 7. Actualizar la contraseña en la bd
                $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
                $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $nuevoHash, $id);

                if ($stmt->execute()) {
                    $exito = "Contraseña actualizada correctamente.";
                } else {
                    $error = "Error al actualizar. Intenta de nuevo.";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <!-- Bootstrap 5 CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- ====== Header ======== -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary py-2 sticky-top">
        <div class="container">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
                <span class="navbar-brand mb-0 h1 fw-bold">Proyecto<span class="text-primary">Web</span></span>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="bg-primary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <span class="fw-bold text-white" style="font-size: 16px;"><?php echo strtoupper(mb_substr($_SESSION['nombre'], 0, 1)); ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </nav>

    <main class="d-flex align-items-center min-vh-100 py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">

                    <!-- Tarjeta principal -->
                    <div class="card bg-dark text-white border-secondary shadow-lg rounded-4">
                        <div class="card-body p-4 p-xl-5">

                            <!-- Cabecera con icono -->
                            <div class="text-center mb-4">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <div class="p-2 rounded-circle bg-primary bg-opacity-25">
                                        <i class="bi bi-key-fill fs-2 text-primary"></i>
                                    </div>
                                    <h1 class="fw-bold text-white fs-2 mb-0">Cambiar<span class="text-primary"> Contraseña</span></h1>
                                </div>
                                <p class="text-white-50">Usuario: <strong class="text-white"><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>
                            </div>

                            <!-- Mensajes de error / éxito -->
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-4 py-2 small" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span><?php echo htmlspecialchars($error); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario (solo si no hay éxito) -->
                            <?php if (empty($exito)): ?>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-white-50"><i class="bi bi-lock me-1 text-white"></i> Contraseña actual</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark bg-opacity-75 border-secondary text-white">
                                                <i class="bi bi-key-fill text-primary text-white"></i>
                                            </span>
                                            <input type="password" class="form-control bg-light" name="actual" placeholder="Ingrese su contraseña actual" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-white-50"><i class="bi bi-shield-lock me-1 text-white"></i> Nueva contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text text-white">
                                                <i class="bi bi-lock text-primary"></i>
                                            </span>
                                            <input type="password" class="form-control bg-light" name="nueva" placeholder="Mín. 6 caracteres, número y mayúscula" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-white-50"><i class="bi bi-check-circle me-1 text-white"></i> Confirmar nueva contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">
                                                <i class="bi bi-shield-check text-success"></i>
                                            </span>
                                            <input type="password" class="form-control bg-light" name="confirmar" placeholder="Repita la nueva contraseña" required>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary rounded-pill py-2">
                                            <i class="bi bi-save me-1"></i> Actualizar contraseña
                                        </button>
                                    </div>
                                </form>

                                <hr class="my-4 bg-secondary">
                                <div class="text-center">
                                    <a href="perfil.php" class="btn btn-outline-primary px-4">
                                        <i class="bi bi-arrow-left me-1"></i> Volver al perfil
                                    </a>
                                </div>

                            <?php else: ?>
                                <!-- Mensaje de éxito cuando la contraseña se actualizo correcatamente -->
                                <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-4 py-2 small" role="alert">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span><?php echo htmlspecialchars($exito); ?></span>
                                </div>
                                <!-- Único enlace después del éxito -->
                                <div class="text-center">
                                    <a href="perfil.php" class="btn btn-outline-primary px-4">
                                        <i class="bi bi-arrow-left me-1"></i> Volver al perfil
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Incluir footer común -->
    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>