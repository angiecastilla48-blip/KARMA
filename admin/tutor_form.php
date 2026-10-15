<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

requiereAdmin();

$mensaje = "";
$tipoMensaje = "";

$tutor = [
    "nombre" => "",
    "telefono" => "",
    "id_materia" => "",
    "descripcion" => "",
    "disponibilidad" => "",
    "foto_url" => ""
];

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$editando = $id !== null && $id !== false && $id > 0;

// Cargar tutor para editar
if ($editando) {
    $consulta = $mysqli->prepare(
        "SELECT id_tutor, nombre, telefono, id_materia,
                descripcion, disponibilidad, foto_url
         FROM tutor
         WHERE id_tutor = ?"
    );

    $consulta->bind_param("i", $id);
    $consulta->execute();

    $resultado = $consulta->get_result();
    $datosTutor = $resultado->fetch_assoc();
    $consulta->close();

    if (!$datosTutor) {
        $mensaje = "No se encontró el tutor seleccionado.";
        $tipoMensaje = "error";
        $editando = false;
        $id = null;
    } else {
        $tutor = $datosTutor;
    }
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accionId = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

    $editando = $accionId !== null
        && $accionId !== false
        && $accionId > 0;

    $id = $editando ? $accionId : null;

    $tutor["nombre"] = trim($_POST["nombre"] ?? "");
    $tutor["telefono"] = trim($_POST["telefono"] ?? "");
    $tutor["id_materia"] = $_POST["id_materia"] ?? "";
    $tutor["descripcion"] = trim($_POST["descripcion"] ?? "");
    $tutor["disponibilidad"] = trim($_POST["disponibilidad"] ?? "");
    $tutor["foto_url"] = trim($_POST["foto_url"] ?? "");

    $errores = [];

    if ($tutor["nombre"] === "") {
        $errores[] = "El nombre del tutor es obligatorio.";
    }

    if (mb_strlen($tutor["nombre"]) > 150) {
        $errores[] = "El nombre no puede superar los 150 caracteres.";
    }

    if (!preg_match('/^3[0-9]{9}$/', $tutor["telefono"])) {
        $errores[] = "El teléfono debe tener 10 dígitos y comenzar por 3.";
    }

    $materiaId = filter_var(
        $tutor["id_materia"],
        FILTER_VALIDATE_INT
    );

    if (!$materiaId || $materiaId < 1) {
        $errores[] = "Selecciona una materia válida.";
    } else {
        $verificarMateria = $mysqli->prepare(
            "SELECT id_materia FROM materia WHERE id_materia = ?"
        );

        $verificarMateria->bind_param("i", $materiaId);
        $verificarMateria->execute();

        $resultadoMateria = $verificarMateria->get_result();
        $existeMateria = $resultadoMateria->num_rows > 0;
        $verificarMateria->close();

        if (!$existeMateria) {
            $errores[] = "La materia seleccionada no existe.";
        }
    }

    if ($tutor["disponibilidad"] === "") {
        $errores[] = "Escribe la disponibilidad del tutor.";
    }

    if (mb_strlen($tutor["disponibilidad"]) > 150) {
        $errores[] = "La disponibilidad no puede superar los 150 caracteres.";
    }

    if (mb_strlen($tutor["foto_url"]) > 255) {
        $errores[] = "La dirección de la foto es demasiado larga.";
    }

    if ($tutor["foto_url"] !== "" &&
        !filter_var($tutor["foto_url"], FILTER_VALIDATE_URL)) {
        $errores[] = "Escribe una dirección válida para la foto.";
    }

    if ($editando) {
        $verificarTutor = $mysqli->prepare(
            "SELECT id_tutor FROM tutor WHERE id_tutor = ?"
        );

        $verificarTutor->bind_param("i", $id);
        $verificarTutor->execute();

        $resultadoTutor = $verificarTutor->get_result();
        $existeTutor = $resultadoTutor->num_rows > 0;
        $verificarTutor->close();

        if (!$existeTutor) {
            $errores[] = "El tutor que intentas editar ya no existe.";
        }
    }

    if (empty($errores)) {
        try {
            $descripcion = $tutor["descripcion"] !== ""
                ? $tutor["descripcion"]
                : null;

            $fotoUrl = $tutor["foto_url"] !== ""
                ? $tutor["foto_url"]
                : null;

            if ($editando) {
                $sql = "UPDATE tutor
                        SET nombre = ?,
                            telefono = ?,
                            id_materia = ?,
                            descripcion = ?,
                            disponibilidad = ?,
                            foto_url = ?
                        WHERE id_tutor = ?";

                $consulta = $mysqli->prepare($sql);

                $consulta->bind_param(
                    "ssisssi",
                    $tutor["nombre"],
                    $tutor["telefono"],
                    $materiaId,
                    $descripcion,
                    $tutor["disponibilidad"],
                    $fotoUrl,
                    $id
                );

                $consulta->execute();
                $consulta->close();

                $mensaje = "Tutor actualizado correctamente.";

            } else {
                $sql = "INSERT INTO tutor
                        (nombre, telefono, id_materia, descripcion,
                         disponibilidad, foto_url, verificado)
                        VALUES (?, ?, ?, ?, ?, ?, 0)";

                $consulta = $mysqli->prepare($sql);

                $consulta->bind_param(
                    "ssisss",
                    $tutor["nombre"],
                    $tutor["telefono"],
                    $materiaId,
                    $descripcion,
                    $tutor["disponibilidad"],
                    $fotoUrl
                );

                $consulta->execute();
                $consulta->close();

                $mensaje = "Tutor registrado correctamente. Está pendiente de verificación.";
            }

            $tipoMensaje = "exito";

            $tutor = [
                "nombre" => "",
                "telefono" => "",
                "id_materia" => "",
                "descripcion" => "",
                "disponibilidad" => "",
                "foto_url" => ""
            ];

            $editando = false;
            $id = null;

        } catch (mysqli_sql_exception $e) {
            $mensaje = "No fue posible guardar el tutor. Revisa los datos e inténtalo nuevamente.";
            $tipoMensaje = "error";
        }

    } else {
        $mensaje = implode(" ", $errores);
        $tipoMensaje = "error";
    }
}

