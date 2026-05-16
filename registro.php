<!--
    * Pagina: registro.php
    * Proposito: permite a nuevos usuarios registrarse en el sistema.
    * Proceso:
        * 1. Valida los datos enviados desde el formulario.
        * 2. Verifica que la cedula, nombre, correo tenga formato correcto.
        * 3. Compruebe que la contraseña sea segura (funcion validarPassword).
        * 4. Evita duplicados (cedula y correo unicos).
        * 5. Inserta el nuevo usuario en la base de datos (contraseña hasheada).
        * 6. Redirige a login.php con mensaje de exito.
-->
<?php
// Incluye archivos necesarios
require_once 'conexion.php'; // Conexion a la base de datos
require_once 'validaciones.php'; // Funciones de validacion personalizadas

// Inicializa variables para mensajes de error o exito
$error = "";
$exito = "";

// Verifica si el formulario ha sido enviado mediante POST, entonces ejecuta el codigo que está dentro de las llaves
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recibir y limpiar los datos
    $cedula   = trim($_POST['cedula']);
    $nombre   = trim($_POST['nombre']);
    $correo   = trim($_POST['correo']);
    $password = trim($_POST['password']);
    $confirmar = trim($_POST['confirmar']);

    // ====== 2. Validaciones de Campos =======
    // Validar que no haya campos vacíos
    if (empty($cedula) || empty($nombre) || empty($correo) || empty($password) || empty($confirmar)) {
        $error = "Todos los campos son obligatorios.";

    // Valida la cedula ecuatoriana (10 dígitos, algoritmo módulo 10)
    } elseif (!validarCedulaEcuatoriana($cedula)) {
        $error = "La cédula ingresada no es válida.";

    // Valida el nombre (solo letras, espacios y acentos)
    } elseif (!validarNombre($nombre)) {
        $error = "El nombre solo puede contener letras y espacios (sin números ni símbolos).";

    // Validar formato de correo de correo electronico
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato del correo no es válido.";

    // Validar que las contraseñas coincidan
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";

        // Validar fortaleza de la contraseña
    } else {
        // ====== 3. Validar fortaleza de la contraseña =========
        // Usa la funcion del archivo validaciones.php (minimo 6 caracteres, un numero y mayuscula)
        $passwordValido = validarPassword($password);
        if ($passwordValido !== true) {
            $error = $passwordValido; // Asigna el mensaje de error especifico de validarPassword()
        } else {

            // ========== 4. Verificar duplicados en la base de datos =========
            // Verificar que la cédula no esté registrada
            // prepare: prepara la consulta SQL para ser ejecutada. 
            // ?: marcador de posicion, indica que mas adelante se sustituira por un valor real de forma segura.
            // $stmt: almacena el objeto de la sentencia preparada.
            $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE cedula = ?");
            // $stmt->bind_param: vincula los marcadores ? con variables reales.
            // s: indica tipo de varible.
            $stmt->bind_param("s", $cedula);
            // $stmt->execute(): ejecuta la consulta ya preparada y con los parametros vinculados. Envia la peticion a la base de datos.
            $stmt->execute();
            // $stmt->store_result(): almacena el resultado de la consulta en el objeto $stmt solo guarda cuantas filas encontro en valor numerico.
            $stmt->store_result();

            // $stmt->num_rows: propiedad que contiene el numero de filas devueltas por consulta.
            if ($stmt->num_rows > 0) {
                $error = "La cédula ya está registrada.";
            } else {
                // cierra la sentencia preparada liberando recursos asociados en el servidor de bd
                $stmt->close();

                // Verificar que el correo no esté registrado
                $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
                $stmt->bind_param("s", $correo);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "El correo ya está registrado.";
                } else {
                    $stmt->close();

                    // 5. ======= Insertar nuevo usuario ======
                    // Hashear o encriptar la contraseña
                    $hash = password_hash($password, PASSWORD_DEFAULT);
    
                    // Prepara la consulta de insercion  para el nuevo usuario
                    $stmt = $conexion->prepare("INSERT INTO usuarios (cedula, nombre, correo, password) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $cedula, $nombre, $correo, $hash);

                    // Ejecuta la insercion
                    if ($stmt->execute()) {
                        // Redirige a la pagina de login con un parametro de exito
                        header("Location: login.php?registro=exitoso");
                        exit(); // Finaliza la ejecucion para evitar que se ejecute mas codigo
                    } else {
                        $error = "Error al registrar. Intenta de nuevo.";
                    }
                }
            }
            $stmt->close(); // Cierra la sentencia
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Usuarios</title>
    <!-- Bootstrap 5 CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>

    <main class="d-flex align-items-center min-vh-100 py-5">
        <div class="container">
            <div class="row g-5 justify-content-center align-items-center"> <!-- align-items-center para centrar verticalmente -->

                <!-- TEXTO DEL Contendor (izq, centrado verticalmente) -->

                <div class="col-12 col-md-6 col-lg-5 d-flex">
                    <div class="hero-text p-5 text-white shadow-lg rounded-4 w-100">
                        <div class="text-end">
                            <div class="d-inline-block bg-primary bg-opacity-25 rounded-pill px-3 py-1 small fw-semibold mb-3">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Acceso al Sistema
                            </div>
                        </div>
                        <h2 class="fw-bold fs-4 lh-sm">
                            Bienvenido al registro de usuario
                        </h2>
                        <p class="mt-4 text-white-60 small text-center">
                            <i class="bi bi-shield-lock-fill me-1"></i> Gestión segura de credenciales, actualización de perfiles y recuperación de contraseña.
                        </p>
                        <div class="mt-2 text-info text-center">
                            <i class="bi bi-filetype-php fs-5 me-3"></i>
                            <i class="bi bi-database fs-5 me-3"></i>
                            <i class="bi bi-bootstrap-fill fs-5 me-3"></i>
                            <i class="bi bi-github fs-5 me-3"></i>
                            <i class="bi bi-code-square fs-5"></i>
                        </div>
                    </div>
                </div>
                <!-- Fin Contenedor -->

                <!-- FORMULARIO de ingreso de datos -->
                <div class="col-12 col-md-6 col-lg-5 smaller-col">
                    <div class="card bg-dark text-white border-secondary shadow-lg">
                        <div class="card-body">
                            <!-- Logo pequeño -->
                            <div class="text-center mb-3">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                    <div class="p-1 rounded-circle bg-primary bg-opacity-10">
                                        <i class="bi bi-rocket-takeoff-fill fs-3 text-primary"></i>
                                    </div>
                                    <h1 class="fw-bold text-white fs-3 mb-0">Registro<span class="text-primary"> Usuarios</span></h1>
                                </div>
                                <p class="text-white small mb-0">Acceso seguro</p>
                            </div>

                            <!-- Mensaje de error PHP -->
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3 rounded-4 py-2 small" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span><?php echo htmlspecialchars($error); ?></span>
                                </div>
                            <?php endif; ?>

                            <!-- Formulario más compacto -->
                            <form method="POST" action="">
                                <div class="row g-2">

                                    <div class="col-12">
                                        <label for="cedula" class="form-label fw-semibold"><i class="bi bi-fingerprint me-1"></i> Cédula</label>
                                        <input type="text" class="form-control bg-light" id="cedula" name="cedula"
                                            placeholder="0912345678" value="<?php echo htmlspecialchars($cedula ?? ''); ?>">
                                    </div>

                                    <div class="col-12">
                                        <label for="nombre" class="form-label fw-semibold"><i class="bi bi-person me-1"></i> Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre"
                                            placeholder="Bryan Samael" value="<?php echo htmlspecialchars($nombre ?? ''); ?>">
                                    </div>

                                    <div class="col-12">
                                        <label for="correo" class="form-label fw-semibold"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                                        <input type="email" class="form-control" id="correo" name="correo"
                                            placeholder="correo@ejemplo.com" value="<?php echo htmlspecialchars($correo ?? ''); ?>">
                                    </div>

                                    <div class="col-12">
                                        <label for="password" class="form-label fw-semibold"><i class="bi bi-lock me-1"></i> Contraseña</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-key text-primary"></i></span>
                                            <input type="password" class="form-control rounded-end-pill" id="password" name="password"
                                                placeholder="Mín. 6 caracteres, número y mayúscula">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="confirmar" class="form-label fw-semibold"><i class="bi bi-shield-check me-1"></i> Confirmar</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-check-circle text-success"></i></span>
                                            <input type="password" class="form-control rounded-end-pill" id="confirmar" name="confirmar"
                                                placeholder="Repite tu contraseña">
                                        </div>
                                    </div>

                                    <!-- Checkbox terminos y condiciones -->
                                    <div class="col-12 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="terminos" required>
                                            <label class="form-check-label text-white small fw-semibold" for="terminos">Acepto <a href="#" class="text-primary text-decoration-none fw-bold">los términos de servicio</a> y <a href="#" class="text-primary text-decoration-none fw-bold">Política de privacidad</a>.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">
                                            Crear cuenta <i class="bi bi-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr class="my-2">
                            <div class="text-center">
                                <p class="text-white small mb-1">O regístrate con</p>
                                <div class="d-flex gap-2 justify-content-center mb-2">
                                    <a href="https://www.google.com/" class="btn btn-outline-secondary rounded-circle p-1"><i class="bi bi-google mb-2"></i></a>
                                    <a href="#" class="btn btn-outline-secondary rounded-circle p-1"><i class="bi bi-github mb-2"></i></a>
                                    <a href="#" class="btn btn-outline-secondary rounded-circle p-1"><i class="bi bi-facebook mb-2"></i></a>
                                </div>
                                <p class="small mb-0 text-white">¿Ya tienes cuenta? &nbsp
                                    <a href="login.php" class="link-primary fw-semibold text-decoration-underline">Inicia sesión</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Incluir footer -->
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>