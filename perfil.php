<!-- 
    * Pagina: perfil.php
    * Proposito: Mostrar y permitir la actualizacion de los datos del usuario autenticado.
    * Proceso:
        * 1. Verifica que el usuario tenga una sesión activa; si no, redirige a login.php.
        * 2. Obtiene los datos actuales del usuario desde la base de datos.
        * 3. Si se envía el formulario (POST), valida y actualiza el nombre y correo.
        * 4. Muestra los datos y el formulario de edición.
-->

<?php
session_start();
require_once 'conexion.php';
require_once 'validaciones.php';

// 1. Verificar sesión activa sino de vuelta al login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$error  = "";
$exito  = "";
$id     = $_SESSION['id']; // ID del usuario logueado

// 2. Consultar datos actuales del usuario
$stmt = $conexion->prepare("SELECT cedula, nombre, correo, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario   = $resultado->fetch_assoc(); // Obtine los datos como array asociativo. 
$stmt->close();

// 3. Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);

    // Validaciones
    if (empty($nombre) || empty($correo)) {
        $error = "Todos los campos son obligatorios.";

    } elseif (!validarNombre($nombre)) {
        $error = "El nombre solo puede contener letras, espacios y acentos.";

    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo no es válido.";

    } else {
        // Verificar correo no usado por otro usuario
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
        $stmt->bind_param("si", $correo, $id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Ese correo ya está en uso por otro usuario.";
        } else {
            $stmt->close();
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nombre, $correo, $id);

            if ($stmt->execute()) {
                $_SESSION['nombre'] = $nombre;
                $_SESSION['correo'] = $correo;
                $usuario['nombre'] = $nombre;
                $usuario['correo'] = $correo;
                $exito = "Perfil actualizado correctamente.";
            } else {
                $error = "Error al actualizar. Intenta de nuevo.";
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
    <title>Mi Perfil</title>
    <!-- Bootstrap 5 CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- ========= 1: Header superior ======== -->
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary py-2 sticky-top">
        <div class="container">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
                <span class="navbar-brand mb-0 h1 fw-bold">Proyecto<span class="text-primary">Web</span></span>
            </div>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="bg-primary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <span class="fw-bold text-white" style="font-size: 16px;"><?php echo strtoupper(mb_substr($usuario['nombre'], 0, 1)); ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </nav>

    <main class="d-flex align-items-start min-vh-100 py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">

                    <!-- Tarjeta principal -->
                    <div class="card bg-dark text-white border-secondary shadow-lg rounded-4 mb-4">
                        <div class="card-body p-4 p-xl-5">
                            <div class="d-flex justify-content-center">      
                                <div class="d-flex flex-column flex-sm-row align-items-center gap-5">
                                    <!-- Avatar  -->
                                    <div class="flex-shrink-0">
                                        <div class="p-3 rounded-circle bg-primary bg-opacity-25 d-inline-block">
                                            <i class="bi bi-person-circle fs-1 text-primary"></i>
                                        </div>
                                    </div>
                                    <!-- Texto -->
                                    <div class="text-center text-sm-start">
                                        <h1 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($usuario['nombre']); ?></h1>
                                        <p class="text-white-85 mb-3"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                                        <span class="badge bg-success bg-opacity-25 text-success px-3 py-1">
                                            <i class="bi bi-check-circle-fill me-1 fs-6"></i> <span class="fs-7">Cuenta verificada</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensajes de error o éxito -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 rounded-4 py-2 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($exito)): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-4 py-2 small" role="alert">
                            <i class="bi bi-check-circle-fill"></i>
                            <span><?php echo htmlspecialchars($exito); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Seccion informacion y formulario actualizar -->
                    <div class="row g-4">
                        <div class="col-12 col-md-5">
                            <div class="bg-dark rounded-4 p-3 border border-secondary h-100">
                                <h3 class="h6 fw-semibold text-white-50 mb-3"><i class="bi bi-info-circle me-1"></i> Información</h3>

                                <!-- Cédula - fila completa -->
                                <div class="mb-3">
                                    <div class="bg-dark bg-opacity-75 rounded-3 p-3 border border-secondary">
                                        <div class="d-flex align-items-center gap-2 text-white-50 small mb-1">
                                            <i class="bi bi-fingerprint text-primary"></i> <span>Cédula</span>
                                        </div>
                                        <p class="fw-semibold fs-5 mb-0 text-white"><?php echo htmlspecialchars($usuario['cedula']); ?></p>
                                    </div>
                                </div>

                                <!-- Miembro desde -->
                                <div class="mb-4">
                                    <div class="bg-dark bg-opacity-75 rounded-3 p-3 border border-secondary">
                                        <div class="d-flex align-items-center gap-2 text-white-50 small mb-1">
                                            <i class="bi bi-calendar-check text-primary"></i> <span>Miembro desde</span>
                                        </div>
                                        <p class="fw-semibold fs-5 mb-0 text-white"><?php echo date('d M Y', strtotime($usuario['fecha_registro'])); ?></p>
                                    </div>
                                </div>

                                <a href="cambiar_password.php" class="btn btn-outline-info w-100 rounded-pill">
                                    <i class="bi bi-key me-1"></i> Cambiar contraseña
                                </a>
                            </div>
                        </div>

                        <!-- Seccion derecha: formulario de actualización -->
                        <div class="col-12 col-md-7">
                            <div class="bg-dark rounded-4 p-3 border border-secondary">
                                <h3 class="h6 fw-semibold text-white-50 mb-3"><i class="bi bi-pencil-square me-1"></i> Actualizar datos</h3>
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label text-white fw-semibold">Nombre completo</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark bg-opacity-75 border-secondary text-white">
                                                <i class="bi bi-person text-primary"></i>
                                            </span>
                                            <input type="text" class="form-control bg-light" name="nombre"
                                                value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label text-white fw-semibold">Correo electrónico</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark bg-opacity-75 border-secondary text-white">
                                                <i class="bi bi-envelope text-primary"></i>
                                            </span>
                                            <input type="email" class="form-control bg-light" name="correo"
                                                value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            <i class="bi bi-save me-1"></i> Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
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