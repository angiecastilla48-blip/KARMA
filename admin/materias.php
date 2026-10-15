<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

requiereAdmin();

$mensaje = "";
$tipoMensaje = "";
$materiaEditar = null;

// Agregar, editar o eliminar materias
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    try {
        if ($accion === "agregar") {
            $nombre = trim($_POST["nombre"] ?? "");

            if ($nombre === "") {
                throw new Exception("Escribe el nombre de la materia.");
            }

            $sql = "INSERT INTO materia (nombre) VALUES (?)";
            $consulta = $mysqli->prepare($sql);
            $consulta->bind_param("s", $nombre);
            $consulta->execute();
            $consulta->close();

            $mensaje = "Materia agregada correctamente.";
            $tipoMensaje = "exito";
        }

        elseif ($accion === "editar") {
            $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
            $nombre = trim($_POST["nombre"] ?? "");

            if (!$id || $id <= 0 || $nombre === "") {
                throw new Exception("Verifica los datos de la materia.");
            }

            $sql = "UPDATE materia SET nombre = ? WHERE id_materia = ?";
            $consulta = $mysqli->prepare($sql);
            $consulta->bind_param("si", $nombre, $id);
            $consulta->execute();
            $consulta->close();

            $mensaje = "Materia actualizada correctamente.";
            $tipoMensaje = "exito";
        }

        elseif ($accion === "eliminar") {
            $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

            if (!$id || $id <= 0) {
                throw new Exception("El identificador no es válido.");
            }

            $sql = "DELETE FROM materia WHERE id_materia = ?";
            $consulta = $mysqli->prepare($sql);
            $consulta->bind_param("i", $id);
            $consulta->execute();
            $consulta->close();

            $mensaje = "Materia eliminada correctamente.";
            $tipoMensaje = "exito";
        }

        else {
            throw new Exception("La acción solicitada no es válida.");
        }

    } catch (mysqli_sql_exception $e) {
        if ($accion === "eliminar") {
            $mensaje = "No se puede eliminar esta materia porque tiene tutores asignados.";
        } elseif ((int) $e->getCode() === 1062) {
            $mensaje = "Ya existe una materia con ese nombre.";
        } else {
            $mensaje = "No se pudo realizar la operación. Revisa los datos.";
        }

        $tipoMensaje = "error";

    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = "error";
    }
}

// Buscar materia para editar
if (isset($_GET["editar"])) {
    $idEditar = filter_input(INPUT_GET, "editar", FILTER_VALIDATE_INT);

    if ($idEditar && $idEditar > 0) {
        $consulta = $mysqli->prepare(
            "SELECT id_materia, nombre
             FROM materia
             WHERE id_materia = ?"
        );

        $consulta->bind_param("i", $idEditar);
        $consulta->execute();
        $resultado = $consulta->get_result();
        $materiaEditar = $resultado->fetch_assoc();
        $consulta->close();

        if (!$materiaEditar) {
            $mensaje = "La materia seleccionada no existe.";
            $tipoMensaje = "error";
        }
    }
}

// Consultar las materias registradas
$resultado = $mysqli->query(
    "SELECT id_materia, nombre
     FROM materia
     ORDER BY id_materia DESC"
);

$materias = $resultado->fetch_all(MYSQLI_ASSOC);

require __DIR__ . "/../includes/header_admin.php";
?>

