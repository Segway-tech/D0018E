

<?php
session_start();
require 'db_connect.php';

//make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $asset_id = $_POST['asset_id'];

    //fetch current stock from assets table for this specific asset
    $stockStmt = $pdo->prepare("SELECT stock_amount FROM assets WHERE asset_id = ?");
    $stockStmt->execute([$asset_id]);
    $stockRow = $stockStmt->fetch(PDO::FETCH_ASSOC);

    if (!$stockRow) {
        // if no asset is found, abort
        die("Error: Asset not found or invalid asset_id.");
    }

    //convert stock_amount to int if stored as string
    $currentStock = (int)$stockRow['stock_amount'];

    if ($currentStock <= 0) {
        // if no stock left at all we cant add to cart
       
        die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
          <meta charset='UTF-8'>
          <title>Very Cool Videogame E-shop - Error</title>
          <link rel='stylesheet' type='text/css' href='css1.css'>
          <style>
            .center-error {
              text-align: center;
              margin-top: 60px;
              color: #fff;
              font-family: 'Press Start 2P', monospace;
            }
            .error-button {
              display: inline-block;
              margin-top: 20px;
              padding: 10px 20px;
              border: 2px solid #00FF00;
              background-color: #000;
              color: #00FF00;
              text-decoration: none;
              text-transform: uppercase;
            }
            .error-button:hover {
              text-shadow: 0 0 5px #00FF00;
            }
          </style>
        </head>
        <body>
          <div class='center-error'>
            <h1>Sorry, this product is out of stock!</h1>
            <p>Please choose another product or come back later.</p>
            <a href='index.php' class='error-button'>Back to Store</a>
          </div>
        </body>
        </html>");
        
    }

    //check if asset already in cart
    $stmt = $pdo->prepare("SELECT * FROM shopping_cart WHERE user_id = ? AND asset_id = ?");
    $stmt->execute([$user_id, $asset_id]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        $newQuantity = $cartItem['quantity'] + 1;
        // make sure newQuantity does not exceed currentStock
        if ($newQuantity > $currentStock) {
            //error page
            die("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
              <meta charset='UTF-8'>
              <title>Very Cool Videogame E-shop - Error</title>
              <link rel='stylesheet' type='text/css' href='css1.css'>
              <style>
                .center-error {
                  text-align: center;
                  margin-top: 60px;
                  color: #fff;
                  font-family: 'Press Start 2P', monospace;
                }
                .error-button {
                  display: inline-block;
                  margin-top: 20px;
                  padding: 10px 20px;
                  border: 2px solid #00FF00;
                  background-color: #000;
                  color: #00FF00;
                  text-decoration: none;
                  text-transform: uppercase;
                }
                .error-button:hover {
                  text-shadow: 0 0 5px #00FF00;
                }
              </style>
            </head>
            <body>
              <div class='center-error'>
                <h1>Cannot add more of this item!</h1>
                <p>Please check availability or remove some from your cart.</p>
                <a href='index.php' class='error-button'>Back to Store</a>
              </div>
            </body>
            </html>");
            
        }

        // update cart row
        $stmt = $pdo->prepare("UPDATE shopping_cart SET quantity = ? WHERE cart_id = ?");
        $stmt->execute([$newQuantity, $cartItem['cart_id']]);

    } else {
        // if its not in the cart add one item
        if ($currentStock <= 0) {
            //ERROR PAGE
            die("
            <!DOCTYPE html>
            <html lang='en'>
            <head>
              <meta charset='UTF-8'>
              <title>Very Cool Videogame E-shop - Error</title>
              <link rel='stylesheet' type='text/css' href='css1.css'>
              <style>
                .center-error {
                  text-align: center;
                  margin-top: 60px;
                  color: #fff;
                  font-family: 'Press Start 2P', monospace;
                }
                .error-button {
                  display: inline-block;
                  margin-top: 20px;
                  padding: 10px 20px;
                  border: 2px solid #00FF00;
                  background-color: #000;
                  color: #00FF00;
                  text-decoration: none;
                  text-transform: uppercase;
                }
                .error-button:hover {
                  text-shadow: 0 0 5px #00FF00;
                }
              </style>
            </head>
            <body>
              <div class='center-error'>
                <h1>Sorry, not enough stock to add this item.</h1>
                <p>Please choose another product or come back later.</p>
                <a href='index.php' class='error-button'>Back to Store</a>
              </div>
            </body>
            </html>");
            
        }

        //insert new cart item with quantity = 1
        $stmt = $pdo->prepare("INSERT INTO shopping_cart (user_id, asset_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $asset_id]);
    }
}

//redirect back to the store page
header("Location: index.php");
exit;
