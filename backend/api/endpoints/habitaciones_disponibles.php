<?php
header("Access-Control-Allow-Origin: *");;
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : null;
$end = isset($_GET['end']) && $_GET['end'] !== '' ? $_GET['end'] : null;
$include = isset($_GET['include_id']) ? intval($_GET['include_id']) : null;

try {
    if ($start && $end) {
        // devolver habitaciones cuyo estado sea 'disponible' y que no tengan reservas confirmadas solapadas,
        // las reservas pendientes NO bloquean la habitación
        // o incluir explícitamente la habitación indicada por include_id
        $sql = "SELECT h.* FROM habitaciones h
                 WHERE h.deleted_at IS NULL
                   AND h.estado != 'mantenimiento'
                   AND (h.id NOT IN (
                       SELECT r.habitacion_id FROM reservas r
                       WHERE r.deleted_at IS NULL
                         AND r.estado = 'confirmada'
                         AND (r.fecha_entrada <= :end AND r.fecha_salida >= :start)
                   )";
        if ($include) {
            $sql .= " OR h.id = :include_id";
        }
        $sql .= ") ORDER BY h.numero ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        if ($include) $stmt->bindParam(':include_id', $include, PDO::PARAM_INT);
    } else {
        // sin fechas: devolver habitaciones que no estén en mantenimiento
        $sql = "SELECT h.* FROM habitaciones h WHERE h.deleted_at IS NULL AND h.estado != 'mantenimiento'";
        if ($include) $sql .= " OR h.id = :include_id";
        $sql .= " ORDER BY h.numero ASC";

        $stmt = $db->prepare($sql);
        if ($include) $stmt->bindParam(':include_id', $include, PDO::PARAM_INT);
    }

    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(array('records' => $rows));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('message' => 'Error interno', 'error' => $e->getMessage()));
}

?>
