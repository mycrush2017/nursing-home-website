<?php
include 'config.php';

// Check if user is logged in and role is user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT username, full_name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch assigned medications
$stmt = $conn->prepare("
    SELECT m.name, m.description, ua.assigned_at, u.username AS assigned_by_username
    FROM medication_assignments ua
    JOIN medications m ON ua.medication_id = m.id
    JOIN users u ON ua.assigned_by = u.id
    WHERE ua.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$medications = $stmt->get_result();

// Fetch payment history
$stmt = $conn->prepare("SELECT amount, payment_date, payment_method, status FROM payments WHERE user_id = ? ORDER BY payment_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payments = $stmt->get_result();

// Fetch appointments
$stmt = $conn->prepare("SELECT appointment_date, appointment_time, status FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$appointments = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Dashboard - Sweet Home Adult Family Care</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
  </style>
</head>
<body class="bg-white text-black min-h-screen">
  <header class="bg-gray-100 p-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold">User Dashboard</h1>
    <a href="logout.php" class="text-red-600 underline">Logout</a>
  </header>

  <main class="max-w-6xl mx-auto p-6 space-y-12">
    <section>
      <h2 class="text-2xl font-semibold mb-4">Welcome, <?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h2>
      <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
      <p>Phone: <?php echo htmlspecialchars($user['phone']); ?></p>
    </section>

    <section>
      <h3 class="text-xl font-semibold mb-2">Assigned Medications</h3>
      <?php if ($medications->num_rows > 0): ?>
        <ul class="list-disc list-inside">
          <?php while ($med = $medications->fetch_assoc()): ?>
            <li>
              <strong><?php echo htmlspecialchars($med['name']); ?></strong>: <?php echo htmlspecialchars($med['description']); ?>
              <br />
              Assigned by: <?php echo htmlspecialchars($med['assigned_by_username']); ?> on <?php echo htmlspecialchars($med['assigned_at']); ?>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No medications assigned.</p>
      <?php endif; ?>
    </section>

    <section>
      <h3 class="text-xl font-semibold mb-2">Payment History</h3>
      <?php if ($payments->num_rows > 0): ?>
        <table class="w-full border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border border-gray-300 p-2">Amount</th>
              <th class="border border-gray-300 p-2">Date</th>
              <th class="border border-gray-300 p-2">Method</th>
              <th class="border border-gray-300 p-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($pay = $payments->fetch_assoc()): ?>
              <tr>
                <td class="border border-gray-300 p-2">$<?php echo htmlspecialchars($pay['amount']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($pay['payment_date']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($pay['payment_method']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($pay['status']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No payment history.</p>
      <?php endif; ?>
    </section>

    <section>
      <h3 class="text-xl font-semibold mb-2">Appointments</h3>
      <?php if ($appointments->num_rows > 0): ?>
        <table class="w-full border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border border-gray-300 p-2">Date</th>
              <th class="border border-gray-300 p-2">Time</th>
              <th class="border border-gray-300 p-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($app = $appointments->fetch_assoc()): ?>
              <tr>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($app['appointment_date']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($app['appointment_time']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($app['status']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No appointments booked.</p>
      <?php endif; ?>
    </section>

    <section>
      <h3 class="text-xl font-semibold mb-2">Upload Documents</h3>
      <form action="upload_document.php" method="POST" enctype="multipart/form-data" class="space-y-4 max-w-md">
        <input type="file" name="document" required class="border border-gray-300 rounded px-3 py-2 w-full" />
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">Upload</button>
      </form>
    </section>
  </main>

  <footer class="bg-gray-100 p-6 text-center text-sm text-gray-600">
    &copy; <?php echo date('Y'); ?> Sweet Home Adult Family Care User Dashboard
  </footer>
</body>
</html>
