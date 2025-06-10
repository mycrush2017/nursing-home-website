<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT users.id, users.username, users.password, roles.role_name FROM users JOIN roles ON users.role_id = roles.id WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name'];

        if ($user['role_name'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: user.php');
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Sweet Home Adult Family Care</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
  </style>
</head>
<body class="bg-white text-black min-h-screen flex flex-col justify-center items-center">
  <div class="max-w-md w-full p-6 border border-gray-300 rounded shadow">
    <h1 class="text-2xl font-bold mb-6 text-center">Login</h1>
    <?php if (isset($error)): ?>
      <p class="text-red-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
      <div>
        <label for="username" class="block font-semibold mb-1">Username</label>
        <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>
      <div>
        <label for="password" class="block font-semibold mb-1">Password</label>
        <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2" />
      </div>
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition w-full">Login</button>
    </form>
  </div>
</body>
</html>
