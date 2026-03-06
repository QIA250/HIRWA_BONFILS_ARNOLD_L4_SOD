<?php
session_start();
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "CWSMS";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Global Variables
$today = date('Y-m-d');
$page = $_GET['page'] ?? 'dashboard';

// --- AUTHENTICATION LOGIC ---
if (isset($_POST['login'])) {
    $u = mysqli_real_escape_string($conn, $_POST['user']);
    $p = $_POST['pass'];
    $res = $conn->query("SELECT * FROM Users WHERE Username='$u' AND Password='$p'");
    if ($res->num_rows > 0) {
        $_SESSION['user'] = $u;
    } else {
        $error = "Invalid Username or Password";
    }
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit(); }

// --- DATA ACTIONS ---
if (isset($_POST['add_record'])) {
    $plate = strtoupper(mysqli_real_escape_string($conn, $_POST['plate']));
    $owner = mysqli_real_escape_string($conn, $_POST['owner']);
    $model = mysqli_real_escape_string($conn, $_POST['model']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $pkg_id = (int)$_POST['package'];
    $method = $_POST['method'];
    $amount = (int)$_POST['amount'];

    // 1. Manage Car Entity (Insert or Update)
    $conn->query("INSERT INTO Car (PlateNumber, OwnerName, CarModel, Color) 
                  VALUES ('$plate', '$owner', '$model', '$color') 
                  ON DUPLICATE KEY UPDATE OwnerName='$owner', CarModel='$model', Color='$color'");
    
    // 2. Manage Payment Entity
    $conn->query("INSERT INTO Payment (PaymentMethod) VALUES ('$method')");
    $pay_id = $conn->insert_id;

    // 3. Create Service Record
    $conn->query("INSERT INTO ServiceRecord (PlateNumber, PackageID, PaymentID, AmountPaid) 
                  VALUES ('$plate', '$pkg_id', '$pay_id', '$amount')");
    
    $success = "Successfully recorded wash for $plate";
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM ServiceRecord WHERE RecordID=$id");
    header("Location: index.php?page=reports");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QIA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg: #0b0e14;
            --sidebar: #151921;
            --card: #1c222d;
            --accent: #00f2ff;
            --text: #e2e8f0;
            --dim: #94a3b8;
        }

        * { box-sizing: border-box; transition: 0.2s; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar); border-right: 1px solid #2d3748; padding: 25px; display: flex; flex-direction: column; position: fixed; height: 100vh; }
        .logo { font-size: 24px; font-weight: 800; color: var(--accent); margin-bottom: 40px; text-align: center; letter-spacing: 1px; }
        .nav-link { padding: 12px 15px; color: var(--dim); text-decoration: none; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; gap: 12px; }
        .nav-link:hover, .nav-link.active { background: #1e293b; color: var(--accent); box-shadow: inset 4px 0 0 var(--accent); }

        /* Main Area */
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .glass-card { background: var(--card); padding: 30px; border-radius: 16px; border: 1px solid #2d3748; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-bottom: 25px; }
        
        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--card); padding: 25px; border-radius: 12px; border-bottom: 3px solid var(--accent); }
        .stat-card h4 { margin: 0; color: var(--dim); font-size: 13px; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 24px; font-weight: bold; }

        /* Inputs */
        label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--dim); }
        input, select { background: #0b0e14; border: 1px solid #2d3748; color: white; padding: 12px; border-radius: 8px; width: 100%; margin-bottom: 20px; }
        input:focus { border-color: var(--accent); outline: none; }
        .btn { background: var(--accent); color: #000; border: none; padding: 14px; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--accent); border-bottom: 2px solid #2d3748; font-size: 13px; }
        td { padding: 15px; border-bottom: 1px solid #2d3748; font-size: 14px; }
        
        @media print { .sidebar, .btn, .no-print { display: none !important; } .main-content { margin: 0; } }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user'])): ?>
    <div style="margin: auto; width: 380px;" class="glass-card">
        <h2 style="text-align: center; color: var(--accent);">CWSMS LOGIN</h2>
        <form method="POST">
            <input type="text" name="user" placeholder="Username" required>
            <input type="password" name="pass" placeholder="Password" required>
            <button type="submit" name="login" class="btn">ACCESS DASHBOARD</button>
            <?php if(isset($error)) echo "<p style='color:#f87171; text-align:center; margin-top:15px;'>$error</p>"; ?>
        </form>
    </div>
<?php else: ?>

    <div class="sidebar">
        <div class="logo">CWSMS</div>
        <a href="?page=dashboard" class="nav-link <?= $page=='dashboard'?'active':'' ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="?page=sales" class="nav-link <?= $page=='sales'?'active':'' ?>"><i class="fas fa-cash-register"></i> New Sale</a>
        <a href="?page=reports" class="nav-link <?= $page=='reports'?'active':'' ?>"><i class="fas fa-chart-line"></i> Sales Report</a>
        <a href="?page=inventory" class="nav-link <?= $page=='inventory'?'active':'' ?>"><i class="fas fa-car"></i> Vehicle Logs</a>
        <div style="margin-top: auto;">
            <a href="?logout=1" class="nav-link" style="color: #f87171;"><i class="fas fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <?php if ($page == 'dashboard'): ?>
            <div class="header-bar">
                <h1 style="margin:0 0 30px 0;">Manager Overview</h1>
            </div>
            <div class="stats-grid">
                <?php 
                $rev = $conn->query("SELECT SUM(AmountPaid) FROM ServiceRecord WHERE DATE(ServiceDate)='$today'")->fetch_row()[0];
                $cars = $conn->query("SELECT COUNT(*) FROM ServiceRecord WHERE DATE(ServiceDate)='$today'")->fetch_row()[0];
                $total_cars = $conn->query("SELECT COUNT(*) FROM Car")->fetch_row()[0];
                ?>
                <div class="stat-card"><h4>Revenue Today</h4><p><?= number_format($rev ?? 0) ?> RWF</p></div>
                <div class="stat-card"><h4>Cars (Today)</h4><p><?= $cars ?></p></div>
                <div class="stat-card"><h4>Total Registered Cars</h4><p><?= $total_cars ?></p></div>
            </div>

            <div class="glass-card">
                <h3>System Snapshot</h3>
                <p>Welcome, <strong><?= $_SESSION['user'] ?></strong>. The system is currently tracking <strong>3 active packages</strong> and processing payments via Cash, MoMo, and Card.</p>
            </div>

        <?php elseif ($page == 'sales'): ?>
            <div class="glass-card" style="max-width: 600px; margin: auto;">
                <h2 style="margin-top:0; color:var(--accent);">Register New Service</h2>
                <form method="POST">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div>
                            <label>Plate Number</label>
                            <input type="text" name="plate" placeholder="RAC 123 T" required>
                        </div>
                        <div>
                            <label>Owner Name</label>
                            <input type="text" name="owner" placeholder="Full Name" required>
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div>
                            <label>Car Model</label>
                            <input type="text" name="model" placeholder="e.g. Toyota Rav4">
                        </div>
                        <div>
                            <label>Color</label>
                            <input type="text" name="color" placeholder="e.g. White">
                        </div>
                    </div>

                    <label>Wash Package</label>
                    <select name="package" id="pkg_sel" onchange="updatePrice()">
                        <?php
                        $pkgs = $conn->query("SELECT * FROM ServicePackage");
                        while($p = $pkgs->fetch_assoc()) echo "<option value='{$p['PackageID']}' data-price='{$p['PackagePrice']}'>{$p['PackageName']}</option>";
                        ?>
                    </select>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div>
                            <label>Payment Method</label>
                            <select name="method">
                                <option>Cash</option>
                                <option>Mobile Money</option>
                                <option>Card</option>
                            </select>
                        </div>
                        <div>
                            <label>Amount (RWF)</label>
                            <input type="number" name="amount" id="amt_field" required>
                        </div>
                    </div>

                    <button type="submit" name="add_record" class="btn">COMPLETE TRANSACTION</button>
                    <?php if(isset($success)) echo "<p style='color:var(--accent); text-align:center; margin-top:15px;'>$success</p>"; ?>
                </form>
            </div>

        <?php elseif ($page == 'reports'): ?>
            <div class="glass-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3>Sales Report - <?= $today ?></h3>
                    <button onclick="window.print()" class="btn no-print" style="width:auto; padding:8px 20px;">Print Report</button>
                </div>
                <table>
                    <thead>
                        <tr><th>Plate</th><th>Package</th><th>Method</th><th>Paid</th><th>Date/Time</th><th class="no-print">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT r.*, p.PackageName, py.PaymentMethod 
                                             FROM ServiceRecord r 
                                             JOIN ServicePackage p ON r.PackageID = p.PackageID 
                                             JOIN Payment py ON r.PaymentID = py.PaymentID 
                                             WHERE DATE(r.ServiceDate) = '$today'
                                             ORDER BY r.RecordID DESC");
                        while($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['PlateNumber'] ?></td>
                                <td><?= $row['PackageName'] ?></td>
                                <td><?= $row['PaymentMethod'] ?></td>
                                <td><?= number_format($row['AmountPaid']) ?></td>
                                <td><?= $row['ServiceDate'] ?></td>
                                <td class="no-print"><a href="?delete=<?= $row['RecordID'] ?>" style="color:#f87171" onclick="return confirm('Delete this record?')"><i class="fas fa-trash"></i></a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page == 'inventory'): ?>
            <div class="glass-card">
                <h3>Vehicle Registration History</h3>
                <table>
                    <thead>
                        <tr><th>Plate Number</th><th>Owner</th><th>Model</th><th>Color</th><th>Registered On</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM Car ORDER BY RegisteredDate DESC");
                        while($row = $res->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= $row['PlateNumber'] ?></strong></td>
                                <td><?= $row['OwnerName'] ?></td>
                                <td><?= $row['CarModel'] ?></td>
                                <td><?= $row['Color'] ?></td>
                                <td><?= $row['RegisteredDate'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updatePrice() {
            var sel = document.getElementById('pkg_sel');
            var price = sel.options[sel.selectedIndex].getAttribute('data-price');
            document.getElementById('amt_field').value = price;
        }
        window.onload = updatePrice;
    </script>
<?php endif; ?>

</body>
</html>