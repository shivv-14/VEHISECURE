<?php include 'config.php'; ?>
<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records - VehiSecure</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-zinc-950">
<div class="max-w-7xl mx-auto p-10">
    <h1 class="text-4xl font-bold mb-6">📋 All Vehicle Records</h1>

    <!-- Search Box -->
    <div class="mb-8">
        <input type="text" id="searchPlate" 
               class="w-full max-w-md bg-zinc-800 border border-zinc-700 text-white px-6 py-4 rounded-2xl text-lg font-mono focus:border-emerald-500 outline-none"
               placeholder="Search by Plate Number (e.g. TS05AB1234)"
               onkeyup="searchRecords()">
    </div>

    <table class="w-full bg-zinc-900 rounded-3xl overflow-hidden" id="recordsTable">
        <thead class="bg-zinc-800">
            <tr>
                <th class="p-6 text-left">Plate</th>
                <th>Entry Time</th>
                <th>Exit Time</th>
                <th>Entry Photo</th>
                <th>Exit Photo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="recordsBody">
        <?php
        // Fetch all records
        $res = mysqli_query($conn, "SELECT * FROM vehicle_logs");
        $all_records = [];
        while($row = mysqli_fetch_assoc($res)){
            $all_records[] = $row;
        }

        // === ADVANCED DSA: SORTING ===
        usort($all_records, function($a, $b) {
            return strtotime($b['entry_time']) - strtotime($a['entry_time']);
        });

        foreach($all_records as $row):
            $exit = $row['exit_time'] ? $row['exit_time'] : '<span class="text-emerald-400 font-bold">-</span>';
            $status = $row['exit_time'] 
                ? '<span class="bg-red-500 text-white px-6 py-1 rounded-full font-bold block text-center mx-auto">OUT</span>' 
                : '<span class="bg-emerald-500 text-white px-6 py-1 rounded-full font-bold block text-center mx-auto">INSIDE CAMPUS</span>';
        ?>
        <tr class="border-t border-zinc-700 hover:bg-zinc-800 record-row" 
            data-plate="<?php echo strtolower($row['plate_number']); ?>">
            <td class="p-6 font-mono font-bold"><?php echo htmlspecialchars($row['plate_number']); ?></td>
            <td class="p-4"><?php echo $row['entry_time']; ?></td>
            <td class="p-4"><?php echo $exit; ?></td>
            <td class="p-4">
                <img src="<?php echo $row['entry_photo']; ?>" class="w-20 h-20 object-cover rounded-xl">
            </td>
            <td class="p-4">
                <?php echo $row['exit_photo'] 
                    ? '<img src="'.$row['exit_photo'].'" class="w-20 h-20 object-cover rounded-xl">' 
                    : '-'; 
                ?>
            </td>
            <td class="p-4"><?php echo $status; ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// === ADVANCED DSA: BINARY SEARCH ===
function binarySearch(arr, target) {
    let left = 0, right = arr.length - 1;
    target = target.toLowerCase().trim();
    
    while (left <= right) {
        let mid = Math.floor((left + right) / 2);
        let plate = arr[mid].getAttribute('data-plate');
        
        if (plate === target) return mid;
        if (plate < target) left = mid + 1;
        else right = mid - 1;
    }
    return -1;
}

function searchRecords() {
    let input = document.getElementById('searchPlate').value.trim();
    let rows = document.querySelectorAll('.record-row');
    let rowArray = Array.from(rows);

    if (input === "") {
        rows.forEach(row => row.style.display = "");
        return;
    }

    // Sort rows by plate for binary search
    rowArray.sort((a, b) => a.getAttribute('data-plate').localeCompare(b.getAttribute('data-plate')));

    let index = binarySearch(rowArray, input);
    
    // Hide all rows first
    rows.forEach(row => row.style.display = "none");

    if (index !== -1) {
        rowArray[index].style.display = "";
    } else {
        // Fallback: show partial matches
        rows.forEach(row => {
            if (row.getAttribute('data-plate').includes(input.toLowerCase())) {
                row.style.display = "";
            }
        });
    }
}
</script>
</body>
</html>