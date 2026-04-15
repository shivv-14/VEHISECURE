 
<?php
include 'config.php';

$plate = mysqli_real_escape_string($conn, $_POST['plate']);
$base64 = $_POST['photo'];

$imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
$filename = "uploads/entry/" . $plate . "_" . time() . ".jpg";
file_put_contents($filename, $imageData);

$now = date('Y-m-d H:i:s');
$sql = "INSERT INTO vehicle_logs (plate_number, entry_time, entry_photo) VALUES ('$plate', '$now', '$filename')";
mysqli_query($conn, $sql);

echo "✅ Vehicle $plate ENTRY recorded successfully!";
?>