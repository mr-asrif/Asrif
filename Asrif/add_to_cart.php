<?php
session_start();
require 'conndb.php';

// At the top of add_to_cart.php
$product_id = $_SERVER['REQUEST_METHOD'] === 'POST' 
    ? ($_POST['product_id'] ?? 0) 
    : ($_GET['id'] ?? 0);

$quantity = $_SERVER['REQUEST_METHOD'] === 'POST' 
    ? ($_POST['quantity'] ?? 1) 
    : 1; // Default quantity for GET

    
// Problem: Redirects without proper conditions
header("Location: products.php"); 
// Should have exit() after and proper validation

// Solution:
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // ← THIS IS CRUCIAL
}

if(isset($_SESSION['user_id'])) {
    echo '<form action="add_to_cart.php" method="POST">';
    echo '<input type="hidden" name="product_id" value="' . $row['id'] . '">';
    echo '<input type="hidden" name="quantity" value="1">'; // Default quantity
    echo '<button type="submit" class="btn">Add to Cart</button>';
    echo '</form>';
} else {
    echo '<a href="login.php" class="btn">Login to Purchase</a>';
}

// Validate inputs
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 10]
]);

if (!$product_id || !$quantity) {
    $_SESSION['error'] = "Invalid product or quantity";
    header("Location: add_to_cart.php");
    exit();
}

try {
    // Verify product exists and get price
    $stmt = $conn->prepare("SELECT id, price, stock_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        $_SESSION['error'] = "Product not found";
        header("Location: add_to_cart.php");
        exit();
    }

    // Check stock availability
    if ($product['stock_quantity'] < $quantity) {
        $_SESSION['error'] = "Only {$product['stock_quantity']} items available";
        header("Location: add_to_cart.php");
        exit();
    }

    // Check if item already in cart
    $check_stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $check_stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $check_stmt->execute();
    $existing_item = $check_stmt->get_result()->fetch_assoc();

    if ($existing_item) {
        // Update existing cart item
        $new_quantity = $existing_item['quantity'] + $quantity;
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $existing_item['id']);
        $update_stmt->execute();
    } else {
        // Add new item to cart
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("iiid", $_SESSION['user_id'], $product_id, $quantity, $product['price']);
        $insert_stmt->execute();
    }

    $_SESSION['success'] = "Product added to cart successfully";
    header("Location: cart.php");
    exit();

} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    $_SESSION['error'] = "Error adding to cart. Please try again.";
    header("Location: add_to_cart.php");
    exit();
}
?>