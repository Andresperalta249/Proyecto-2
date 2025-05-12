<?php
// Mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

function safe($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Model.php';
require_once 'models/User.php';

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $hash_input = $_POST['hash'] ?? '';
    echo "<h2>Prueba de Hash de Contraseña</h2>";
    echo "<b>Contraseña ingresada:</b> <code>" . safe($password) . "</code><br>";
    
    // Generar hash
    $hash_generado = password_hash($password, PASSWORD_DEFAULT);
    echo "<b>Hash generado por PHP:</b> <input type='text' value='" . safe($hash_generado) . "' style='width:500px' readonly><br>";
    echo "<small>Algoritmo usado: <b>" . password_get_info($hash_generado)['algoName'] . "</b></small><br>";
    echo "<small>Versión de PHP: <b>" . phpversion() . "</b></small><br><br>";

    // Si se ingresó un hash para verificar
    if (!empty($hash_input)) {
        echo "<b>Hash a verificar:</b> <input type='text' value='" . safe($hash_input) . "' style='width:500px' readonly><br>";
        $info = password_get_info($hash_input);
        echo "<small>Algoritmo detectado en hash ingresado: <b>" . safe($info['algoName']) . "</b></small><br>";
        if ($info['algo'] === 0) {
            echo "<span style='color:red'><b>Advertencia:</b> El hash ingresado NO es válido o no es un hash soportado por password_verify.</span><br>";
        }
        $verifica = password_verify($password, $hash_input);
        echo "<b>Resultado de verificación:</b> ";
        if ($verifica) {
            echo "<span style='color:green;font-weight:bold'>VÁLIDO ✓</span> (La contraseña coincide con el hash ingresado)";
        } else {
            echo "<span style='color:red;font-weight:bold'>NO VÁLIDO ✗</span> (La contraseña NO coincide con el hash ingresado)";
        }
        echo "<br><br>";
    } else {
        echo "<i>Si quieres verificar un hash, pégalo en el campo correspondiente abajo.</i><br><br>";
    }

    // Mostrar tabla de usuarios y comparación
    $userModel = new User();
    $usuarios = $userModel->query("SELECT id, nombre, email, password FROM usuarios ORDER BY id");
    echo "<h3>Comparación de la contraseña ingresada con los hashes de la base de datos:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Nombre</th><th>Email</th><th>Hash en BD</th><th>¿Coincide?</th>";
    echo "</tr>";
    foreach ($usuarios as $usuario) {
        $hash_bd = $usuario['password'];
        $resultado = password_verify($password, $hash_bd);
        echo "<tr>";
        echo "<td>" . $usuario['id'] . "</td>";
        echo "<td>" . safe($usuario['nombre']) . "</td>";
        echo "<td>" . safe($usuario['email']) . "</td>";
        echo "<td style='word-break: break-all; max-width: 300px;'>" . safe($hash_bd) . "</td>";
        echo "<td style='color: " . ($resultado ? 'green' : 'red') . "; font-weight: bold;'>" . ($resultado ? '✓ SÍ' : '✗ NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo '<hr><a href="hash_test.php">Volver</a>';
    exit;
}
?>
<h2>Test detallado de password_hash y password_verify</h2>
<form method="post">
    <label>Contraseña a probar:<br><input type="text" name="password" required style="width:300px"></label><br><br>
    <label>Hash a verificar (opcional):<br><input type="text" name="hash" style="width:500px"></label><br><br>
    <button type="submit">Generar y/o Verificar</button>
</form>
<hr>
<ul>
    <li>Este test genera un hash seguro con <b>password_hash</b> y verifica con <b>password_verify</b>.</li>
    <li>Puedes pegar cualquier hash generado por PHP para comprobar si la contraseña coincide.</li>
    <li>El hash generado cambia cada vez, aunque la contraseña sea la misma (por el salt aleatorio).</li>
    <li>Si el hash ingresado no es válido, se mostrará una advertencia.</li>
    <li>Abajo puedes ver si la contraseña que ingresaste coincide con algún usuario de la base de datos.</li>
</ul> 