<?php
session_start();
include 'config.php';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

// Handle company info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company']) && isset($_SESSION['admin_logged_in'])) {
    $name = $_POST['name'] ?? '';
    $logo = $_POST['logo'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    $stmt = $conn->prepare("UPDATE company_info SET name = ?, logo = ?, address = ?, email = ?, phone = ? WHERE id = 1");
    $stmt->bind_param("sssss", $name, $logo, $address, $email, $phone);
    $stmt->execute();

    $success = "Company information updated successfully.";
}

// Fetch company info for form
$sql = "SELECT * FROM company_info WHERE id = 1";
$result = $conn->query($sql);
$company = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - <?php echo htmlspecialchars($company['name']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
  </style>
</head>
<body class="bg-white text-black min-h-screen flex flex-col">
  <header class="bg-gray-100 p-6">
    <h1 class="text-3xl font-bold">Admin Dashboard</h1>
  </header>

  <main class="flex-grow max-w-4xl mx-auto p-6">
    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
      <h2 class="text-2xl font-semibold mb-4">Login</h2>
      <?php if (isset($error)): ?>
        <p class="text-red-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <form method="POST" class="space-y-4 max-w-sm">
        <div>
          <label for="username" class="block font-semibold mb-1">Username</label>
          <input type="text" id="username" name="username" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label for="password" class="block font-semibold mb-1">Password</label>
          <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <button type="submit" name="login" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Login</button>
      </form>
    <?php else: ?>
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">Edit Company Information</h2>
        <a href="admin.php?action=logout" class="text-red-600 underline">Logout</a>
      </div>
      <?php if (isset($success)): ?>
        <p class="text-green-600 mb-4"><?php echo htmlspecialchars($success); ?></p>
      <?php endif; ?>
      <form method="POST" class="space-y-4 max-w-lg">
        <div>
          <label for="name" class="block font-semibold mb-1">Company Name</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($company['name']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label for="logo" class="block font-semibold mb-1">Logo URL</label>
          <input type="url" id="logo" name="logo" value="<?php echo htmlspecialchars($company['logo']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label for="address" class="block font-semibold mb-1">Address</label>
          <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($company['address']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label for="email" class="block font-semibold mb-1">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($company['email']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <div>
          <label for="phone" class="block font-semibold mb-1">Phone</label>
          <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($company['phone']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
        </div>
        <button type="submit" name="update_company" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">Update Information</button>
      </form>
    <?php endif; ?>
  </main>

  <footer class="bg-gray-100 p-6 text-center text-sm text-gray-600">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['name']); ?> Admin Dashboard
  </footer>
</body>
</html>