// Consultar materias para el selector
$resultadoMaterias = $mysqli->query(
    "SELECT id_materia, nombre
     FROM materia
     ORDER BY nombre ASC"
);

$materias = $resultadoMaterias->fetch_all(MYSQLI_ASSOC);

require __DIR__ . "/../includes/header_admin.php";
?>

<style>
    .form-tutor {
        max-width: 750px;
        margin: 0 auto;
        padding: 25px;
        font-family: "Nunito", Arial, sans-serif;
        color: #3d3150;
    }

    .form-tutor h2 {
        color: #6c4a9e;
        margin-bottom: 8px;
    }

    .descripcion {
        color: #777;
        margin-bottom: 22px;
        line-height: 1.5;
    }

    .volver {
        display: inline-block;
        margin-bottom: 22px;
        color: #6c4a9e;
        text-decoration: none;
    }

    .volver:hover {
        text-decoration: underline;
    }

    .tarjeta-formulario {
        background: white;
        padding: 28px;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(108, 74, 158, 0.10);
    }

    .tarjeta-formulario h3 {
        color: #6c4a9e;
        margin-top: 0;
        margin-bottom: 22px;
    }

    .campo {
        margin-bottom: 20px;
    }

    .campo label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        font-size: 14px;
    }

    .campo input,
    .campo select,
    .campo textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 13px;
        border: 1px solid #ddd2ed;
        border-radius: 10px;
        background: #fff;
        font-size: 14px;
        font-family: inherit;
    }

    .campo input:focus,
    .campo select:focus,
    .campo textarea:focus {
        outline: none;
        border-color: #9b7ac5;
        box-shadow: 0 0 0 3px #f3eef9;
    }

    .campo textarea {
        min-height: 100px;
        resize: vertical;
    }

    .ayuda {
        display: block;
        margin-top: 7px;
        color: #777;
        font-size: 12px;
    }

    .mensaje {
        padding: 14px;
        border-radius: 10px;
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .exito {
        background: #e0f5df;
        color: #286b36;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
    }

    .botones {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 25px;
    }

    .boton {
        display: inline-block;
        padding: 12px 18px;
        border: none;
        border-radius: 10px;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
    }

    .boton-guardar {
        background: #6c4a9e;
        color: white;
    }

    .boton-guardar:hover {
        background: #56377f;
    }

    .boton-guardar:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .boton-cancelar {
        background: #eee8f5;
        color: #4b3864;
    }

    .sin-materias {
        padding: 12px;
        border-radius: 10px;
        background: #e0f5df;
        color: #286b36;
        font-size: 14px;
        margin-bottom: 20px;
    }

    @media (max-width: 600px) {
        .form-tutor {
            padding: 14px;
        }

        .tarjeta-formulario {
            padding: 20px;
        }
    }
</style>

<section class="form-tutor">

    <h2>👩‍🏫 Formulario de tutor</h2>

    <p class="descripcion">
        Registra un nuevo tutor o actualiza su información en KARMA.
    </p>

    <a href="tutores.php" class="volver">
        ‹ Volver a gestión de tutores
    </a>

    <?php if ($mensaje !== ""): ?>
        <div class="mensaje <?= htmlspecialchars($tipoMensaje) ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="tarjeta-formulario">

        <h3>
            <?= $editando ? "Editar información del tutor" : "Registrar nuevo tutor" ?>
        </h3>

        <?php if (empty($materias)): ?>
            <div class="sin-materias">
                No hay materias registradas. Primero agrega una materia
                desde Gestión de materias.
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
            <?php endif; ?>

            <div class="campo">
                <label for="nombre">Nombre completo *</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    maxlength="150"
                    placeholder="Ejemplo: Laura Benavides"
                    value="<?= htmlspecialchars($tutor["nombre"] ?? "") ?>"
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
                    maxlength="10"
                    minlength="10"
                    placeholder="Ejemplo: 3001234567"
                    value="<?= htmlspecialchars($tutor["telefono"] ?? "") ?>"
                    required
                >
                <small class="ayuda">
                    Debe contener 10 dígitos y comenzar por 3.
                </small>
            </div>

            <div class="campo">
                <label for="id_materia">Materia que enseña *</label>
                <select id="id_materia" name="id_materia" required>
                    <option value="">Selecciona una materia</option>

                    <?php foreach ($materias as $materia): ?>
                        <option
                            value="<?= (int) $materia["id_materia"] ?>"
                            <?= (string) ($tutor["id_materia"] ?? "") ===
                                (string) $materia["id_materia"] ? "selected" : "" ?>
                        >
                            <?= htmlspecialchars($materia["nombre"]) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción del tutor</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    placeholder="Experiencia, conocimientos y temas que enseña..."
                ><?= htmlspecialchars($tutor["descripcion"] ?? "") ?></textarea>
            </div>

            <div class="campo">
                <label for="disponibilidad">Disponibilidad *</label>
                <input
                    type="text"
                    id="disponibilidad"
                    name="disponibilidad"
                    maxlength="150"
                    placeholder="Ejemplo: Lunes a viernes, de 3 a 6 p. m."
                    value="<?= htmlspecialchars($tutor["disponibilidad"] ?? "") ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="foto_url">Enlace de la foto</label>
                <input
                    type="url"
                    id="foto_url"
                    name="foto_url"
                    maxlength="255"
                    placeholder="https://ejemplo.com/foto.jpg"
                    value="<?= htmlspecialchars($tutor["foto_url"] ?? "") ?>"
                >
                <small class="ayuda">
                    Campo opcional. Puedes dejarlo vacío si no tienes una foto.
                </small>
            </div>

            <div class="botones">
                <button
                    type="submit"
                    class="boton boton-guardar"
                    <?= empty($materias) ? "disabled" : "" ?>
                >
                    <?= $editando ? "Guardar cambios" : "Registrar tutor" ?>
                </button>

                <a href="tutores.php" class="boton boton-cancelar">
                    Cancelar
                </a>
            </div>

        </form>
    </div>

</section>

<?php require __DIR__ . "/../includes/footer.php"; ?>