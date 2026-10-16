<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($correo === "" || $password === "") {

        $error = "Completa todos los campos.";

    } else {

        $sql = "SELECT id_admin, nombre, correo, password_hash
                FROM administrador
                WHERE correo = ?
                LIMIT 1";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $admin = $resultado->fetch_assoc();

        if ($admin && password_verify($password, $admin["password_hash"])) {

            session_regenerate_id(true);

            $_SESSION["admin"] = [
                "id_admin" => $admin["id_admin"],
                "nombre" => $admin["nombre"],
                "correo" => $admin["correo"]
            ];

            $stmt->close();

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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Nunito", Arial, sans-serif;
            background: linear-gradient(135deg, #f5f0fa, #e4d9f2, #d9f2d5);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #3d3150;
        }

        .login {
            background: white;
            width: 100%;
            max-width: 410px;
            padding: 38px;
            border: 1.5px solid #e4d9f2;
            border-radius: 24px;
            box-shadow: 0 12px 35px rgba(108, 74, 158, 0.12);
            text-align: center;
        }

        .login h1 {
            color: #6c4a9e;
            font-family: "Bricolage Grotesque", cursive;
            font-size: 42px;
            margin: 0 0 5px;
        }

        .login .subtitulo {
            color: #6c4a9e;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 28px;
        }

        .login h2 {
            color: #3d3150;
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 27px;
            margin-bottom: 8px;
        }

        .login p {
            color: #706580;
            font-size: 14px;
            line-height: 1.6;
        }

        .campo {
            text-align: left;
            margin: 22px 0;
        }

        .campo label {
            display: block;
            color: #3d3150;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 800;
        }

        .campo input {
            width: 100%;
            padding: 14px;
            border: 1.5px solid #e4d9f2;
            border-radius: 10px;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            background: #fdfcff;
        }

        .campo input:focus {
            border-color: #6c4a9e;
            box-shadow: 0 0 0 3px rgba(108, 74, 158, 0.10);
        }

        .boton {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #6c4a9e;
            color: white;
            font-family: inherit;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .boton:hover {
            background: #573a82;
        }

        .error {
            background: #fff0f0;
            color: #b42318 !important;
            padding: 12px;
            border-radius: 10px;
            font-size: 14px;
        }

        .volver {
            display: inline-block;
            margin-top: 25px;
            color: #6c4a9e;
            text-decoration: none;
            font-size: 14px;
            font-weight: 800;
        }

        .volver:hover {
            color: #573a82;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login {
                padding: 27px 22px;
            }
        }
    </style>
</head>

<body>

    <section class="login">

        <h1>✦ KARMA</h1>

        <p class="subtitulo">Panel de administración</p>

        <h2>¡Bienvenido!</h2>

        <p>Ingresa tus datos para administrar la plataforma.</p>

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
                    autocomplete="username"
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
                    autocomplete="current-password"
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