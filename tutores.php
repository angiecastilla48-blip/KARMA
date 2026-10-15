
<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

require_once __DIR__ . "/includes/conexion.php";

$materiaSeleccionada = filter_input(
    INPUT_GET,
    "materia",
    FILTER_VALIDATE_INT
);

if (
    $materiaSeleccionada === false ||
    ($materiaSeleccionada !== null && $materiaSeleccionada < 1)
) {
    $materiaSeleccionada = null;
}

// Obtener las materias disponibles
$consultaMaterias = $mysqli->query(
    "SELECT id_materia AS id, nombre
     FROM materia
     ORDER BY nombre ASC"
);

$materias = $consultaMaterias->fetch_all(MYSQLI_ASSOC);

// Obtener los tutores verificados
$sql = "SELECT
            t.id_tutor AS id,
            t.nombre,
            t.descripcion,
            t.telefono,
            t.foto_url,
            m.nombre AS nombre_materia
        FROM tutor t
        LEFT JOIN materia m ON t.id_materia = m.id_materia
        WHERE t.verificado = 1";

if ($materiaSeleccionada !== null) {
    $sql .= " AND t.id_materia = ?";
}

$sql .= " ORDER BY t.nombre ASC";

$consulta = $mysqli->prepare($sql);

if ($materiaSeleccionada !== null) {
    $consulta->bind_param("i", $materiaSeleccionada);
}

