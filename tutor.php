
<?php
session_start();

require_once __DIR__ . "/includes/conexion.php";
require_once __DIR__ . "/includes/funciones.php";

// Recibir el ID del tutor
$idTutor = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

$tutor = null;

if ($idTutor && $idTutor > 0) {

    // Buscar el tutor verificado y su materia
    $sql = "SELECT
                t.id_tutor AS id,
                t.nombre,
                t.telefono,
                t.descripcion,
                t.disponibilidad,
                t.foto_url,
                t.id_materia,
                m.nombre AS nombre_materia
            FROM tutor t
            LEFT JOIN materia m
                ON t.id_materia = m.id_materia
            WHERE t.id_tutor = ?
              AND t.verificado = 1
            LIMIT 1";

    $consulta = $mysqli->prepare($sql);
    $consulta->bind_param("i", $idTutor);
    $consulta->execute();

    $resultado = $consulta->get_result();
    $tutor = $resultado->fetch_assoc();

    $consulta->close();
}

// Preparar el número para WhatsApp
$whatsapp = "";

if ($tutor) {
    $telefono = preg_replace('/\D+/', '', $tutor["telefono"] ?? "");

    if (strlen($telefono) === 10 && str_starts_with($telefono, "3")) {
        $whatsapp = "57" . $telefono;
    } elseif (strlen($telefono) === 12 && str_starts_with($telefono, "57")) {
        $whatsapp = $telefono;
    }
}

require __DIR__ . "/includes/header.php";
?>


