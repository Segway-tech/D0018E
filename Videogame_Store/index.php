<?php
session_start();

//database connection
$host = 'localhost';
$db   = 'videogames_db';
$user = 'videogames_user';
$pass = 'VideoGamesHaha123!';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    //PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //get all rows from assets table
    $stmt = $pdo->query("SELECT * FROM assets");
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic Meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Title -->
    <title>Spel E-Shop</title>
    <!-- Google Fonts: "Press Start 2P" -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <!-- External CSS (css1.css) -->
    <link rel="stylesheet" type="text/css" href="css1.css" />
    <!-- Additional styling for the table and navigation -->
    <style>
      table {
        border-collapse: collapse;
        width: 70%;
        margin: 40px auto;
        background-color: #222;
      }
      table, th, td {
        border: 1px solid #555;
      }
      th, td {
        padding: 10px;
        text-align: left;
      }
      th {
        background-color: #333;
        color: #fff;
      }
      td {
        color: #fff;
      }
      .footer {
        text-align: center;
        margin-top: 40px;
      }
      .nav {
          text-align: right;
          margin: 10px 40px;
      }
      .nav a {
          color: #fff;
          text-decoration: none;
          margin-left: 10px;
      }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <?php
                    //query items in shopping cart
                    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $cartCount = $stmt->fetchColumn();
                    if (!$cartCount) { 
                        $cartCount = 0; 
                    }
                ?>
                <a href="view_cart.php">🛒 View Cart (<?= $cartCount ?>)</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </header>
    <div class="blue-line"></div>
    <div class="vertical-line"></div>
    <div class="center-rectangle"></div>
    <div class="center-rectangle2"></div>
    <!-- Main Content -->
    <h2 style="text-align:center; margin-top: 40px;">List of Current Games</h2>
    <?php if (!empty($assets)): ?>
        <table>
            <tr>
                <th>Title</th>
                <th>Platform</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Refurbished?</th>
                <th>Copy Type</th>
                <th>Action</th>
            </tr>
            <?php foreach ($assets as $asset): ?>
            <tr>
                <td><?= htmlspecialchars($asset['videogame_title']) ?></td>
                <td><?= htmlspecialchars($asset['platform']) ?></td>
                <td><?= htmlspecialchars($asset['price']) ?></td>
                <td><?= htmlspecialchars($asset['stock_amount']) ?></td>
                <td><?= htmlspecialchars($asset['used_condition']) ?></td>
                <td><?= htmlspecialchars($asset['copy_type']) ?></td>
                <td>
                    <form action="add_to_cart.php" method="post">
                        <input type="hidden" name="asset_id" value="<?= $asset['asset_id'] ?>">
                        <input type="submit" value="Add to Cart">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No items found in the database.</p>
    <?php endif; ?>
    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?= date('Y') ?> Very Cool Videogame E-shop!</p>
    </div>
</body>
</html>
