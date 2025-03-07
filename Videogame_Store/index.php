




<?php
session_start();


//to show stars in latest reviews on front page
function getStarString($ratingInt) {
    $ratingInt = max(1, min(5, (int)$ratingInt));
    $filled = '★';
    $empty  = '☆';
    $stars = '';
    for ($i=1; $i<=5; $i++) {
        $stars .= ($i <= $ratingInt) ? $filled : $empty;
    }
    return $stars;
}




// database connection
$host = 'localhost';
$db   = 'videogames_db';
$user = 'videogames_user';
$pass = 'VideoGamesHaha123!';

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    // PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // dynamic query for searching/filtering
    $sql = "SELECT * FROM assets";
    $whereClauses = [];
    $params = [];

    if (!empty($_GET['q'])) {
        $searchTerm = "%".$_GET['q']."%";
        $whereClauses[] = "(videogame_title LIKE ? OR platform LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if (!empty($_GET['platform'])) {
        $whereClauses[] = "platform = ?";
        $params[] = $_GET['platform'];
    }

    if (!empty($_GET['min_price'])) {
        $whereClauses[] = "price >= ?";
        $params[] = $_GET['min_price'];
    }

    if (!empty($_GET['max_price'])) {
        $whereClauses[] = "price <= ?";
        $params[] = $_GET['max_price'];
    }

    if ($whereClauses) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // fetch latest reviews (limit to 5)
    $stmtReviews = $pdo->query("
        SELECT r.rating, r.comment, r.created_at, u.username, a.videogame_title
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        JOIN assets a ON r.asset_id = a.asset_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $latestReviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "database error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- basic meta -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spel E-Shop</title>

    <!-- google fonts: "Press Start 2P" -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="css1.css" />

    <!-- styling -->
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

      .nav, .search-form, .center-rectangle, .center-rectangle2, header, table, .footer, .latest-reviews {
          position: relative;
          z-index: 1;
      }

      /* glow effect on links */
      a:hover {
          text-shadow: 0 0 5px #00FF00;
      }

      /* retro buttons */
      input[type="submit"], button {
          font-family: 'Press Start 2P', monospace;
          background-color: #000;
          color: #00FF00;
          border: 2px solid #00FF00;
          padding: 5px;
          text-transform: uppercase;
      }

      input[type="submit"]:hover, button:hover {
          text-shadow: 0 0 5px #00FF00;
          cursor: pointer;
      }

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

      .latest-reviews {
        width: 70%;
        margin: 40px auto;
        background-color: #111;
        padding: 10px;
        border: 2px solid #00FF00;
      }
      .latest-reviews h3 {
        margin: 0 0 10px 0;
        text-align: center;
      }
      .latest-reviews ul {
        list-style: none;
        padding: 0;
      }
      .latest-reviews li {
        margin-bottom: 15px;
        color: #00FF00;
      }
      .latest-reviews li span {
        margin-left: 5px;
        color: #fff;
      }
    </style>
</head>
<body>
    <!-- header/navigation -->
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <?php
                    // query total items in shopping cart
                    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $cartCount = $stmt->fetchColumn();
                    if (!$cartCount) {
                        $cartCount = 0;
                    }
                ?>
                <a href="view_cart.php">🛒 View Cart (<?= $cartCount ?>)</a>

                <!-- show admin link if user is admin -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <a href="admin_dashboard.php">Admin Dashboard</a>
                <?php endif; ?>

                <!-- Provide a link to edit profile & my orders -->
                <a href="edit_profile.php">Edit Profile</a>
                <a href="my_orders.php">My Orders</a>

                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="blue-line"></div>
    <div class="vertical-line"></div>
    <!-- remain commented out so they don't show extra dots/pixels:
    <div class="center-rectangle"></div>
    <div class="center-rectangle2"></div>
    -->

    <!-- search/filter form -->
    <div class="search-form">
        <h2>Search/Filter Games Here</h2>
        <form method="get" action="index.php">
            <input type="text" name="q" placeholder="Search title or platform..."
                   value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

            <select name="platform">
                <option value="">All Platforms</option>
                <option value="PC"   <?= (($_GET['platform'] ?? '') === 'PC') ? 'selected' : '' ?>>PC</option>
                <option value="PS4"  <?= (($_GET['platform'] ?? '') === 'PS4') ? 'selected' : '' ?>>PS4</option>
                <option value="Xbox" <?= (($_GET['platform'] ?? '') === 'Xbox') ? 'selected' : '' ?>>Xbox</option>
                <option value="N64"  <?= (($_GET['platform'] ?? '') === 'N64') ? 'selected' : '' ?>>N64</option>
            </select>

            <input type="number" name="min_price" placeholder="Min Price"
                   value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
            <input type="number" name="max_price" placeholder="Max Price"
                   value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">

            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- game listing -->
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
                <!-- make title clickable -->
                <td>
                  <a href="asset_detail.php?asset_id=<?= $asset['asset_id'] ?>">
                    <?= htmlspecialchars($asset['videogame_title']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($asset['platform']) ?></td>
                <td><?= htmlspecialchars($asset['price']) ?></td>
                <td><?= htmlspecialchars($asset['stock_amount']) ?></td>

                <!--dont display refurbished status on new games -->
                <?php if ($asset['used_condition'] !== 'none'): ?>
                    <td><?= htmlspecialchars($asset['used_condition']) ?></td>
                <?php else: ?>
                    <td></td>
                <?php endif; ?>

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

    <!-- latest reviews -->
    <div class="latest-reviews">
      <h3>Latest Reviews</h3>
      <?php if (!empty($latestReviews)): ?>
        <ul>
          <?php foreach ($latestReviews as $rev): ?>

	    <?php $starDisplay = getStarString($rev['rating']); ?>
            <li>
              <strong><?= htmlspecialchars($rev['username']) ?></strong>
              rated
              <em><?= htmlspecialchars($rev['videogame_title']) ?></em>

              <span>(<?= $starDisplay ?>)</span><br>

              "<?= nl2br(htmlspecialchars($rev['comment'])) ?>"
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="text-align:center;">No reviews yet.</p>
      <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; <?= date('Y') ?> Very Cool Videogame E-shop!</p>
    </div>
</body>
</html>
