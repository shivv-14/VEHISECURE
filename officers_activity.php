<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officers Activity - VehiSecure</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-zinc-950">
<div class="max-w-7xl mx-auto p-10">
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-4xl font-bold">👮 Officers Activity Log</h1>
        <a href="index.php" class="text-emerald-400 hover:text-emerald-300">← Back to Dashboard</a>
    </div>
    
    <table class="w-full bg-zinc-900 rounded-3xl overflow-hidden">
        <thead class="bg-zinc-800">
            <tr>
                <th class="p-6 text-left">Officer Name</th>
                <th>Plate Number</th>
                <th>Action</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $res = mysqli_query($conn, "SELECT recorded_by, plate_number, entry_time, exit_time 
                                    FROM vehicle_logs 
                                    ORDER BY COALESCE(exit_time, entry_time) DESC LIMIT 100");
        while($row = mysqli_fetch_assoc($res)):
            $action = $row['exit_time'] ? 'Exit' : 'Entry';
            $time = $row['exit_time'] ? $row['exit_time'] : $row['entry_time'];
            $color = $action == 'Entry' ? 'text-emerald-400' : 'text-red-400';
        ?>
        <tr class="border-t border-zinc-700 hover:bg-zinc-800">
            <td class="p-6 font-semibold"><?php echo htmlspecialchars($row['recorded_by']); ?></td>
            <td class="p-6 font-mono font-bold"><?php echo htmlspecialchars($row['plate_number']); ?></td>
            <td class="p-6">
                <span class="<?php echo $color; ?> font-medium"><?php echo $action; ?></span>
            </td>
            <td class="p-6 text-zinc-300"><?php echo $time; ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>