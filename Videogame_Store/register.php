

<?php
//start session, include database connection
session_start();
require 'db_connect.php'; // Create a separate file for connecting to MySQL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //get and validate inputs
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    
    //validation    
	if (!empty($username) && !empty($email) && !empty($password)) {
        //hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        //prepare SQL statement
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashedPassword])) {
            header("Location: login.php"); //redirect after success
            exit;
        } else {
            $error = "Registration failed. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Very Cool Videogame E-shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css1.css">
</head>
<body>
    <header>
      <h1>Very Cool Videogame E-shop!</h1>
    </header>
    <div class="blue-line"></div>

    <h2 style="text-align:center;">Register</h2>
    <?php if (isset($error)): ?>
      <p style='color:red; text-align:center;'><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" action="register.php" style="text-align:center; margin:20px;">
      <label>Username:</label><br>
      <input type="text" name="username"><br><br>
      <label>Email:</label><br>
      <input type="email" name="email"><br><br>
      <label>Password:</label><br>
      <input type="password" name="password"><br><br>
      <input type="submit" value="Register">
    </form>
    <p style="text-align:center;">
      Already registered? <a href="login.php">Login here</a>
    </p>
</body>
</html>