<style>
    .gestion {
        max-width: 1100px;
        margin: auto;
        padding: 30px 20px;
        color: #3d3150;
        font-family: "Nunito", Arial, sans-serif;
    }

    .gestion h2 {
        color: #6c4a9e;
        margin-bottom: 8px;
    }

    .subtitulo {
        color: #777;
        margin-bottom: 25px;
    }

    .formulario,
    .tabla-contenedor {
        background: white;
        padding: 25px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(108, 74, 158, 0.10);
        margin-bottom: 25px;
    }

    .formulario h3,
    .tabla-contenedor h3 {
        color: #6c4a9e;
        margin-bottom: 20px;
    }

    .campo {
        margin-bottom: 18px;
    }

    .campo label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
    }

    .campo input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd2ed;
        border-radius: 9px;
        font-size: 15px;
        box-sizing: border-box;
    }

    .campo input:focus {
        outline: none;
        border-color: #9b7ac5;
    }

    .boton {
        display: inline-block;
        border: none;
        padding: 10px 16px;
        border-radius: 9px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        text-align: center;
        font-weight: bold;
    }

    .boton-guardar {
        background: #6c4a9e;
        color: white;
    }

    .boton-guardar:hover {
        background: #56377f;
    }

    .boton-cancelar {
        background: #eee8f5;
        color: #4b3864;
    }

    .boton-editar {
        background: #e8e0f5;
        color: #5d3d88;
    }

    .boton-eliminar {
        background: #fde7e7;
        color: #a62c42;
    }

    .mensaje {
        padding: 14px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .exito {
        background: #e0f5df;
        color: #286b36;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
    }

    .tabla-scroll {
        overflow-x: auto;
    }

    .gestion table {
        width: 100%;
        border-collapse: collapse;
        min-width: 400px;
    }

    .gestion th,
    .gestion td {
        padding: 14px 12px;
        border-bottom: 1px solid #eee8f5;
        text-align: left;
    }

    .gestion th {
        background: #f3eef9;
        color: #6c4a9e;
    }

    .gestion td {
        font-size: 14px;
    }

    .acciones {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .acciones form {
        margin: 0;
    }

    .vacio {
        text-align: center;
        padding: 25px;
        color: #777;
    }

    @media (max-width: 600px) {
        .gestion {
            padding: 18px 12px;
        }

        .formulario,
        .tabla-contenedor {
            padding: 18px;
        }
    }
</style>

<section class="gestion">

    <h2>📚 Gestión de materias</h2>

    <p class="subtitulo">
        Administra las materias disponibles en la plataforma KARMA.
    </p>

    <?php if ($mensaje !== ""): ?>
        <div class="mensaje <?= htmlspecialchars($tipoMensaje) ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="formulario">

        <h3>
            <?= $materiaEditar ? "Editar materia" : "Agregar nueva materia" ?>
        </h3>

        <form method="POST">

            <input
                type="hidden"
                name="accion"
                value="<?= $materiaEditar ? 'editar' : 'agregar' ?>"
            >

            <?php if ($materiaEditar): ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $materiaEditar["id_materia"] ?>"
                >
            <?php endif; ?>

            <div class="campo">
                <label for="nombre">Nombre de la materia</label>

                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    maxlength="150"
                    placeholder="Ejemplo: Matemáticas"
                    value="<?= htmlspecialchars($materiaEditar["nombre"] ?? "") ?>"
                    required
                >
            </div>

            <button type="submit" class="boton boton-guardar">
                <?= $materiaEditar ? "Guardar cambios" : "Agregar materia" ?>
            </button>

            <?php if ($materiaEditar): ?>
                <a href="materias.php" class="boton boton-cancelar">
                    Cancelar
                </a>
            <?php endif; ?>

        </form>
    </div>

    <div class="tabla-contenedor">

        <h3>Materias registradas</h3>

        <div class="tabla-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($materias) > 0): ?>

                        <?php foreach ($materias as $materia): ?>
                            <tr>
                                <td>
                                    <?= (int) $materia["id_materia"] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($materia["nombre"]) ?>
                                </td>

                                <td>
                                    <div class="acciones">

                                        <a
                                            class="boton boton-editar"
                                            href="materias.php?editar=<?= (int) $materia["id_materia"] ?>"
                                        >
                                            Editar
                                        </a>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('¿Estás segura de eliminar esta materia?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="accion"
                                                value="eliminar"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $materia["id_materia"] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="boton boton-eliminar"
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
                            <td colspan="3" class="vacio">
                                Todavía no hay materias registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</section>

<?php require __DIR__ . "/../includes/footer.php"; ?>