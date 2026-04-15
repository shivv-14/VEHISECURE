<?php
include 'config.php';

$plate = mysqli_real_escape_string($conn, strtoupper($_POST['plate']));
$base64 = $_POST['photo'];

// === ADVANCED DSA: HASH MAP (Hash Table) for fast lookup ===
$open_vehicles = [];   // Key = plate_number, Value = id
$res = mysqli_query($conn, "SELECT id, plate_number FROM vehicle_logs WHERE exit_time IS NULL");

while ($row = mysqli_fetch_assoc($res)) {
    $open_vehicles[$row['plate_number']] = $row['id'];   // Hashing
}
// =======================================================

if (isset($open_vehicles[$plate])) {
    $id = $open_vehicles[$plate];

    // Save exit photo
    $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
    $filename = "uploads/exit/" . $plate . "_" . time() . ".jpg";
    file_put_contents($filename, $imageData);

    $now = date('Y-m-d H:i:s');
    
    $sql = "UPDATE vehicle_logs SET exit_time = '$now', exit_photo = '$filename' WHERE id = $id";
    mysqli_query($conn, $sql);

    echo "✅ Vehicle $plate EXIT recorded successfully!";
} else {
    echo "❌ No open entry found for plate $plate! Vehicle may already be out or not entered.";
}
?>