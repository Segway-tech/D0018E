

<?php
session_start();
require 'db_connect.php';

//HELPER FUNCTION TO CONVERT 1-5 RATING INTO STARS
function getStarString($ratingInt) {
    $ratingInt = max(1, min(5, (int)$ratingInt));
    $filledStar = '★';
    $emptyStar  = '☆';
    $starString = '';
    for ($i=1; $i<=5; $i++) {
        $starString .= ($i <= $ratingInt) ? $filledStar : $emptyStar;
    }
    return $starString;
}

//check if asset id provided
if (!isset($_GET['asset_id'])) {
    die("No asset_id provided.");
}
$asset_id = $_GET['asset_id'];

//fetch asset info
$stmt = $pdo->prepare("SELECT * FROM assets WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$asset = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$asset){
    die("Asset not found.");
}

//fetch reviews for this asset
$stmt = $pdo->prepare("
    SELECT r.review_id, r.rating, r.comment, r.created_at, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.asset_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$asset_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

//calculate average rating + total reviews
$stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE asset_id = ?");
$stmt->execute([$asset_id]);
$ratingData   = $stmt->fetch(PDO::FETCH_ASSOC);
$avgRating    = $ratingData['avg_rating'];
$totalReviews = $ratingData['total_reviews'];

//star string for the average rating too

$roundedAvg = ($avgRating) ? round($avgRating) : 0; // handle no reviews
$avgStarDisplay = ($roundedAvg > 0) ? getStarString($roundedAvg) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Game Detail</title>

    <!-- same fonts and css as index -->
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

      .nav, .center-rectangle, .center-rectangle2, header, .footer, .content-area {
          position: relative;
          z-index: 1;
      }

      .content-area {
        width: 70%;
        margin: 40px auto;
        background-color: #111;
        padding: 20px;
        border: 2px solid #00FF00;
      }

      h1, h3 {
        margin: 0 0 15px 0;
      }

      .review-box {
        border: 1px solid #00FF00;
        margin: 10px 0;
        padding: 10px;
      }
      .review-box strong {
        color: #00FF00;
      }
      .review-box em {
        color: #FFF;
        font-size: 1.2em; 
      }

      form label {
        display: block;
        margin: 10px 0 5px 0;
      }
      form textarea {
        width: 100%;
        max-width: 100%;
        height: 80px;
      }
      /* link back to store */
      .back-link {
        text-align: center;
        margin-top: 20px;
      }
    </style>
</head>
<body>
    <!-- header and nav -->
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <?php
            
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


    <div class="content-area">
        <h1>
          <?= htmlspecialchars($asset['videogame_title']) ?>
          (<?= htmlspecialchars($asset['platform']) ?>)
        </h1>
        <p>Price: <?= htmlspecialchars($asset['price']) ?></p>
        <p>Stock: <?= htmlspecialchars($asset['stock_amount']) ?></p>

        <?php if ($totalReviews > 0): ?>
            <!--STARS FOR AVERAGE RATING -->
            <h3>Average Rating: <em><?= $avgStarDisplay ?></em> (<?= $totalReviews ?> reviews)</h3>
        <?php else: ?>
            <h3>No reviews yet.</h3>
        <?php endif; ?>

        <!-- show reviews -->
        <?php foreach ($reviews as $rev): ?>
            <?php
            //STARS FOR INDIVIDUAL REVIEW
            $revStarString = getStarString($rev['rating']);
            ?>
            <div class="review-box">
                <strong><?= htmlspecialchars($rev['username']) ?></strong><br>
                <em><?= $revStarString ?></em><br>
                <?= nl2br(htmlspecialchars($rev['comment'])) ?><br>
                <small>Posted on <?= $rev['created_at'] ?></small>
            </div>
        <?php endforeach; ?>

        <!--add review form only if logged in -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <h3>Leave a Review</h3>
          <form method="post" action="add_review.php">
              <input type="hidden" name="asset_id" value="<?= $asset_id ?>">
              
	      <label>Rating:</label>
		<div class="star-rating">
  		<input type="radio" id="star5" name="rating" value="5" required /><label for="star5">★</label>
  		<input type="radio" id="star4" name="rating" value="4" /><label for="star4">★</label>
  		<input type="radio" id="star3" name="rating" value="3" /><label for="star3">★</label>
  		<input type="radio" id="star2" name="rating" value="2" /><label for="star2">★</label>
  		<input type="radio" id="star1" name="rating" value="1" /><label for="star1">★</label>
		</div>
		
              <label>Comment:</label>
              <textarea name="comment"></textarea>
              <br><br>
              <button type="submit">Submit Review</button>
          </form>
        <?php else: ?>
            <p><a href="login.php">Login</a> to leave a review.</p>
        <?php endif; ?>

        <!--back to store -->
        <div class="back-link">
            <p><a href="index.php">← Back to Store</a></p>
        </div>
    </div>

    <!-- footer -->
    <div class="footer">
        <p>&copy; <?= date('Y') ?> Very Cool Videogame E-shop!</p>
    </div>
</body>
</html>
