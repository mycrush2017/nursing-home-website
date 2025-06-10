<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = basename($_FILES['document']['name']);
    $targetFilePath = $uploadDir . time() . '_' . $fileName;

    if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFilePath)) {
        $stmt = $conn->prepare("INSERT INTO documents (user_id, file_name, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $fileName, $targetFilePath);
        $stmt->execute();
        $stmt->close();
        header('Location: user.php?upload=success');
        exit;
    } else {
        $error = "Failed to upload document.";
    }
} else {
    $error = "No document uploaded.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Upload Document - Sweet Home Adult Family Care</title>
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
    <h1 class="text-2xl font-bold mb-6 text-center">Upload Document</h1>
    <?php if (isset($error)): ?>
      <p class="text-red-600 mb-4"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <a href="user.php" class="text-blue-600 underline">Back to Dashboard</a>
  </div>
</body>
</html>
