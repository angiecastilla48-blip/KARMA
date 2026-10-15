<?php
session_start();

require_once __DIR__ . "/includes/conexion.php";
require_once __DIR__ . "/includes/funciones.php";

$mensaje = "";
$errores = [];

$nombre = "";
$telefono = "";
$descripcion = "";
$tutorId = "";

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $tutorId = $_POST["tutor_id"] ?? "";

    // Validar nombre
    if ($nombre === "") {
        $errores[] = "El nombre es obligatorio.";
    }

    // Validar teléfono colombiano
    if (!preg_match('/^3[0-9]{9}$/', $telefono)) {
        $errores[] = "El teléfono debe tener 10 dígitos y comenzar por 3.";
    }

    // Validar descripción
    if ($descripcion === "") {
        $errores[] = "Describe brevemente la duda que necesitas resolver.";
    }

    // Validar tutor seleccionado
    $tutorIdValidado = filter_var($tutorId, FILTER_VALIDATE_INT);

    if (!$tutorIdValidado || $tutorIdValidado < 1) {
        $errores[] = "Selecciona un tutor válido.";
    } else {
        $verificarTutor = $conexion->prepare(
            "SELECT COUNT(*) FROM tutores WHERE id = :id"
        );

        $verificarTutor->execute([
            ":id" => $tutorIdValidado
        ]);

        if ((int) $verificarTutor->fetchColumn() === 0) {
            $errores[] = "El tutor seleccionado no existe.";
        }
    }

    // Guardar solicitud
    if (empty($errores)) {
        try {
            $sql = "INSERT INTO solicitudes
                        (nombre, telefono, descripcion, tutor_id, fecha, medio)
                    VALUES
                        (:nombre, :telefono, :descripcion, :tutor_id, :fecha, :medio)";

            $consulta = $conexion->prepare($sql);

            $consulta->execute([
                ":nombre" => $nombre,
                ":telefono" => $telefono,
                ":descripcion" => $descripcion,
                ":tutor_id" => $tutorIdValidado,
                ":fecha" => date("Y-m-d H:i:s"),
                ":medio" => "formulario"
            ]);

            // Patrón PRG: redirigir después de guardar
            $_SESSION["mensaje_solicitud"] =
                "¡Tu solicitud fue enviada correctamente! Pronto podrán ponerse en contacto contigo.";

            header("Location: " . $_SERVER["PHP_SELF"]);
            exit;

        } catch (PDOException $e) {
            $errores[] =
                "No fue posible enviar la solicitud. Inténtalo nuevamente.";
        }
    }
}

// Mostrar mensaje después de la redirección
if (isset($_SESSION["mensaje_solicitud"])) {
    $mensaje = $_SESSION["mensaje_solicitud"];
    unset($_SESSION["mensaje_solicitud"]);
}

// Consultar tutores disponibles
$consulta = $mysqli->query(
    "SELECT id_tutor, nombre
     FROM tutor
     WHERE verificado = 1
     ORDER BY nombre ASC"
);

$tutores = $consulta->fetch_all(MYSQLI_ASSOC);

require __DIR__ . "/includes/header.php";
?>

