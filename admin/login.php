<?php
session_start();

// Configuration
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'StudioLegale2025!'; // Change this in production!

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Credenziali non valide";
    }
}
?>
<!DOCTYPE html>
<html lang="it" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Studio Legale BCS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-md border border-gray-700">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-green-400">Area Riservata</h1>
            <p class="text-gray-400">Gestione Blog Studio Legale</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-500/10 border border-red-500 text-red-500 p-3 rounded mb-4 text-sm text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Utente</label>
                <input type="text" name="username" class="w-full bg-gray-700 border border-gray-600 rounded p-2 focus:border-green-400 focus:outline-none text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <input type="password" name="password" class="w-full bg-gray-700 border border-gray-600 rounded p-2 focus:border-green-400 focus:outline-none text-white">
            </div>
            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-gray-900 font-bold py-2 rounded transition-colors">
                Accedi
            </button>
        </form>
        
        <div class="mt-6 pt-6 border-t border-gray-700">
            <a href="../index.html" class="flex items-center justify-center gap-2 text-gray-400 hover:text-green-400 transition-colors text-sm font-medium group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 group-hover:-translate-x-1 transition-transform">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Torna alla Homepage
            </a>
        </div>
    </div>
</body>
</html>
