

<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    //get user by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        //set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        //store role in the session for admin checks
        $_SESSION['role'] = $user['role'];

        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Very Cool Videogame E-shop</title>
    <!-- same fonts and css as in index -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css1.css" />
</head>
<body>
    <header>
        <h1>Very Cool Videogame E-shop!</h1>
    </header>

    <div class="blue-line"></div>
    
    <div class="vertical-line"></div> 

    <h2 style="text-align:center;">Login</h2>
    <?php if (isset($error)): ?>
      <p style='color:red; text-align:center;'><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php" style="text-align:center; margin:20px;">
      <label>Username:</label><br>
      <input type="text" name="username"><br><br>
      <label>Password:</label><br>
      <input type="password" name="password"><br><br>
      <input type="submit" value="Login">
    </form>

    <p style="text-align:center;">
      Don't have an account? <a href="register.php">Register here</a>
    </p>
</body>
</html>
