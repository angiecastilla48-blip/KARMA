<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

requiereAdmin();

$mensaje = "";
$error = "";
$tutores = [];

// Procesar acciones
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $accion = $_POST["accion"] ?? "";

    if (!$id || $id < 1) {
        $error = "El tutor seleccionado no es válido.";

    } elseif ($accion === "verificar" || $accion === "quitar") {
        $estado = ($accion === "verificar") ? 1 : 0;

        try {
            $sql = "UPDATE tutor
                    SET verificado = ?
                    WHERE id_tutor = ?";

            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ii", $estado, $id);
            $stmt->execute();

            $mensaje = $stmt->affected_rows > 0
                ? "Estado del tutor actualizado correctamente."
                : "El tutor ya tiene ese estado o no existe.";

            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            $error = "No se pudo actualizar el estado del tutor.";
        }

    } elseif ($accion === "eliminar") {
        try {
            $sql = "DELETE FROM tutor WHERE id_tutor = ?";

            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $mensaje = $stmt->affected_rows > 0
                ? "Tutor eliminado correctamente."
                : "No se encontró el tutor.";

            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            $error = "No se pudo eliminar el tutor. Puede tener solicitudes relacionadas.";
        }

    } else {
        $error = "Acción no válida.";
    }
}

// Consultar tutores y sus materias
try {
    $sql = "SELECT
                t.id_tutor,
                t.nombre,
                t.telefono,
                t.verificado,
                m.nombre AS materia
            FROM tutor t
            LEFT JOIN materia m
                ON t.id_materia = m.id_materia
            ORDER BY t.nombre ASC";

    $resultado = $mysqli->query($sql);
    $tutores = $resultado->fetch_all(MYSQLI_ASSOC);

} catch (mysqli_sql_exception $e) {
    $error = "No se pudieron consultar los tutores. Revisa la estructura de la base de datos.";
}

require __DIR__ . "/../includes/header_admin.php";
?>

<style>
    .gestion-tutores {
        max-width: 1150px;
        margin: 30px auto;
        padding: 20px;
        color: #3d3150;
        font-family: "Nunito", Arial, sans-serif;
    }

    .encabezado-tutores {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .encabezado-tutores h2 {
        color: #6c4a9e;
        margin-bottom: 8px;
    }

    .encabezado-tutores p {
        color: #777;
        margin-top: 0;
    }

    .boton-tutor {
        display: inline-block;
        border: none;
        border-radius: 9px;
        padding: 10px 13px;
        text-decoration: none;
        font-size: 13px;
        cursor: pointer;
        font-weight: bold;
        font-family: inherit;
    }

    .nuevo-tutor {
        background: #6c4a9e;
        color: white;
    }

    .nuevo-tutor:hover {
        background: #56377f;
    }

    .editar-tutor {
        background: #eee5f8;
        color: #5d3d88;
    }

    .verificar-tutor {
        background: #dcf5dd;
        color: #286b36;
    }

    .quitar-tutor {
        background: #fff0d6;
        color: #875900;
    }

    .eliminar-tutor {
        background: #fde7e7;
        color: #a52b38;
    }

    .tabla-contenedor {
        overflow-x: auto;
        background: white;
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(108, 74, 158, 0.10);
    }

    .tabla-tutores {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .tabla-tutores th {
        background: #f3eef9;
        color: #6c4a9e;
        padding: 15px 12px;
        text-align: left;
    }

    .tabla-tutores td {
        padding: 13px 12px;
        border-bottom: 1px solid #eee8f5;
        color: #45364e;
    }

    .tabla-tutores tbody tr:hover td {
        background: #faf8fd;
    }

    .estado {
        display: inline-block;
        padding: 6px 9px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }

    .estado-si {
        background: #dcf5dd;
        color: #286b36;
    }

    .estado-no {
        background: #fff0d6;
        color: #875900;
    }

    .acciones-tutor {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        align-items: center;
    }

    .acciones-tutor form {
        margin: 0;
    }

    .mensaje-tutor {
        padding: 13px;
        border-radius: 9px;
        margin-bottom: 18px;
    }

    .mensaje-ok {
        background: #e0f5df;
        color: #286b36;
    }

    .mensaje-error {
        background: #fee2e2;
        color: #991b1b;
    }

    .sin-tutores {
        text-align: center;
        padding: 25px !important;
        color: #777 !important;
    }

    .total-tutores {
        margin-top: 18px;
        color: #5d4779;
    }

    @media (max-width: 600px) {
        .gestion-tutores {
            padding: 12px;
        }
    }
</style>

<section class="seccion gestion-tutores">

    <div class="encabezado-tutores">
        <div>
            <h2>👩‍🏫 Gestión de tutores</h2>
            <p>Administra los tutores registrados en KARMA.</p>
        </div>

        <a href="tutor_form.php" class="boton-tutor nuevo-tutor">
            + Nuevo tutor
        </a>
    </div>

    <?php if ($mensaje !== ""): ?>
        <div class="mensaje-tutor mensaje-ok">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="mensaje-tutor mensaje-error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <div class="tabla-contenedor">

        <table class="tabla-tutores">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Materia</th>
                    <th>Verificación</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($tutores) > 0): ?>

                    <?php foreach ($tutores as $tutor): ?>
                        <tr>
                            <td>
                                <?= (int) $tutor["id_tutor"] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $tutor["nombre"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $tutor["telefono"] ?? "",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $tutor["materia"] ?? "Sin materia",
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </td>

                            <td>
                                <?php if ((int) $tutor["verificado"] === 1): ?>
                                    <span class="estado estado-si">
                                        Verificado
                                    </span>
                                <?php else: ?>
                                    <span class="estado estado-no">
                                        Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="acciones-tutor">

                                    <a
                                        href="tutor_form.php?id=<?= (int) $tutor["id_tutor"] ?>"
                                        class="boton-tutor editar-tutor"
                                    >
                                        Editar
                                    </a>

                                    <form method="POST">
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $tutor["id_tutor"] ?>"
                                        >

                                        <?php if ((int) $tutor["verificado"] === 1): ?>
                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="quitar"
                                            >

                                            <button
                                                type="submit"
                                                class="boton-tutor quitar-tutor"
                                            >
                                                Quitar verificación
                                            </button>
                                        <?php else: ?>
                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="verificar"
                                            >

                                            <button
                                                type="submit"
                                                class="boton-tutor verificar-tutor"
                                            >
                                                Verificar
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este tutor? Se pueden eliminar también sus solicitudes relacionadas.');"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $tutor["id_tutor"] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="accion"
                                            value="eliminar"
                                        >

                                        <button
                                            type="submit"
                                            class="boton-tutor eliminar-tutor"
                                        >
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="6" class="sin-tutores">
                            No hay tutores registrados todavía.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <p class="total-tutores">
        Total de tutores registrados:
        <strong><?= count($tutores) ?></strong>
    </p>

</section>

<?php require __DIR__ . "/../includes/footer.php"; ?>