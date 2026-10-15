
<?php
session_start();

require_once __DIR__ . "/../includes/conexion.php";
require_once __DIR__ . "/../includes/funciones.php";

// Verificar que el usuario haya iniciado sesión como administrador
requiereAdmin();

// Contar los registros de una tabla permitida
function contarRegistros(mysqli $mysqli, string $tabla): int
{
    $tablasPermitidas = [
        "materia",
        "tutor",
        "solicitud"
    ];

    if (!in_array($tabla, $tablasPermitidas, true)) {
        return 0;
    }

    $consulta = $mysqli->query(
        "SELECT COUNT(*) AS total FROM `$tabla`"
    );

    $resultado = $consulta->fetch_assoc();

    return (int) $resultado["total"];
}

// Totales del panel
$totalMaterias = contarRegistros($mysqli, "materia");
$totalTutores = contarRegistros($mysqli, "tutor");
$totalSolicitudes = contarRegistros($mysqli, "solicitud");

// Contar tutores verificados
$consulta = $mysqli->query(
    "SELECT COUNT(*) AS total
     FROM tutor
     WHERE verificado = 1"
);

$resultado = $consulta->fetch_assoc();
$tutoresVerificados = (int) $resultado["total"];

require __DIR__ . "/../includes/header_admin.php";
?>

<style>
    .panel {
        max-width: 1150px;
        margin: 0 auto;
        padding: 40px 25px;
        font-family: "Nunito", Arial, sans-serif;
        color: #3d3150;
    }

    .panel h2 {
        color: #6c4a9e;
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        font-size: 34px;
        margin-bottom: 8px;
    }

    .descripcion {
        color: #706580;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .tarjetas {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .tarjeta {
        background: white;
        padding: 25px;
        border-radius: 18px;
        border: 1.5px solid #e4d9f2;
        box-shadow: 0 4px 14px rgba(108, 74, 158, 0.08);
    }

    .tarjeta:nth-child(1) {
        background: #c8f0c5;
        border-color: #c8f0c5;
    }

    .tarjeta:nth-child(2) {
        background: #e4d9f2;
        border-color: #e4d9f2;
    }

    .tarjeta:nth-child(3) {
        background: #dcdcf7;
        border-color: #dcdcf7;
    }

    .tarjeta:nth-child(4) {
        background: white;
    }

    .tarjeta h3 {
        color: #3d3150;
        font-size: 16px;
        margin: 0 0 18px;
    }

    .numero {
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        font-size: 36px;
        font-weight: 800;
        color: #6c4a9e;
        margin: 0;
    }

    .bienvenida {
        background: #3d3150;
        padding: 30px;
        border-radius: 20px;
        color: white;
    }

    .bienvenida h3 {
        color: white;
        font-family: "Bricolage Grotesque", Arial, sans-serif;
        font-size: 24px;
        margin-bottom: 12px;
    }

    .bienvenida p {
        color: #efe8f8;
        line-height: 1.7;
        margin: 0;
    }

    @media (max-width: 600px) {
        .panel {
            padding: 25px 15px;
        }

        .panel h2 {
            font-size: 28px;
        }

        .bienvenida {
            padding: 22px;
        }
    }
</style>

<section class="panel">

    <h2>Panel principal ✦</h2>

    <p class="descripcion">
        Consulta el resumen general de tu plataforma de tutorías KARMA.
    </p>

    <div class="tarjetas">

        <div class="tarjeta">
            <h3>📚 Materias registradas</h3>
            <p class="numero">
                <?= $totalMaterias ?>
            </p>
        </div>

        <div class="tarjeta">
            <h3>👩‍🏫 Tutores registrados</h3>
            <p class="numero">
                <?= $totalTutores ?>
            </p>
        </div>

        <div class="tarjeta">
            <h3>✅ Tutores verificados</h3>
            <p class="numero">
                <?= $tutoresVerificados ?>
            </p>
        </div>

        <div class="tarjeta">
            <h3>📝 Solicitudes recibidas</h3>
            <p class="numero">
                <?= $totalSolicitudes ?>
            </p>
        </div>

    </div>

    <div class="bienvenida">
        <h3>¡Bienvenido al administrador! ✨</h3>

        <p>
            Desde este panel puedes consultar las materias,
            administrar los tutores, verificar sus perfiles
            y revisar las solicitudes de tutoría recibidas.
        </p>
    </div>

</section>

<?php require __DIR__ . "/../includes/footer.php"; ?>
