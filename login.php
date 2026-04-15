<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - VehiSecure</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-950 min-h-screen flex items-center justify-center">
    <div class="bg-zinc-900 p-10 rounded-3xl w-full max-w-md shadow-2xl">
        <h1 class="text-4xl font-bold text-emerald-400 text-center mb-2">VehiSecure</h1>
        <p class="text-zinc-400 text-center mb-8">Campus Vehicle Management</p>

        <?php
        if (isset($_POST['login'])) {
            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $password = mysqli_real_escape_string($conn, $_POST['password']);

            $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                $_SESSION['officer_id'] = $user['id'];
                $_SESSION['officer_name'] = $user['full_name'];
                header("Location: index.php");
                exit();
            } else {
                echo '<p class="text-red-500 text-center mb-4">Invalid username or password!</p>';
            }
        }
        ?>

        <form method="POST">
            <div class="mb-6">
                <label class="block text-zinc-400 mb-2">Username</label>
                <input type="text" name="username" placeholder="e.g. guard1" 
                       class="w-full bg-zinc-800 text-white px-6 py-4 rounded-2xl focus:outline-none focus:border-emerald-500" required>
            </div>
            <div class="mb-8">
                <label class="block text-zinc-400 mb-2">Password</label>
                <input type="password" name="password" placeholder="123456" 
                       class="w-full bg-zinc-800 text-white px-6 py-4 rounded-2xl focus:outline-none focus:border-emerald-500" required>
            </div>
            <button type="submit" name="login" 
                    class="w-full bg-emerald-600 hover:bg-emerald-700 py-4 rounded-2xl text-white font-bold text-lg transition">
                Login
            </button>
        </form>

        <p class="text-center text-zinc-500 text-sm mt-6">
            Demo Accounts:<br>
            guard1 / 123456<br>
            guard2 / 123456
        </p>
    </div>
</body>
</html>