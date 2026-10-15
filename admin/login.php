<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($correo === "" || $password === "") {

        $error = "Completa todos los campos.";

    } else {

        $sql = "SELECT * FROM administrador WHERE correo = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $admin = $resultado->fetch_assoc();

        if ($admin && password_verify($password, $admin["password"])) {

            session_regenerate_id(true);

            $_SESSION["admin"] = $admin;

            header("Location: index.php");
            exit;

        } else {

            $error = "Correo o contraseña incorrectos.";
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

    <title>Panel de KARMA - Ingresar</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0fc, #eee7f8);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .login {
            background: white;
            width: 100%;
            max-width: 390px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(65, 42, 95, 0.12);
            text-align: center;
            box-sizing: border-box;
        }

        .login h1 {
            color: #6c4a9e;
            font-family: cursive;
            font-size: 40px;
            margin: 0 0 8px;
        }

        .login h2 {
            color: #352747;
            font-size: 23px;
            margin-bottom: 10px;
        }

        .login p {
            color: #777080;
            font-size: 14px;
            line-height: 1.5;
        }

        .campo {
            text-align: left;
            margin: 20px 0;
        }

        .campo label {
            display: block;
            color: #45364e;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
        }

        .campo input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ded5eb;
            border-radius: 7px;
            box-sizing: border-box;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            background: #fdfcff;
        }

        .campo input:focus {
            border-color: #6c4a9e;
            box-shadow: 0 0 0 2px rgba(108, 74, 158, 0.10);
        }

        .boton {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 7px;
            background: #6c4a9e;
            color: white;
            font-family: inherit;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .boton:hover {
            background: #573a82;
        }

        .error {
            background: #fff0f0;
            color: #b42318 !important;
            padding: 10px;
            border-radius: 7px;
            font-size: 14px;
        }

        .volver {
            display: inline-block;
            margin-top: 22px;
            color: #6c4a9e;
            text-decoration: none;
            font-size: 14px;
        }

        .volver:hover {
            color: #573a82;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <section class="login">

        <h1>✦ KARMA</h1>

        <p>Panel de administración</p>

        <h2>¡Bienvenido!</h2>

        <p>Ingresa tus datos para continuar.</p>

        <?php if ($error !== ""): ?>
            <p class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="campo">
                <label for="correo">Correo electrónico</label>

                <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="admin@karma.com"
                    value="<?= htmlspecialchars($_POST["correo"] ?? "", ENT_QUOTES, "UTF-8") ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    required
                >
            </div>

            <button type="submit" class="boton">
                Iniciar sesión
            </button>

        </form>

        <a href="../index.php" class="volver">
            ← Volver al sitio web
        </a>

    </section>

</body>
</html>