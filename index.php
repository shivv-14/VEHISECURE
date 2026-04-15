<?php include 'config.php'; ?>
<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiSecure - Campus Vehicle Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .stat-card { background: linear-gradient(135deg, #10b981, #059669); }
        .status-inside { color: #10b981; font-weight: 600; }
        .status-out { color: #ef4444; font-weight: 600; }
    </style>
</head>
<body class="min-h-screen bg-zinc-950">
<div class="flex">
    <!-- Sidebar -->
    <div class="w-72 bg-zinc-950 h-screen fixed p-6 border-r border-zinc-800">
        <h1 class="text-3xl font-bold text-emerald-400 mb-10">VehiSecure</h1>
<nav class="space-y-2">
    <a href="index.php" class="flex items-center gap-3 bg-emerald-500 text-white px-6 py-4 rounded-2xl font-semibold">🏠 Dashboard</a>
    <a href="entry.php" class="flex items-center gap-3 hover:bg-zinc-800 px-6 py-4 rounded-2xl font-medium">🚘 Vehicle Entry</a>
    <a href="exit.php" class="flex items-center gap-3 hover:bg-zinc-800 px-6 py-4 rounded-2xl font-medium">🚪 Vehicle Exit</a>
    <a href="records.php" class="flex items-center gap-3 hover:bg-zinc-800 px-6 py-4 rounded-2xl font-medium">📋 All Records</a>
    <a href="officers_activity.php" class="flex items-center gap-3 hover:bg-zinc-800 px-6 py-4 rounded-2xl font-medium">👮 Officers Activity</a>
    
    <div class="mt-12 pt-6 border-t border-zinc-700">
        <p class="text-zinc-400 text-sm">Logged in as</p>
        <p class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['officer_name']); ?></p>
        <a href="logout.php" class="text-red-400 hover:text-red-500 text-sm mt-4 block">Logout</a>
    </div>
</nav>
    </div>

    <!-- Main Content -->
    <div class="ml-72 flex-1 p-10">
        <h1 class="text-4xl font-bold mb-2 text-white">Welcome, Security Officer</h1>
        <p class="text-zinc-400 mb-10">Real-time Campus Vehicle Tracking System</p>

        <?php
        $today = date('Y-m-d');
        $entered_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM vehicle_logs WHERE DATE(entry_time)='$today'"))['c'];
        $inside = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM vehicle_logs WHERE exit_time IS NULL"))['c'];
        $total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM vehicle_logs"))['c'];
        ?>

        <div class="grid grid-cols-3 gap-6">
            <div class="stat-card p-8 rounded-3xl text-white shadow-2xl card-hover">
                <p class="text-emerald-100">Entered Today</p>
                <p class="text-6xl font-bold"><?php echo $entered_today; ?></p>
            </div>
            <div class="bg-zinc-900 p-8 rounded-3xl shadow-2xl card-hover border border-emerald-500">
                <p class="text-emerald-400">Currently Inside Campus</p>
                <p class="text-6xl font-bold text-white"><?php echo $inside; ?></p>
            </div>
            <div class="bg-zinc-900 p-8 rounded-3xl shadow-2xl card-hover">
                <p class="text-zinc-400">Total Records</p>
                <p class="text-6xl font-bold"><?php echo $total; ?></p>
            </div>
        </div>

        <div class="mt-12 bg-zinc-900 rounded-3xl p-8 glass">
            <h2 class="text-2xl font-semibold mb-6 text-white">Recent Activity</h2>
            <table class="w-full">
                <thead>
                    <tr class="text-zinc-400 border-b border-zinc-700">
                        <th class="text-left py-4">Plate Number</th>
                        <th>Entry Time</th>
                        <th>Exit Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $res = mysqli_query($conn, "SELECT * FROM vehicle_logs ORDER BY id DESC LIMIT 8");
                while($row = mysqli_fetch_assoc($res)):
                    $exit_time = $row['exit_time'] ? $row['exit_time'] : '-';
                    $status = $row['exit_time'] 
                        ? '<span class="status-out">Out</span>' 
                        : '<span class="status-inside">Inside Campus</span>';
                ?>
                <tr class="border-t border-zinc-700 hover:bg-zinc-800 transition">
                    <td class="py-5 font-mono font-bold text-white"><?php echo htmlspecialchars($row['plate_number']); ?></td>
                    <td class="py-5 text-zinc-300"><?php echo $row['entry_time']; ?></td>
                    <td class="py-5 text-zinc-300"><?php echo $exit_time; ?></td>
                    <td class="py-5"><?php echo $status; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <a href="records.php" class="mt-8 inline-block bg-white text-black px-8 py-4 rounded-2xl font-semibold hover:bg-zinc-200 transition">View All Records →</a>
        </div>
    </div>
</div>
</body>
</html>