<style>
    * {
        box-sizing: border-box;
    }

    .perfil {
        max-width: 1100px;
        margin: 0 auto;
        padding: 55px 30px 80px;
        color: #3d3150;
        font-family: "Nunito", Arial, sans-serif;
    }

    .perfil-encabezado {
        text-align: center;
        margin-bottom: 40px;
    }

    .perfil-etiqueta {
        color: #6c4a9e;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .perfil-encabezado h2 {
        color: #3d3150;
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        font-size: 42px;
        margin: 15px 0;
    }

    .perfil-encabezado h2 span {
        color: #6c4a9e;
    }

    .perfil-encabezado p {
        max-width: 600px;
        margin: 0 auto;
        color: #706580;
        font-size: 17px;
        line-height: 1.7;
    }

    .tarjeta-perfil {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 35px;
        align-items: center;
        padding: 40px;
        background: white;
        border: 1.5px solid #e4d9f2;
        border-radius: 28px;
        box-shadow: 0 8px 25px rgba(108, 74, 158, 0.09);
    }

    .perfil-lateral {
        text-align: center;
        background: #e4d9f2;
        padding: 35px 20px;
        border-radius: 22px;
    }

    .avatar {
        width: 115px;
        height: 115px;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #c8f0c5;
        font-size: 50px;
    }

    .perfil-lateral h3 {
        color: #6c4a9e;
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        font-size: 23px;
        margin-bottom: 12px;
        overflow-wrap: anywhere;
    }

    .verificado {
        display: inline-block;
        padding: 8px 13px;
        border-radius: 30px;
        background: #c8f0c5;
        color: #315b38;
        font-size: 12px;
        font-weight: 800;
    }

    .perfil-contenido h3 {
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        color: #3d3150;
        font-size: 27px;
        margin-bottom: 12px;
    }

    .perfil-descripcion {
        color: #706580;
        line-height: 1.7;
        margin-bottom: 25px;
    }

    .informacion {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .dato {
        background: #f5f0fa;
        border: 1px solid #e4d9f2;
        border-radius: 15px;
        padding: 18px;
        min-width: 0;
    }

    .dato strong {
        display: block;
        color: #6c4a9e;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .dato span {
        display: block;
        color: #3d3150;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .perfil-mensaje {
        background: #d9f2d5;
        border-radius: 14px;
        padding: 17px;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .botones {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .boton {
        display: inline-block;
        padding: 13px 19px;
        border-radius: 10px;
        text-decoration: none;
        text-align: center;
        font-family: inherit;
        font-size: 14px;
        font-weight: 800;
        transition: background 0.2s;
    }

    .boton-whatsapp {
        background: #c8f0c5;
        color: #315b38;
    }

    .boton-whatsapp:hover {
        background: #b9e7b5;
    }

    .boton-solicitud {
        background: #6c4a9e;
        color: white;
    }

    .boton-solicitud:hover {
        background: #573a82;
    }

    .boton-volver {
        background: #f5f0fa;
        color: #6c4a9e;
        border: 1px solid #e4d9f2;
    }

    .boton-volver:hover {
        background: #e4d9f2;
    }

    .mensaje {
        max-width: 650px;
        margin: 0 auto;
        padding: 40px 25px;
        text-align: center;
        background: white;
        border: 1.5px solid #e4d9f2;
        border-radius: 25px;
        box-shadow: 0 8px 25px rgba(108, 74, 158, 0.08);
    }

    .mensaje h3 {
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        color: #6c4a9e;
        font-size: 27px;
        margin-bottom: 15px;
    }

    .mensaje p {
        color: #706580;
        line-height: 1.7;
        margin-bottom: 25px;
    }

    @media (max-width: 750px) {
        .perfil {
            padding: 35px 18px 55px;
        }

        .perfil-encabezado h2 {
            font-size: 32px;
        }

        .tarjeta-perfil {
            grid-template-columns: 1fr;
            padding: 22px;
            gap: 25px;
        }

        .perfil-lateral {
            padding: 25px 15px;
        }

        .informacion {
            grid-template-columns: 1fr;
        }

        .botones {
            flex-direction: column;
        }

        .boton {
            width: 100%;
        }
    }
</style>

<section class="perfil">

    <div class="perfil-encabezado">
        <h2>💜 Conoce a tu tutor</h2>

        <p>
            Encuentra apoyo para tus estudios y resuelve tus dudas
            con ayuda de un tutor.
        </p>
    </div>

    <?php if ($tutor): ?>

        <div class="tarjeta-perfil">

            <div class="avatar">👩‍🏫</div>

            <h3 class="nombre-tutor">
                <?= htmlspecialchars($tutor["nombre"]) ?>
            </h3>

            <span class="verificado">
                ✓ Tutor verificado
            </span>

            <div class="informacion">

                <div class="dato">
                    <strong>📚 Materia</strong>

                    <span>
                        <?= htmlspecialchars(
                            $tutor["nombre_materia"] ?? "Materia no disponible"
                        ) ?>
                    </span>
                </div>

                <div class="dato">
                    <strong>📱 Contacto</strong>

                    <span>
                        <?= htmlspecialchars($tutor["telefono"] ?? "") ?>
                    </span>
                </div>

            </div>

            <p>
                ¿Necesitas ayuda con esta materia?
                Puedes contactar al tutor o enviar una solicitud de tutoría.
            </p>

            <div class="botones">

                <?php if ($whatsapp !== ""): ?>
                    <a
                        class="boton boton-whatsapp"
                        href="https://wa.me/<?= htmlspecialchars($whatsapp) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Contactar por WhatsApp
                    </a>
                <?php endif; ?>

                <a
                    class="boton boton-solicitud"
                    href="solicitud.php?id_tutor=<?= (int) $tutor["id"] ?>"
                >
                    Solicitar tutoría
                </a>

                <a
                    class="boton boton-volver"
                    href="index.php#tutores"
                >
                    Volver a tutores
                </a>

            </div>

        </div>

    <?php else: ?>

        <div class="mensaje">

            <h3>😕 Tutor no disponible</h3>

            <p>
                No encontramos un tutor verificado con ese identificador.
                Puede que no exista o que todavía no haya sido verificado.
            </p>

            <a
                href="index.php#tutores"
                class="boton boton-solicitud"
            >
                Ver tutores disponibles
            </a>

        </div>

    <?php endif; ?>

</section>

<?php require __DIR__ . "/includes/footer.php"; ?>