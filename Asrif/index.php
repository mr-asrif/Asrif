<?php
require_once 'conndb.php';

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asrif Hardware Store</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <span class="logo-icon">🛠️</span>
                <h1>Asrif Hardware Store</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#products">Products</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="account.php">My Account</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" id="loginBtn">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <style>
        body {
          background-image: url(img/store.jpeg);
          background-size: cover;
          background-repeat: no-repeat;
          background-attachment: fixed;
          background-position: center;
        }
        
        body::before {
          content: "";
          position: fixed;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: rgba(37, 40, 40, 0.7);
          z-index: -1;
        }
        
        main {
          position: relative;
        }
      </style>
      
    <section id="home" class="hero">
        <div class="container hero-content">
            <h2>Your One-Stop Shop for Quality Tools</h2>
            <p>At Asrif Hardware Store, we provide the best tools and equipment for professionals and DIY enthusiasts. From hammers to power drills, we've got everything you need to get the job done right.</p>
            <a href="#products" class="btn">Shop Now</a>
        </div>
    </section>
    
    <section id="products">
        <div class="container">
            <h2 class="section-heading">Visit Our Products</h2>
            <div class="products-grid">
            <?php
            // Error reporting for debugging (remove in production)
            error_reporting(E_ALL);
            ini_set('display_errors', 1);

            // Fetch products from database
            try {
                // Verify database connection
                if (!$conn) {
                    throw new Exception("Database connection failed");
                }

                $sql = "SELECT id, name, price, description, image_path FROM products";
                $result = $conn->query($sql);
                
                if (!$result) {
                    throw new Exception("Query failed: " . $conn->error);
                }
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Validate product data
                        if (empty($row['id']) || !is_numeric($row['id'])) {
                            error_log("Invalid product ID: " . $row['id']);
                            continue; // Skip this product
                        }
                        
                        echo '<div class="product">';
                        
                        // Sanitize image path
                        $image_path = htmlspecialchars($row['image_path'] ?? 'img/default-product.jpg');
                        echo '<img src="' . $image_path . '" alt="' . htmlspecialchars($row['name']) . '">';
                        
                        echo '<div class="product-info">';
                        echo '<h3 class="product-title">' . htmlspecialchars($row['name']) . '</h3>';
                        
                        // Format price safely
                        $price = is_numeric($row['price']) ? $row['price'] : 0;
                        echo '<p class="product-price">LKR' . number_format($price, 2) . '</p>';
                        
                        echo '<p class="product-description">' . htmlspecialchars($row['description']) . '</p>';
                        
                        // Form for adding to cart (better than link)
                        if(isset($_SESSION['user_id'])) {
                            echo '<form action="add_to_cart.php" method="post">';
                            echo '<input type="hidden" name="product_id" value="' . (int)$row['id'] . '">';
                            echo '<input type="number" name="quantity" value="1" min="1" max="10">';
                            echo '<button type="submit" class="btn">Add to Cart</button>';
                            echo '</form>';
                        } else {
                            echo '<a href="login.php" class="btn">Login to Purchase</a>';
                        }
                        
                        echo '</div></div>';
                    }
                } else {
                    echo "<p>No products available at the moment.</p>";
                }
            } catch (Exception $e) {
                error_log("Error displaying products: " . $e->getMessage());
                echo "<p>Error loading products. Please try again later.</p>";
                
                // For debugging only (remove in production)
                echo "<!-- Error: " . htmlspecialchars($e->getMessage()) . " -->";
            }

            // Don't close connection here if you need it elsewhere on the page
            ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="products.php" class="btn">View All Products</a>
            </div>
        </div>
    </section>
   
    <section id="contact">
        <div class="container">
            <h2 class="section-heading">Contact Us</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Visit Us</h3>
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-text">
                            <strong>Address</strong>
                            97 karuwattukal 03 sammanthurai, Srilanka
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-text">
                            <strong>Phone</strong>
                            +94 763541462
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉</div>
                        <div class="contact-text">
                            <strong>Email</strong>
                            mohammedasrif41@gmail.com
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h3>Send Us a Message</h3>
                    <form method="post" action="process_contact.php">
                        <div class="form-group">
                            <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                        </div>
                        
                        <div class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        
                        <div class="form-group">
                            <textarea name="message" class="form-control" placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>Asrif Hardware</h3>
                    <p>Your trusted partner for quality tools and hardware solutions.</p>
                    <div class="social-links">
                        <a href="#" class="social-icon">Facebook</a>
                        <a href="#" class="social-icon">Twitter</a>
                        <a href="#" class="social-icon">Instagram</a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#products">Products</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Newsletter</h3>
                    <p>Subscribe to our newsletter for updates and special offers.</p>
                    <form method="post" action="subscribe.php" class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                        <button type="submit" class="btn">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date("Y"); ?> Asrif Hardware Store. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
    
</body>
</html>
<?php
$conn->close();
?>