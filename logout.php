<?php
session_start();
// Destruir la sesión en el servidor
session_destroy();
// Redirigir al login
header("Location: login.php?logout=exitoso");
exit();
?>