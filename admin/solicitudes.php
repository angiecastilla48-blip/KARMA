<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

requiereAdmin();

$solicitudes = [];
$mensaje = "";

try {
    $sql = "SELECT
                s.id_solicitud,
                s.nombre_estudiante,
                s.telefono_estudiante,
                s.descripcion_duda,
                s.fecha,
                s.medio,
                t.nombre AS nombre_tutor,
                m.nombre AS nombre_materia
            FROM solicitud s
            LEFT JOIN tutor t ON s.id_tutor = t.id_tutor
            LEFT JOIN materia m ON t.id_materia = m.id_materia
            ORDER BY s.fecha DESC, s.id_solicitud DESC";

    $consulta = $mysqli->query($sql);
    $solicitudes = $consulta->fetch_all(MYSQLI_ASSOC);

} catch (mysqli_sql_exception $e) {
    $mensaje = "No se pudieron cargar las solicitudes. Verifica la estructura de la base de datos.";
}

require __DIR__ . "/../includes/header_admin.php";
?>

<style>
    .bandeja {
        max-width: 1200px;
        margin: auto;
        padding: 30px 20px;
        font-family: "Nunito", Arial, sans-serif;
        color: #3d3150;
    }

    .bandeja h2 {
        color: #6c4a9e;
        margin-bottom: 8px;
    }

    .descripcion {
        color: #777;
        margin-bottom: 25px;
    }

    .resumen {
        background: linear-gradient(135deg, #f3eef9, #e0f5df);
        padding: 22px;
        border-radius: 15px;
        margin-bottom: 25px;
    }

    .resumen p {
        margin-bottom: 8px;
        color: #5d4779;
    }

    .resumen strong {
        font-size: 30px;
        color: #6c4a9e;
    }

    .tabla-contenedor {
        background: white;
        padding: 22px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(108, 74, 158, 0.10);
    }

    .tabla-contenedor h3 {
        color: #6c4a9e;
        margin-bottom: 20px;
    }

    .tabla-scroll {
        overflow-x: auto;
    }

    .tabla-solicitudes {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .tabla-solicitudes th {
        background: #f3eef9;
        color: #6c4a9e;
        padding: 14px;
        text-align: left;
        font-size: 14px;
    }

    .tabla-solicitudes td {
        padding: 14px;
        border-bottom: 1px solid #eee8f5;
        font-size: 14px;
    }

    .tabla-solicitudes tbody tr:hover {
        background: #faf8fd;
    }

    .medio {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }

    .formulario {
        background: #eee5f8;
        color: #5d3d88;
    }

    .whatsapp {
        background: #dcf5dd;
        color: #286b36;
    }

    .medio-desconocido {
        background: #f3f4f6;
        color: #4b5563;
    }

    .sin-datos {
        text-align: center;
        padding: 30px !important;
        color: #777;
    }

    .mensaje-error {
        background: #fee2e2;
        color: #991b1b;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .detalle {
        max-width: 250px;
        overflow-wrap: anywhere;
    }

    @media (max-width: 600px) {
        .bandeja {
            padding: 18px 12px;
        }

        .tabla-contenedor {
            padding: 15px;
        }
    }
</style>

<section class="bandeja">

    <h2>📝 Bandeja de solicitudes</h2>

    <p class="descripcion">
        Consulta las solicitudes recibidas, los datos de los estudiantes
        y los tutores asignados.
    </p>

    <?php if ($mensaje !== ""): ?>

        <div class="mensaje-error">
            <?= htmlspecialchars($mensaje) ?>
        </div>

    <?php else: ?>

        <div class="resumen">
            <p>Solicitudes registradas</p>
            <strong><?= count($solicitudes) ?></strong>
        </div>

        <div class="tabla-contenedor">

            <h3>Solicitudes recibidas</h3>

            <div class="tabla-scroll">

                <table class="tabla-solicitudes">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estudiante</th>
                            <th>Teléfono</th>
                            <th>Tutor</th>
                            <th>Materia</th>
                            <th>Fecha</th>
                            <th>Medio</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (count($solicitudes) > 0): ?>

                            <?php foreach ($solicitudes as $solicitud): ?>

                                <?php
                                $medio = strtolower(
                                    trim($solicitud["medio"] ?? "")
                                );

                                if ($medio === "formulario") {
                                    $claseMedio = "formulario";
                                    $textoMedio = "Formulario";
                                } elseif ($medio === "whatsapp") {
                                    $claseMedio = "whatsapp";
                                    $textoMedio = "WhatsApp";
                                } else {
                                    $claseMedio = "medio-desconocido";
                                    $textoMedio = $medio !== ""
                                        ? ucfirst($medio)
                                        : "No especificado";
                                }
                                ?>

                                <tr>
                                    <td>
                                        <?= (int) $solicitud["id_solicitud"] ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $solicitud["nombre_estudiante"] ?? "Sin nombre"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $solicitud["telefono_estudiante"] ?? "Sin teléfono"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $solicitud["nombre_tutor"] ?? "No asignado"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $solicitud["nombre_materia"] ?? "No asignada"
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $solicitud["fecha"] ?? "Sin fecha"
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="medio <?= $claseMedio ?>">
                                            <?= htmlspecialchars($textoMedio) ?>
                                        </span>
                                    </td>

                                    <td class="detalle">
                                        <?= htmlspecialchars(
                                            $solicitud["descripcion_duda"] ?? "Sin descripción"
                                        ) ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="sin-datos">
                                    📭 Todavía no hay solicitudes registradas.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php endif; ?>

</section>

<?php require __DIR__ . "/../includes/footer.php"; ?>