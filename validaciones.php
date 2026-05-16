<?php

function validarCedulaEcuatoriana($cedula) {
    // 1. Verificar que tenga exactamente 10 dígitos numéricos
    if (!preg_match('/^\d{10}$/', $cedula)) {
        return false;
    }
    
    // 2. Verificar que los primeros 2 dígitos sean provincia válida (01-24)
    $provincia = (int)substr($cedula, 0, 2);
    if ($provincia < 1 || $provincia > 24) {
        return false;
    }
    
    // 3. Verificar que el tercer dígito sea menor a 6
    $tercerDigito = (int)substr($cedula, 2, 1);
    if ($tercerDigito >= 6) {
        return false;
    }
    
    // 4. Aplicar algoritmo Módulo 10
    $digitos = str_split($cedula);
    $suma = 0;
    
    for ($i = 0; $i < 9; $i++) {
        $digito = (int)$digitos[$i];
        
        if ($i % 2 == 0) {
            $multiplicado = $digito * 2;
            if ($multiplicado > 9) {
                $multiplicado -= 9;
            }
            $suma += $multiplicado;
        } else {
            $suma += $digito;
        }
    }
    
    $residuo = $suma % 10;
    $digitoVerificador = $residuo == 0 ? 0 : 10 - $residuo;
    
    return $digitoVerificador == (int)$digitos[9];
}

function validarNombre($nombre) {
    // Permite letras mayúsculas/minúsculas con acentos, ñ y espacios
    return preg_match("/^[a-zA-ZáéíóúñÁÉÍÓÚÑ\s]+$/", $nombre);
}

function validarPassword($password) {
    // 1. Longitud mínima 6 caracteres
    if (strlen($password) < 6) {
        return "La contraseña debe tener al menos 6 caracteres.";
    }
    // 2. Al menos un número
    if (!preg_match('/[0-9]/', $password)) {
        return "La contraseña debe contener al menos un número.";
    }
    // 3. Al menos una letra mayúscula
    if (!preg_match('/[A-Z]/', $password)) {
        return "La contraseña debe contener al menos una letra mayúscula.";
    }
    // Si pasa todas las validaciones
    return true;
}

?>