```php
<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

requiereAdmin();

$mensaje = "";
$error = "";

/* Procesar acciones */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $accion = $_POST["accion"] ?? "";

    if (!$id || $id < 1) {
        $error = "El tutor seleccionado no es válido.";

    } elseif ($accion === "verificar" || $accion === "quitar") {

        $estado = ($accion === "verificar") ? 1 : 0;

        $sql = "UPDATE tutores SET verificado = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $estado, $id);

        if ($stmt->execute()) {
            $mensaje = "Estado del tutor actualizado correctamente.";
        } else {
            $error = "No se pudo actualizar el estado del tutor.";
        }

        $stmt->close();

    } elseif ($accion === "eliminar") {

        $sql = "DELETE FROM tutores WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $mensaje = $stmt->affected_rows > 0
                ? "Tutor eliminado correctamente."
                : "No se encontró el tutor.";
        } else {
            $error = "No se pudo eliminar el tutor. Puede tener registros relacionados.";
        }

        $stmt->close();

    } else {
        $error = "Acción no válida.";
    }
}

/* Consultar tutores */
$sql = "SELECT t.id, t.nombre, t.correo, t.telefono,
               t.verificado, m.nombre AS materia
        FROM tutores t
        LEFT JOIN materias m ON t.materia_id = m.id
        ORDER BY t.nombre ASC";

$resultado = $conexion->query($sql);

$tutores = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $tutores[] = $fila;
    }
} else {
    $error = "No se pudieron consultar los tutores. Revisa las tablas de la base de datos.";
}

require __DIR__ . "/../includes/header_admin.php";
?>

<style>
    .gestion-tutores {
        max-width: 1150px;
        margin: 30px auto;
        padding: 20px;
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
        color: #a83291;
        margin-bottom: 8px;
    }

    .encabezado-tutores p {
        color: #777;
        margin-top: 0;
    }

    .boton-tutor {
        display: inline-block;
        border: none;
        border-radius: 8px;
        padding: 10px 13px;
        text-decoration: none;
        font-size: 13px;
        cursor: pointer;
        font-weight: bold;
    }

    .nuevo-tutor {
        background: #a83291;
        color: white;
    }

    .editar-tutor {
        background: #eee4fa;
        color: #6837a1;
    }

    .verificar-tutor {
        background: #dcf5e5;
        color: #216b40;
    }

    .quitar-tutor {
        background: #fff0d6;
        color: #875900;
    }

    .eliminar-tutor {
        background: #ffe5e5;
        color: #a52b38;
    }

    .tabla-contenedor {
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
    }

    .tabla-tutores {
        width: 100%;
        border-collapse: collapse;
        min-width: 750px;
    }

    .tabla-tutores th {
        background: #f6eafa;
        color: #64265f;
        padding: 15px 12px;
        text-align: left;
    }

    .tabla-tutores td {
        padding: 13px 12px;
        border-bottom: 1px solid #eee;
        color: #45364e;
    }

    .tabla-tutores tr:hover td {
        background: #fdf9ff;
    }

    .estado {
        display: inline-block;
        padding: 6px 9px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
    }

    .estado-si {
        background: #dcf5e5;
        color: #216b40;
    }

    .estado-no {
        background: #fff0d6;
        color: #875900;
    }

    .acciones-tutor {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .acciones-tutor form {
        margin: 0;
    }

    .mensaje-tutor {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 18px;
    }

    .mensaje-ok {
        background: #dcf5e5;
        color: #216b40;
    }

    .mensaje-error {
        background: #ffe5e5;
        color: #a52b38;
    }

    .sin-tutores {
        text-align: center;
        padding: 25px !important;
        color: #777 !important;
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
            <h2>Gestión de tutores</h2>
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
                    <th>Correo</th>
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
                                <?= (int) $tutor["id"] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tutor["nombre"] ?? "", ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tutor["correo"] ?? "", ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tutor["telefono"] ?? "", ENT_QUOTES, "UTF-8") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($tutor["materia"] ?? "Sin materia", ENT_QUOTES, "UTF-8") ?>
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
                                        href="tutor_form.php?id=<?= (int) $tutor["id"] ?>"
                                        class="boton-tutor editar-tutor">
                                        Editar
                                    </a>

                                    <form method="POST">
                                        <input type="hidden" name="id"
                                               value="<?= (int) $tutor["id"] ?>">

                                        <?php if ((int) $tutor["verificado"] === 1): ?>
                                            <input type="hidden" name="accion" value="quitar">

                                            <button type="submit" class="boton-tutor quitar-tutor">
                                                Quitar verificación
                                            </button>
                                        <?php else: ?>
                                            <input type="hidden" name="accion" value="verificar">

                                            <button type="submit" class="boton-tutor verificar-tutor">
                                                Verificar
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <form method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este tutor?');">

                                        <input type="hidden" name="id"
                                               value="<?= (int) $tutor["id"] ?>">

                                        <input type="hidden" name="accion" value="eliminar">

                                        <button type="submit" class="boton-tutor eliminar-tutor">
                                            Eliminar
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7" class="sin-tutores">
                            No hay tutores registrados todavía.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>

    <p>
        Total de tutores registrados:
        <strong><?= count($tutores) ?></strong>
    </p>

</section>

<?php require __DIR__ . "/../includes/footer.php"; ?>
```