$consulta->execute();
$tutores = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
$consulta->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tutores | KARMA</title>

    <!-- Fuentes de KARMA -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Nunito", Arial, sans-serif;
            background-color: #f5f0fa;
            color: #3d3150;
        }

        /* Navegación */
        .nav-publica {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding: 12px 40px;
            background: white;
            border-bottom: 1.5px solid #e4d9f2;
        }

        .nav-marca {
            color: #6c4a9e;
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }

        .nav-links a {
            color: #3d3150;
            text-decoration: none;
            font-weight: 700;
        }

        .nav-links a:hover {
            color: #6c4a9e;
        }

        /* Presentación */
        .tutores-portada {
            background: #d9f2d5;
            padding: 65px 20px;
            text-align: center;
        }

        .etiqueta {
            color: #6c4a9e;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .tutores-portada h1 {
            color: #6c4a9e;
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 45px;
            margin: 12px 0;
        }

        .tutores-portada p {
            max-width: 600px;
            margin: auto;
            line-height: 1.7;
            font-size: 17px;
        }

        /* Contenido principal */
        .contenedor {
            max-width: 1100px;
            margin: auto;
            padding: 55px 25px 70px;
        }

        .titulo-seccion {
            color: #6c4a9e;
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .texto-seccion {
            color: #6e627b;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        /* Filtro */
        .filtro {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 15px;
            background: white;
            border: 1.5px solid #e4d9f2;
            padding: 22px;
            border-radius: 18px;
            margin-bottom: 30px;
        }

        .filtro-grupo {
            flex: 1;
            min-width: 200px;
        }

        .filtro label {
            display: block;
            color: #6c4a9e;
            font-weight: 800;
            margin-bottom: 9px;
        }

        .filtro select {
            width: 100%;
            padding: 13px;
            border: 1.5px solid #cbb8e6;
            border-radius: 10px;
            background: #fdfbff;
            color: #3d3150;
            font-family: inherit;
            font-size: 15px;
        }

        /* Botones */
        .boton {
            display: inline-block;
            padding: 13px 22px;
            border: none;
            border-radius: 10px;
            background: #6c4a9e;
            color: white;
            font-family: inherit;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .boton:hover {
            background: #8564b5;
        }

        .boton-secundario {
            background: #c8f0c5;
            color: #3d3150;
        }

        .boton-secundario:hover {
            background: #b9e7b5;
        }

        /* Cantidad de tutores */
        .cantidad {
            color: #6e627b;
            margin-bottom: 20px;
        }

        .cantidad strong {
            color: #6c4a9e;
        }

        /* Tarjetas */
        .rejilla-tutores {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(245px, 1fr)
            );
            gap: 25px;
        }

        .tarjeta-tutor {
            background: white;
            border: 1.5px solid #e4d9f2;
            border-radius: 20px;
            padding: 28px 23px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(108, 74, 158, 0.12);
        }

        .avatar {
            width: 85px;
            height: 85px;
            margin: 0 auto 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background: #dcdcf7;
            color: #6c4a9e;
            font-size: 32px;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tarjeta-tutor h3 {
            color: #6c4a9e;
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 22px;
            margin-bottom: 10px;
        }

        .verificado {
            display: inline-block;
            color: #3d3150;
            background: #c8f0c5;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .materia {
            display: block;
            color: #6c4a9e;
            font-weight: 800;
            margin-bottom: 13px;
        }

        .descripcion {
            color: #6e627b;
            line-height: 1.6;
            font-size: 14px;
            margin-bottom: 22px;
        }

        .boton-perfil {
            display: block;
            width: 100%;
        }

        /* Cuando no hay resultados */
        .sin-resultados {
            grid-column: 1 / -1;
            padding: 40px 20px;
            background: #e4d9f2;
            border-radius: 18px;
            text-align: center;
        }

        .sin-resultados h3 {
            color: #6c4a9e;
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .sin-resultados p {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        /* Pie de página */
        .pie {
            padding: 28px 20px;
            background: #6c4a9e;
            color: white;
            text-align: center;
        }

        .pie strong {
            font-family: "Bricolage Grotesque", Arial, sans-serif;
            font-size: 22px;
        }

        .pie p {
            margin-top: 8px;
            line-height: 1.6;
        }

        /* Adaptación a celulares */
        @media (max-width: 650px) {
            .nav-publica {
                justify-content: center;
                padding: 18px;
            }

            .nav-links {
                justify-content: center;
                gap: 15px;
            }

            .tutores-portada {
                padding: 45px 18px;
            }

            .tutores-portada h1 {
                font-size: 34px;
            }

            .contenedor {
                padding: 35px 16px 50px;
            }

            .filtro {
                align-items: stretch;
            }

            .filtro .boton {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <nav class="nav-publica">
        <a href="index.php" class="nav-marca">KARMA ✦</a>

        <div class="nav-links">
            <a href="index.php">Inicio</a>
            <a href="tutores.php">Tutores</a>
            <a href="index.php#nosotros">Nosotros</a>
        </div>
    </nav>

    <header class="tutores-portada">
        <span class="etiqueta">Aprender juntos</span>

        <h1>Encuentra tu tutor ideal ✦</h1>

        <p>
            Cada persona aprende de una manera diferente.
            Encuentra un tutor que te acompañe y te ayude
            a comprender mejor tus materias.
        </p>
    </header>

    <main class="contenedor">

        <h2 class="titulo-seccion">Nuestros tutores</h2>

        <p class="texto-seccion">
            Explora las materias y conoce a nuestros tutores verificados.
        </p>

        <form method="GET" action="tutores.php" class="filtro">

            <div class="filtro-grupo">
                <label for="materia">Selecciona una materia</label>

                <select name="materia" id="materia">
                    <option value="">Todas las materias</option>

                    <?php foreach ($materias as $materia): ?>
                        <option
                            value="<?= (int) $materia['id'] ?>"
                            <?= $materiaSeleccionada !== null
                                && $materiaSeleccionada == $materia['id']
                                ? 'selected'
                                : '' ?>
                        >
                            <?= htmlspecialchars($materia['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="boton">
                Filtrar tutores
            </button>

            <a href="tutores.php" class="boton boton-secundario">
                Ver todos
            </a>

        </form>

        <p class="cantidad">
            Tutores encontrados:
            <strong><?= count($tutores) ?></strong>
        </p>

        <section class="rejilla-tutores">

            <?php if (!empty($tutores)): ?>

                <?php foreach ($tutores as $tutor): ?>

                    <article class="tarjeta-tutor">

                        <div class="avatar">
                            <?php if (!empty($tutor['foto_url'])): ?>
                                <img
                                    src="<?= htmlspecialchars($tutor['foto_url']) ?>"
                                    alt="Foto de <?= htmlspecialchars($tutor['nombre']) ?>"
                                >
                            <?php else: ?>
                                ✦
                            <?php endif; ?>
                        </div>

                        <h3>
                            <?= htmlspecialchars($tutor['nombre']) ?>
                        </h3>

                        <div class="verificado">
                            ✓ Tutor verificado
                        </div>

                        <p class="materia">
                            📚
                            <?= htmlspecialchars(
                                $tutor['nombre_materia']
                                ?? 'Materia no asignada'
                            ) ?>
                        </p>

                        <p class="descripcion">
                            <?= htmlspecialchars(
                                $tutor['descripcion']
                                ?? 'Tutor disponible para acompañarte en tu aprendizaje.'
                            ) ?>
                        </p>

                        <a
                            href="tutor.php?id=<?= (int) $tutor['id'] ?>"
                            class="boton boton-perfil"
                        >
                            Conocer perfil
                        </a>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="sin-resultados">
                    <h3>¡Pronto encontrarás tu tutor! ✦</h3>

                    <p>
                        No encontramos tutores verificados para esta materia.
                        Prueba seleccionando otra o consulta todos los tutores.
                    </p>

                    <a href="tutores.php" class="boton">
                        Ver todos los tutores
                    </a>
                </div>

            <?php endif; ?>

        </section>

    </main>

    <footer class="pie">
        <strong>KARMA ✦</strong>
        <p>Un espacio para aprender, compartir y crecer.</p>
    </footer>

</body>
</html>
