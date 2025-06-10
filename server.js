const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const session = require('express-session');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
  secret: 'nursing-home-secret',
  resave: false,
  saveUninitialized: true,
  cookie: { secure: false }
}));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));

// Load company info from JSON file
const companyInfoPath = path.join(__dirname, 'data', 'company.json');
let companyInfo = {
  name: "Sweet Home Adult Family Care",
  logo: "https://static.wixstatic.com/media/11062b_fce4349362194db9a95427b6d511ebaff000.jpg",
  contact: {
    address: "2953 Bickley Drive, Apopka, FL, USA",
    email: "info@sweethome.com",
    phone: "(123) 456-7890"
  }
};

function loadCompanyInfo() {
  if (fs.existsSync(companyInfoPath)) {
    const data = fs.readFileSync(companyInfoPath, 'utf-8');
    companyInfo = JSON.parse(data);
  } else {
    saveCompanyInfo();
  }
}

function saveCompanyInfo() {
  fs.mkdirSync(path.dirname(companyInfoPath), { recursive: true });
  fs.writeFileSync(companyInfoPath, JSON.stringify(companyInfo, null, 2));
}

// Load company info on server start
loadCompanyInfo();

// Admin credentials
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'admin123';

// Admin login endpoint
app.post('/api/admin/login', (req, res) => {
  const { username, password } = req.body;
  if (username === ADMIN_USERNAME && password === ADMIN_PASSWORD) {
    req.session.isAdmin = true;
    res.json({ success: true, message: 'Login successful' });
  } else {
    res.status(401).json({ success: false, message: 'Invalid credentials' });
  }
});

// Admin logout endpoint
app.post('/api/admin/logout', (req, res) => {
  req.session.destroy();
  res.json({ success: true, message: 'Logged out' });
});

// Middleware to check admin session
function checkAdmin(req, res, next) {
  if (req.session.isAdmin) {
    next();
  } else {
    res.status(403).json({ success: false, message: 'Unauthorized' });
  }
}

// Get company info
app.get('/api/company', (req, res) => {
  res.json(companyInfo);
});

// Update company info (admin only)
app.post('/api/company', checkAdmin, (req, res) => {
  const { name, logo, contact } = req.body;
  if (name) companyInfo.name = name;
  if (logo) companyInfo.logo = logo;
  if (contact) companyInfo.contact = contact;
  saveCompanyInfo();
  res.json({ success: true, message: 'Company info updated', companyInfo });
});

// Simulated payment webhook endpoints
app.post('/api/payment/paypal', (req, res) => {
  // Simulate PayPal payment processing
  console.log('Received PayPal payment:', req.body);
  res.json({ success: true, message: 'PayPal payment processed (simulated)' });
});

app.post('/api/payment/stripe', (req, res) => {
  // Simulate Stripe payment processing
  console.log('Received Stripe payment:', req.body);
  res.json({ success: true, message: 'Stripe payment processed (simulated)' });
});

// Start server
app.listen(PORT, () => {
  console.log("Server running on http://localhost:" + PORT);
});
