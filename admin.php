<?php
session_start();
include 'config.php';

// Check admin login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle company info update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_company'])) {
    $name = $_POST['name'] ?? '';
    $logo = $_POST['logo'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $fees = $_POST['fees'] ?? 100.00;

    $stmt = $conn->prepare("UPDATE company_info SET name = ?, logo = ?, address = ?, email = ?, phone = ?, fees = ? WHERE id = 1");
    $stmt->bind_param("sssssd", $name, $logo, $address, $email, $phone, $fees);
    $stmt->execute();

    $success = "Company information updated successfully.";
}

// Fetch company info for form
$sql = "SELECT * FROM company_info WHERE id = 1";
$result = $conn->query($sql);
$company = $result->fetch_assoc();

// Fetch users
$users = [];
$user_result = $conn->query("SELECT users.id, users.username, users.full_name, users.email, users.phone, roles.role_name FROM users JOIN roles ON users.role_id = roles.id");
if ($user_result) {
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Handle new user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $new_username = $_POST['new_username'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_role = $_POST['new_role'] ?? 'user';
    $new_full_name = $_POST['new_full_name'] ?? '';
    $new_email = $_POST['new_email'] ?? '';
    $new_phone = $_POST['new_phone'] ?? '';

    // Get role id
    $stmt = $conn->prepare("SELECT id FROM roles WHERE role_name = ?");
    $stmt->bind_param("s", $new_role);
    $stmt->execute();
    $role_result = $stmt->get_result();
    $role = $role_result->fetch_assoc();

    if ($role && $new_username && $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role_id, full_name, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $new_username, $hashed_password, $role['id'], $new_full_name, $new_email, $new_phone);
        $stmt->execute();
        header('Location: admin.php');
        exit;
    } else {
        $user_error = "Please fill all required fields.";
    }
}

// Fetch payment history
$payments = [];
$payment_result = $conn->query("SELECT payments.id, users.username, payments.amount, payments.payment_date, payments.payment_method, payments.status FROM payments JOIN users ON payments.user_id = users.id ORDER BY payments.payment_date DESC");
if ($payment_result) {
    while ($row = $payment_result->fetch_assoc()) {
        $payments[] = $row;
    }
}

// Fetch medications
$medications = [];
$med_result = $conn->query("SELECT * FROM medications");
if ($med_result) {
    while ($row = $med_result->fetch_assoc()) {
        $medications[] = $row;
    }
}

// Fetch medication assignments
$med_assignments = [];
$assign_result = $conn->query("SELECT ma.id, m.name AS medication_name, u.username AS patient_username, a.username AS assigned_by_username, ma.assigned_at FROM medication_assignments ma JOIN medications m ON ma.medication_id = m.id JOIN users u ON ma.user_id = u.id JOIN users a ON ma.assigned_by = a.id ORDER BY ma.assigned_at DESC");
if ($assign_result) {
    while ($row = $assign_result->fetch_assoc()) {
        $med_assignments[] = $row;
    }
}

