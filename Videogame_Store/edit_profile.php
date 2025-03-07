


<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// if form is submitted, update user data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $address  = trim($_POST['address']);
    $phone    = trim($_POST['phone']);
    $newPassword = $_POST['new_password'];

    if (!empty($newPassword)) {
        // hash new password
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username=?, email=?, address=?, phone=?, password=?
            WHERE user_id=?
        ");
        $stmt->execute([$username, $email, $address, $phone, $hashed, $user_id]);
    } else {
        // don't change password
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username=?, email=?, address=?, phone=?
            WHERE user_id=?
        ");
        $stmt->execute([$username, $email, $address, $phone, $user_id]);
    }
    header("Location: edit_profile.php"); // reload
    exit;
}

// otherwise fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>

    <!--same styling as index -->
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

      .content-area {
        width: 70%;
        margin: 40px auto;
        background-color: #111;
        padding: 20px;
        border: 2px solid #00FF00;
      }

      label {
        display: block;
        margin: 15px 0 5px 0;
      }

      input[type="text"], input[type="email"], input[type="password"] {
        width: 60%;
        padding: 5px;
        margin-bottom: 10px;
      }

      input[type="submit"] {
        margin-top: 10px;
      }

      h1 {
        text-align: center;
      }
    </style>
</head>
<body>
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
        <div class="nav">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
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

    <div class="content-area">
      <h1>Edit Profile</h1>

      <form method="post" action="">
          <label>Username:</label>
          <input type="text" name="username" 
                 value="<?= htmlspecialchars($user['username']) ?>">

          <label>Email:</label>
          <input type="email" name="email" 
                 value="<?= htmlspecialchars($user['email']) ?>">

          <label>Address:</label>
          <input type="text" name="address" 
                 value="<?= htmlspecialchars($user['address']) ?>">

          <label>Phone:</label>
          <input type="text" name="phone" 
                 value="<?= htmlspecialchars($user['phone']) ?>">

          <label>New Password (leave blank to keep current):</label>
          <input type="password" name="new_password">

          <input type="submit" value="Update Profile">
      </form>
    </div>
</body>
</html>
