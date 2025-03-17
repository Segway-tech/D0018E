

<?php
session_start();
require 'db_connect.php';

//check if user is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// admin can see pending orders, etc.
$ordersStmt = $pdo->query("
    SELECT o.*, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$usersStmt = $pdo->query("SELECT * FROM users WHERE role='user'");
$allUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

//handle add/edit/remove for assets 

// if this is a post request, then check if it's adding or editing a product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_asset') {

        // insert new product into assets
        $videogame_title = $_POST['videogame_title'];
        $platform        = $_POST['platform'];
        $price           = $_POST['price'];
        $stock_amount    = $_POST['stock_amount'];
        $used_condition  = $_POST['used_condition'];
        $copy_type       = $_POST['copy_type'];

        $addStmt = $pdo->prepare("
            INSERT INTO assets (videogame_title, platform, price, stock_amount, used_condition, copy_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $addStmt->execute([$videogame_title, $platform, $price, $stock_amount, $used_condition, $copy_type]);

        header("Location: admin_dashboard.php");
        exit;

    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit_asset') {
        // update an existing product
        $asset_id        = (int)$_POST['asset_id'];
        $videogame_title = $_POST['videogame_title'];
        $platform        = $_POST['platform'];
        $price           = $_POST['price'];
        $stock_amount    = $_POST['stock_amount'];
        $used_condition  = $_POST['used_condition'];
        $copy_type       = $_POST['copy_type'];

        $editStmt = $pdo->prepare("
            UPDATE assets
            SET videogame_title = ?, platform = ?, price = ?, stock_amount = ?, used_condition = ?, copy_type = ?
            WHERE asset_id = ?
        ");
        $editStmt->execute([$videogame_title, $platform, $price, $stock_amount, $used_condition, $copy_type, $asset_id]);

        header("Location: admin_dashboard.php");
        exit;
    }
}

// if there is a GET action for removing an asset
if (isset($_GET['action']) && $_GET['action'] === 'remove_asset' && isset($_GET['asset_id'])) {
    $asset_id = (int)$_GET['asset_id'];

    $remStmt = $pdo->prepare("DELETE FROM assets WHERE asset_id = ?");
    $remStmt->execute([$asset_id]);

    header("Location: admin_dashboard.php");
    exit;
}

// fetch list of all assets

$assetsStmt = $pdo->query("SELECT * FROM assets");
$allAssets = $assetsStmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!-- Same styling as index.php -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css1.css" />

    <style>
      .vertical-line {
          position: absolute;
          top: 0;
          left: 2%;
          width: 5px;
          height: 100%;
          background-color: #00FF00;
          z-index: 0;
      }

      .nav, header {
          position: relative;
          z-index: 1;
      }

      table {
        border-collapse: collapse;
        width: 80%;
        margin: 30px auto;
        background-color: #222;
      }
      table, th, td {
        border: 1px solid #555;
      }
      th, td {
        padding: 10px;
        text-align: left;
        color: #fff;
      }
      th {
        background-color: #333;
      }

      h1, h2 {
        text-align: center;
      }

      
      .asset-form {
        width: 60%;
        margin: 20px auto;
        background-color: #333;
        padding: 15px;
        border: 2px solid #00FF00;
      }
      .asset-form label {
        display: block;
        margin: 5px 0;
      }
      .asset-form input, .asset-form select {
        width: 100%;
        margin-bottom: 10px;
      }
      
    </style>
</head>
<body>
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <!-- We can add a link back to index, or admin-specific links here if desired -->
                <a href="index.php">Back to Store</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="blue-line"></div>
    <div class="vertical-line"></div>

    <h1>Welcome, Admin!</h1>

    <h2>Manage Orders</h2>
    <table>
        <tr>
          <th>Order ID</th>
          <th>Username</th>
          <th>Status</th>
          <th>Total</th>
          <th>Actions</th>
        </tr>
        <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= $order['order_id'] ?></td>
            <td><?= htmlspecialchars($order['username']) ?></td>
            <td><?= htmlspecialchars($order['status']) ?></td>
            <td><?= htmlspecialchars($order['total_amount']) ?></td>
            <td>
                <?php if ($order['status'] === 'pending'): ?>
                    <a href="admin_order_action.php?action=ship&order_id=<?= $order['order_id'] ?>">Ship</a> |
                    <a href="admin_order_action.php?action=cancel&order_id=<?= $order['order_id'] ?>">Cancel</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Manage Users</h2>
    <table>
        <tr><th>User ID</th><th>Username</th><th>Email</th><th>Actions</th></tr>
        <?php foreach ($allUsers as $u): ?>
        <tr>
            <td><?= $u['user_id'] ?></td>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <a href="admin_remove_user.php?user_id=<?= $u['user_id'] ?>">Remove</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!--
         Manage Products / Assets
    -->

    <h2>Manage Products</h2>
    <table>
      <tr>
        <th>Asset ID</th>
        <th>Title</th>
        <th>Platform</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Condition</th>
        <th>Copy Type</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($allAssets as $asset): ?>
      <tr>
        <td><?= $asset['asset_id'] ?></td>
        <td><?= htmlspecialchars($asset['videogame_title']) ?></td>
        <td><?= htmlspecialchars($asset['platform']) ?></td>
        <td><?= htmlspecialchars($asset['price']) ?></td>
        <td><?= htmlspecialchars($asset['stock_amount']) ?></td>
        <td><?= htmlspecialchars($asset['used_condition']) ?></td>
        <td><?= htmlspecialchars($asset['copy_type']) ?></td>
        <td>
          
          <a href="admin_dashboard.php?asset_id=<?= $asset['asset_id'] ?>&videogame_title=<?= urlencode($asset['videogame_title']) ?>&platform=<?= urlencode($asset['platform']) ?>&price=<?= $asset['price'] ?>&stock_amount=<?= $asset['stock_amount'] ?>&used_condition=<?= $asset['used_condition'] ?>&copy_type=<?= $asset['copy_type'] ?>#editForm">
            Edit
          </a> |
          <a href="admin_dashboard.php?action=remove_asset&asset_id=<?= $asset['asset_id'] ?>"
             onclick="return confirm('Are you sure you want to remove this product?');">
            Remove
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>

    <!-- form for adding a new product -->

    <div class="asset-form">
      <h3>Add a New Product</h3>
      <form method="post" action="admin_dashboard.php">
        <input type="hidden" name="action" value="add_asset">
        <label>Title:</label>
        <input type="text" name="videogame_title" required>

        <label>Platform:</label>
        <input type="text" name="platform" required>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" required>

        <label>Stock Amount:</label>
        <input type="text" name="stock_amount" required>

        <label>Used Condition (none, poor, medium, good):</label>
        <select name="used_condition">
          <option value="none">none</option>
          <option value="poor">poor</option>
          <option value="medium">medium</option>
          <option value="good">good</option>
        </select>

        <label>Copy Type (physical or digital):</label>
        <select name="copy_type">
          <option value="physical">physical</option>
          <option value="digital">digital</option>
        </select>

        <br><br>
        <input type="submit" value="Add Product">
      </form>
    </div>

    <!-- form for editing a product -->

    <?php if (isset($_GET['asset_id'])): ?>
      <a name="editForm"></a> <!-- anchor to jump here -->
      <div class="asset-form">
        <h3>Edit Product (ID: <?= (int)$_GET['asset_id'] ?>)</h3>
        <form method="post" action="admin_dashboard.php">
          <input type="hidden" name="action" value="edit_asset">
          <input type="hidden" name="asset_id" value="<?= (int)$_GET['asset_id'] ?>">

          <label>Title:</label>
          <input type="text" name="videogame_title" required
                 value="<?= htmlspecialchars($_GET['videogame_title'] ?? '') ?>">

          <label>Platform:</label>
          <input type="text" name="platform" required
                 value="<?= htmlspecialchars($_GET['platform'] ?? '') ?>">

          <label>Price:</label>
          <input type="number" step="0.01" name="price" required
                 value="<?= htmlspecialchars($_GET['price'] ?? '') ?>">

          <label>Stock Amount:</label>
          <input type="text" name="stock_amount" required
                 value="<?= htmlspecialchars($_GET['stock_amount'] ?? '') ?>">

          <label>Used Condition (none, poor, medium, good):</label>
          <?php $currentCond = $_GET['used_condition'] ?? 'none'; ?>
          <select name="used_condition">
            <option value="none"   <?= ($currentCond==='none')?'selected':'' ?>>none</option>
            <option value="poor"   <?= ($currentCond==='poor')?'selected':'' ?>>poor</option>
            <option value="medium" <?= ($currentCond==='medium')?'selected':'' ?>>medium</option>
            <option value="good"   <?= ($currentCond==='good')?'selected':'' ?>>good</option>
          </select>

          <label>Copy Type (physical or digital):</label>
          <?php $currentCopy = $_GET['copy_type'] ?? 'physical'; ?>
          <select name="copy_type">
            <option value="physical" <?= ($currentCopy==='physical')?'selected':'' ?>>physical</option>
            <option value="digital"  <?= ($currentCopy==='digital')?'selected':'' ?>>digital</option>
          </select>

          <br><br>
          <input type="submit" value="Update Product">
        </form>
      </div>
    <?php endif; ?>

</body>
</html>