// Handle medication assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_medication'])) {
    $med_id = $_POST['medication_id'] ?? 0;
    $patient_id = $_POST['patient_id'] ?? 0;

    if ($med_id && $patient_id) {
        $stmt = $conn->prepare("INSERT INTO medication_assignments (medication_id, user_id, assigned_by) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $med_id, $patient_id, $user_id);
        $stmt->execute();
        header('Location: admin.php');
        exit;
    } else {
        $assign_error = "Please select medication and patient.";
    }
}
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
    .tab-content > div {
      display: none;
    }
    .tab-content > div.active {
      display: block;
    }
  </style>
  <script>
    function showTab(tabId) {
      const tabs = document.querySelectorAll('.tab-content > div');
      tabs.forEach(tab => tab.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');

      const tabButtons = document.querySelectorAll('.tab-buttons button');
      tabButtons.forEach(btn => btn.classList.remove('bg-blue-600', 'text-white'));
      document.querySelector('[data-tab="' + tabId + '"]').classList.add('bg-blue-600', 'text-white');
    }
    window.onload = function() {
      showTab('company-info');
    }
  </script>
</head>
<body class="bg-white text-black min-h-screen flex flex-col">
  <header class="bg-gray-100 p-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold">Admin Dashboard</h1>
    <a href="admin.php?action=logout" class="text-red-600 underline">Logout</a>
  </header>

  <main class="flex-grow max-w-6xl mx-auto p-6">
    <div class="tab-buttons flex space-x-4 mb-6">
      <button data-tab="company-info" onclick="showTab('company-info')" class="px-4 py-2 rounded font-semibold">Company Info</button>
      <button data-tab="user-management" onclick="showTab('user-management')" class="px-4 py-2 rounded font-semibold">User Management</button>
      <button data-tab="payment-history" onclick="showTab('payment-history')" class="px-4 py-2 rounded font-semibold">Payment History</button>
      <button data-tab="medication-assignments" onclick="showTab('medication-assignments')" class="px-4 py-2 rounded font-semibold">Medication Assignments</button>
    </div>

    <div class="tab-content">
      <div id="company-info" class="active">
        <h2 class="text-2xl font-semibold mb-4">Edit Company Information</h2>
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
          <div>
            <label for="fees" class="block font-semibold mb-1">Fees (USD)</label>
            <input type="number" step="0.01" id="fees" name="fees" value="<?php echo htmlspecialchars($company['fees']); ?>" required class="w-full border border-gray-300 rounded px-3 py-2" />
          </div>
          <button type="submit" name="update_company" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">Update Information</button>
        </form>
      </div>

      <div id="user-management">
        <h2 class="text-2xl font-semibold mb-4">User Management</h2>
        <?php if (isset($user_error)): ?>
          <p class="text-red-600 mb-4"><?php echo htmlspecialchars($user_error); ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4 max-w-lg mb-6">
          <h3 class="font-semibold text-lg mb-2">Create New User</h3>
          <div>
            <label for="new_username" class="block font-semibold mb-1">Username</label>
            <input type="text" id="new_username" name="new_username" required class="w-full border border-gray-300 rounded px-3 py-2" />
          </div>
          <div>
            <label for="new_password" class="block font-semibold mb-1">Password</label>
            <input type="password" id="new_password" name="new_password" required class="w-full border border-gray-300 rounded px-3 py-2" />
          </div>
          <div>
            <label for="new_role" class="block font-semibold mb-1">Role</label>
            <select id="new_role" name="new_role" class="w-full border border-gray-300 rounded px-3 py-2">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div>
            <label for="new_full_name" class="block font-semibold mb-1">Full Name</label>
            <input type="text" id="new_full_name" name="new_full_name" class="w-full border border-gray-300 rounded px-3 py-2" />
          </div>
          <div>
            <label for="new_email" class="block font-semibold mb-1">Email</label>
            <input type="email" id="new_email" name="new_email" class="w-full border border-gray-300 rounded px-3 py-2" />
          </div>
          <div>
            <label for="new_phone" class="block font-semibold mb-1">Phone</label>
            <input type="text" id="new_phone" name="new_phone" class="w-full border border-gray-300 rounded px-3 py-2" />
          </div>
          <button type="submit" name="create_user" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Create User</button>
        </form>

        <h3 class="font-semibold text-lg mb-2">Existing Users</h3>
        <table class="w-full border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border border-gray-300 p-2">Username</th>
              <th class="border border-gray-300 p-2">Full Name</th>
              <th class="border border-gray-300 p-2">Email</th>
              <th class="border border-gray-300 p-2">Phone</th>
              <th class="border border-gray-300 p-2">Role</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($u['username']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($u['full_name']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($u['email']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($u['phone']); ?></td>
                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($u['role_name']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div id="payment-history">
        <h2 class="text-2xl font-semibold mb-4">Payment History</h2>
        <?php if (count($payments) > 0): ?>
          <table class="w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="border border-gray-300 p-2">User</th>
                <th class="border border-gray-300 p-2">Amount</th>
                <th class="border border-gray-300 p-2">Date</th>
                <th class="border border-gray-300 p-2">Method</th>
                <th class="border border-gray-300 p-2">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
                <tr>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($p['username']); ?></td>
                  <td class="border border-gray-300 p-2">$<?php echo htmlspecialchars($p['amount']); ?></td>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($p['payment_date']); ?></td>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($p['payment_method']); ?></td>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($p['status']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No payment history.</p>
        <?php endif; ?>
      </div>

      <div id="medication-assignments">
        <h2 class="text-2xl font-semibold mb-4">Medication Assignments</h2>
        <?php if (isset($assign_error)): ?>
          <p class="text-red-600 mb-4"><?php echo htmlspecialchars($assign_error); ?></p>
        <?php endif; ?>
        <form method="POST" class="space-y-4 max-w-lg mb-6">
          <h3 class="font-semibold text-lg mb-2">Assign Medication to Patient</h3>
          <div>
            <label for="medication_id" class="block font-semibold mb-1">Medication</label>
            <select id="medication_id" name="medication_id" class="w-full border border-gray-300 rounded px-3 py-2">
              <option value="">Select Medication</option>
              <?php foreach ($medications as $med): ?>
                <option value="<?php echo $med['id']; ?>"><?php echo htmlspecialchars($med['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="patient_id" class="block font-semibold mb-1">Patient</label>
            <select id="patient_id" name="patient_id" class="w-full border border-gray-300 rounded px-3 py-2">
              <option value="">Select Patient</option>
              <?php foreach ($users as $u): ?>
                <?php if ($u['role_name'] === 'user'): ?>
                  <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['username']); ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" name="assign_medication" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 transition">Assign Medication</button>
        </form>

        <h3 class="font-semibold text-lg mb-2">Existing Assignments</h3>
        <?php if (count($med_assignments) > 0): ?>
          <table class="w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-200">
                <th class="border border-gray-300 p-2">Medication</th>
                <th class="border border-gray-300 p-2">Patient</th>
                <th class="border border-gray-300 p-2">Assigned By</th>
                <th class="border border-gray-300 p-2">Assigned At</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($med_assignments as $assign): ?>
                <tr>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($assign['medication_name']); ?></td>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($assign['patient_username']); ?></td>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($assign['assigned_by_username']); ?></td>
                  <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($assign['assigned_at']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No medication assignments.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer class="bg-gray-100 p-6 text-center text-sm text-gray-600">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['name']); ?> Admin Dashboard
  </footer>
</body>
</html>