<style>
    .solicitud {
        max-width: 760px;
        margin: 0 auto;
        padding: 30px 20px;
        font-family: Arial, sans-serif;
        color: #333;
    }

    .encabezado-solicitud {
        text-align: center;
        margin-bottom: 28px;
    }

    .encabezado-solicitud h2 {
        color: #7c3aed;
        margin-bottom: 10px;
        font-size: 30px;
    }

    .encabezado-solicitud p {
        color: #777;
        line-height: 1.6;
    }

    .tarjeta-solicitud {
        background: white;
        padding: 30px;
        border-radius: 18px;
        box-shadow: 0 5px 22px rgba(0, 0, 0, 0.07);
    }

    .campo {
        margin-bottom: 21px;
    }

    .campo label {
        display: block;
        font-weight: bold;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .campo input,
    .campo select,
    .campo textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 13px;
        border: 1px solid #ddd6fe;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        background: #fff;
    }

    .campo input:focus,
    .campo select:focus,
    .campo textarea:focus {
        outline: none;
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px #f3e8ff;
    }

    .campo textarea {
        resize: vertical;
        min-height: 130px;
    }

    .ayuda {
        display: block;
        margin-top: 7px;
        font-size: 12px;
        color: #777;
    }

    .boton-enviar {
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 10px;
        background: #7c3aed;
        color: white;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
    }

    .boton-enviar:hover {
        background: #6d28d9;
    }

    .boton-enviar:disabled {
        background: #c4b5fd;
        cursor: not-allowed;
    }

    .mensaje-exito {
        padding: 16px;
        background: #dcfce7;
        color: #166534;
        border-radius: 12px;
        margin-bottom: 22px;
        line-height: 1.6;
    }

    .mensaje-error {
        padding: 16px;
        background: #fee2e2;
        color: #991b1b;
        border-radius: 12px;
        margin-bottom: 22px;
        line-height: 1.6;
    }

    .nota {
        text-align: center;
        color: #888;
        font-size: 12px;
        margin-top: 18px;
    }

    @media (max-width: 600px) {
        .solicitud {
            padding: 20px 14px;
        }

        .tarjeta-solicitud {
            padding: 20px;
        }

        .encabezado-solicitud h2 {
            font-size: 25px;
        }
    }
</style>

<section class="solicitud">

    <div class="encabezado-solicitud">
        <h2>💜 Solicita tu tutoría</h2>

        <p>
            Cuéntanos qué necesitas aprender y selecciona el tutor
            que te puede ayudar.
        </p>
    </div>

    <?php if ($mensaje !== ""): ?>
        <div class="mensaje-exito" role="status">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="mensaje-error" role="alert">
            <strong>Revisa los siguientes datos:</strong>

            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="tarjeta-solicitud">

        <form method="POST" action="">

            <div class="campo">
                <label for="nombre">Nombre completo *</label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    maxlength="150"
                    placeholder="Escribe tu nombre"
                    value="<?= htmlspecialchars($nombre) ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="telefono">Teléfono celular *</label>

                <input
                    type="tel"
                    id="telefono"
                    name="telefono"
                    inputmode="numeric"
                    pattern="3[0-9]{9}"
                    minlength="10"
                    maxlength="10"
                    placeholder="Ejemplo: 3001234567"
                    value="<?= htmlspecialchars($telefono) ?>"
                    required
                >

                <small class="ayuda">
                    Ingresa un número de 10 dígitos que comience por 3.
                </small>
            </div>

            <div class="campo">
                <label for="tutor_id">Selecciona tu tutor *</label>

                <select
                    id="tutor_id"
                    name="tutor_id"
                    required
                >
                    <option value="">Elige un tutor</option>

                    <?php foreach ($tutores as $tutor): ?>
                        <option
                            value="<?= (int) $tutor["id_tutor"] ?>"
                            <?= (isset($idTutor) && (int) $idTutor === (int) $tutor["id_tutor"]) ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($tutor["nombre"]) ?>
                        </option>
                    <?php endforeach; ?>

                </select>

                <?php if (empty($tutores)): ?>
                    <small class="ayuda">
                        Todavía no hay tutores registrados.
                    </small>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="descripcion">¿Qué duda necesitas resolver? *</label>

                <textarea
                    id="descripcion"
                    name="descripcion"
                    maxlength="2000"
                    placeholder="Ejemplo: Necesito ayuda con ecuaciones matemáticas..."
                    required
                ><?= htmlspecialchars($descripcion) ?></textarea>
            </div>

            <button
                type="submit"
                class="boton-enviar"
                <?= empty($tutores) ? "disabled" : "" ?>
            >
                Enviar solicitud ✨
            </button>

        </form>

    </div>

    <p class="nota">
        * Los campos marcados son obligatorios.
    </p>

</section>

<?php require __DIR__ . "/includes/footer.php"; ?>