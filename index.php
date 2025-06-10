<?php
include 'config.php';

// Fetch company info
$sql = "SELECT * FROM company_info WHERE id = 1";
$result = $conn->query($sql);
$company = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($company['name']); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
  </style>
</head>
<body class="bg-white text-black">
  <header class="bg-gray-100 p-6 flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <img src="<?php echo htmlspecialchars($company['logo']); ?>" alt="Logo" class="h-16 w-auto" />
      <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($company['name']); ?></h1>
    </div>
    <div class="text-right">
      <p class="font-semibold"><?php echo htmlspecialchars($company['address']); ?></p>
      <p>Email: <a href="mailto:<?php echo htmlspecialchars($company['email']); ?>" class="text-blue-600 underline"><?php echo htmlspecialchars($company['email']); ?></a></p>
      <p>Phone: <?php echo htmlspecialchars($company['phone']); ?></p>
    </div>
  </header>

  <main class="max-w-6xl mx-auto p-6 space-y-12">
    <section class="text-center">
      <h2 class="text-4xl font-bold mb-4">Welcome to <?php echo htmlspecialchars($company['name']); ?></h2>
      <p class="text-lg max-w-3xl mx-auto">
        Sweet Home Adult Family Care is an Assisted Living Facility in Apopka, Florida founded in 2023. We provide a safe, comfortable, and engaging environment for seniors who value independence, friendships, and quality medical care.
      </p>
      <img src="https://static.wixstatic.com/media/11062b_f5ab5669f0494541a7f3c0a37f710353~mv2_d_5000_3335_s_4_2.jpg" alt="Happy Senior Couple" class="mx-auto mt-6 rounded-lg shadow-lg max-w-full h-auto" />
    </section>

    <section>
      <h3 class="text-3xl font-semibold mb-6 text-center">Our Services</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div>
          <img src="https://static.wixstatic.com/media/54438f_5fdd1b0aa5454f52a9f7e0be370cbbae~mv2.png" alt="Meals" class="mx-auto mb-4 h-24 w-24" />
          <h4 class="font-bold text-xl mb-2">Meals</h4>
          <p>We serve fresh and nutritious meals prepared with the highest quality ingredients to meet every dietary need.</p>
        </div>
        <div>
          <img src="https://static.wixstatic.com/media/54438f_3f73cbd7737e40d18ea32d3e0f1721f6~mv2.jpg" alt="Medication Monitoring" class="mx-auto mb-4 h-24 w-24" />
          <h4 class="font-bold text-xl mb-2">Monitoring of Medication</h4>
          <p>Our team ensures prescriptions are filled and taken at the right times, providing peace of mind.</p>
        </div>
        <div>
          <img src="https://static.wixstatic.com/media/54438f_2a672ee9de544fa6a3231e73f676234c~mv2.jpeg" alt="Personal Care" class="mx-auto mb-4 h-24 w-24" />
          <h4 class="font-bold text-xl mb-2">Personal Care</h4>
          <p>Assistance with dressing and bathing by skilled caregivers to make daily life comfortable and stress-free.</p>
        </div>
      </div>
    </section>

    <section class="bg-gray-100 p-8 rounded-lg text-center">
      <h3 class="text-3xl font-semibold mb-4">Satisfaction or Your Money Back</h3>
      <p>We provide the highest quality services with a money-back guarantee. Visit us today and experience the difference.</p>
    </section>

    <section class="text-center">
      <h3 class="text-3xl font-semibold mb-6">Make a Payment</h3>
      <div class="flex justify-center space-x-8">
        <!-- PayPal Button -->
        <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_blank">
          <input type="hidden" name="cmd" value="_xclick" />
          <input type="hidden" name="business" value="your-paypal-business@example.com" />
          <input type="hidden" name="item_name" value="Nursing Home Service" />
          <input type="hidden" name="amount" value="100.00" />
          <input type="hidden" name="currency_code" value="USD" />
          <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 transition">Pay with PayPal</button>
        </form>

        <!-- Stripe Button (redirect to Stripe Checkout) -->
        <form action="stripe_checkout.php" method="POST">
          <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded hover:bg-purple-700 transition">Pay with Stripe</button>
        </form>
      </div>
    </section>
  </main>

  <footer class="bg-gray-100 p-6 text-center text-sm text-gray-600">
    &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['name']); ?>. All rights reserved.
  </footer>
</body>
</html>
