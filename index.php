<?php
declare(strict_types=1);//ya lo vimos en config/database.php,

// Requerimos el archivo de conexión usando require_once para asegurar su carga única
require_once __DIR__ . '/config/database.php';

// Obtención de la conexión PDO
$pdo = getPDOConnection();

/**
 * EJEMPLO DEL MÉTODO GET:
 * Leemos la variable 'buscar' de la URL ($_GET).
 * Usamos el operador de fusión de nulos (??) para asignar una cadena vacía si no existe el parámetro.
 * filter_input sanitiza la entrada filtrando caracteres no deseados recibidos por GET.
 */
$busqueda = filter_input(INPUT_GET, 'textbuscar') ?? '';
$busqueda = trim((string)$busqueda);

// Construcción de la consulta con Prepared Statements para búsqueda dinámica
if (!empty($busqueda)) {
    // Consulta parametrizada con marcador posicional o con nombre (:busqueda)
    $sql = "SELECT * FROM alumnos WHERE nombre LIKE :busqueda1 OR club LIKE :busqueda2 ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    
    // Ejecutamos pasando el parámetro con los comodines de SQL (%)
    $stmt->execute([':busqueda1' => '%' . $busqueda . '%', ':busqueda2' => '%' . $busqueda . '%']);
} else {
    // Consulta sin filtro si no hay término de búsqueda
    $sql = "SELECT * FROM alumnos ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

// fetchAll() recupera todos los registros resultantes como un arreglo asociativo multidimensional
$alumnos = $stmt->fetchAll();

/**
 * FUNCIÓN AUXILIAR DE SEGURIDAD (Prevención de XSS):
 * htmlspecialchars convierte caracteres especiales en entidades HTML (ej. '<' en '&lt;').
 * Se debe aplicar SIEMPRE que se imprima texto proveniente de la BD o de entradas de usuario.
 */
function e(string $cadena): string {
    return htmlspecialchars($cadena, ENT_QUOTES, 'UTF-8');
    //<script>alert('XSS');</script> se convierte en 
    //&lt;script&gt;alert(&#039;XSS&#039;);&lt;/script&gt;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Control de Alumnos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f6f8; }
        h1 { color: #2c3e50; }
        .tabla { width: 100%; border-collapse: collapse; background: #fff; margin-top: 15px; }
        .tabla th, .tabla td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .tabla th { background-color: #34495e; color: #fff; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: #fff; display: inline-block; }
        .btn-crear { background-color: #27ae60; margin-bottom: 10px; }
        .btn-editar { background-color: #f39c12; }
        .btn-eliminar { background-color: #e74c3c; border: none; cursor: pointer; }
        .form-inline { display: inline-block; margin-bottom: 15px; }
        input[type="text"] { padding: 6px; width: 250px; }
    </style>
</head>
<body>

    <h1>Listado de Alumnos y Clubes</h1>

    <!-- Formulario que utiliza el método GET para enviar el parámetro de búsqueda en la URL -->
    <form action="index.php" method="GET" class="form-inline">
        <input type="text" name="textbuscar" placeholder="Buscar por nombre o club..." value="<?= e($busqueda) ?>">
        <button type="submit" class="btn" style="background-color: #2980b9;">Buscar (GET)</button>
        <?php if (!empty($busqueda)): ?>
            <a href="index.php" style="margin-left: 10px;">Limpiar búsqueda</a>
        <?php endif; ?>
    </form>

    <br>
    <a href="crear.php" class="btn btn-crear">+ Registrar Nuevo Alumno</a>

    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Correo Electrónico</th>
                <th>Club Extracurricular</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($alumnos)): ?>
                <tr>
                    <td colspan="6">No se encontraron registros.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <!-- Aplicación obligatoria de e() para evitar scripts inyectados (XSS) -->
                        <td><?= e((string)$alumno['id']) ?></td>
                        <td><?= e($alumno['nombre']) ?></td>
                        <td><?= e($alumno['email']) ?></td>
                        <td><?= e($alumno['club']) ?></td>
                        <td><?= e($alumno['estatus']) ?></td>
                        <td>
                            <!-- Pasamos el ID mediante parámetro GET en la URL para editar -->
                            <a href="editar.php?id=<?= e((string)$alumno['id']) ?>" class="btn btn-editar">Editar</a>

                            <!-- Formulario inline para eliminar usando POST (NUNCA usar enlaces GET para eliminar) -->
                            <form action="eliminar.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este alumno?');">
                                <input type="hidden" name="id" value="<?= e((string)$alumno['id']) ?>">
                                <button type="submit" class="btn btn-eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>