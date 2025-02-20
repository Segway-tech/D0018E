

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
<html>
<head>
    <title>Register</title>
</head>
<body>
  <h2>Register</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="post" action="register.php">
    <label>Username:</label><br>
    <input type="text" name="username"><br>
    <label>Email:</label><br>
    <input type="email" name="email"><br>
    <label>Password:</label><br>
    <input type="password" name="password"><br><br>
    <input type="submit" value="Register">
  </form>
  <p>Already registered? <a href="login.php">Login here</a></p>
</body>
</html>
