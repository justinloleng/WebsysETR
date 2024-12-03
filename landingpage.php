<?php
require("0conn.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$fetchTopMealsStmt = $pdo->prepare("SELECT * FROM meals ORDER BY views DESC LIMIT 3");
$fetchTopMealsStmt->execute();
$topMeals = $fetchTopMealsStmt->fetchAll(PDO::FETCH_ASSOC);

$fetchRecentTestimoniesStmt = $pdo->prepare("SELECT * FROM testimonies ORDER BY date_posted DESC LIMIT 5");
$fetchRecentTestimoniesStmt->execute();
$recentTestimonies = $fetchRecentTestimoniesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Website Name</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .logo-container {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            padding: 20px;
            width: auto;
            margin-right: 10px;
        }

        .logo h1 {
            font-family: cursive;
            font-size: 24px;
            margin: 0;
            color: #16b978;
        }

        .topnav {
            background-color: rgba(22, 185, 120, 0.9);
            background-color: #16b978;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            padding-top: 90px;
            transition: top 0.3s;
            margin-bottom: 120px;
        }

        .topnav a {
            float: center;
            color: #f2f2f2;
            text-align: center;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 17px;
            display: flex;
            align-items: center;
        }
        .topnav a i {
            margin-right: 30px;
        }
        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        .topnav a.active {
            background-color: #04AA6D;
            color: white;
        }

        .container {
            display: flex;
            width: 100%;
            border-radius: 20px;
            background-color: #DAF2E8; /* Updated background color */
            position: relative;
            overflow: hidden; /* Updated to hide scroll bars */
            margin-top: 140px;
            transition: margin-top 0.3s; /* Add transition for smooth effect */
        }

        .left-panel {
            flex: 0.8; /* Reduced flex value for width */
            padding: 17px; /* Reduced padding */
            background-color: #DAF2E8; /* Updated background color */
            color: #18B877; /* Updated text color */
            overflow: hidden; /* Updated to hide scroll bars */
            position: relative;
            padding:20px ;
        }

        .left-panel h2 {
            font-size: 45px; /* Adjusted font size */
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Changed font family */
            font-weight: bold;
            color: #18B877;
            margin-bottom: 10px; /* Increased margin-bottom for spacing */
        }
        .left-panel p {
            text-align: left;
            color: black; /* Updated text color */
        }

        .dish-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            width: 100%; /* Make sure it takes 100% width inside the container */
        }

        .dish {
            text-align: center;
            flex-basis: 30%; /* Adjusted flex basis */
            position: relative;
        }

        .dish img {
            width: 90%;
            height: 170px; /* Updated height */
            object-fit: cover;
            border-radius: 10px;
        }

        .dish-name {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .favorite-icon {
            position: absolute;
            top: 5px;
            right: 5px;
            color: #ff6347; /* Tomato color for heart */
            font-size: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Added box shadow */
            background-color: white;
            border-radius: 20px;
            padding: 5px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .right-panel {
            flex: 0.7;
            background: url('vegetable.png');
            background-size: cover;
            background-position: center;
            padding: 20px;
            flex-direction: column;
            align-items: center;
            overflow: hidden; /* Updated to hide scroll bars */
        }

        .breakfast-section {
            text-align: center;
            margin-top: 20px;
        }

        .dish-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
        }

        .dish-name {
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 5px;
            color: black;
        }

        .meal-container {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
        }

        .meal-card {
            position: relative; /* Added position relative */
            width: calc(33.333% - 10px); /* Adjusted width and added margin */
            text-align: center;
            background-color: #fff;
            margin-bottom: 1em;
            border-radius: 10px;
            margin-right: 25px; /* Add margin to create space between cards */
            cursor: pointer; 
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1); /* Added box shadow */
        }

        .meal-card .favorite-icon {
            position: absolute;
            top: 10px;
            margin-left: 20px;
            color: #ff6347; /* Tomato color for heart */
            font-size: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Added box shadow */
            background-color: white;
            border-radius: 20px;
            padding: 5px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .meal-card img {
            object-fit: cover;
            width: 100%;
            height: 230px; /* Updated height */
            object-fit: cover;
            border-radius: 10px;
        }

        .meal-card h3 {
            font-size: 18px;
            color: #16b978; /* Adjusted text color */
            margin-top: 10px;
        }

        .meal-card .views {
            color: gray;
            font-size: 15px;
        }

        .meal-card .meal-description {
            margin-top: 1em;
            color: black;
        }

        .meal-card button {
            background-color: #16b978; /* Adjusted button color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .meal-card:hover {
            background-color: #ff6347; /* Change the background color on hover */
        }

        .meal-card:hover h3,
        .meal-card:hover .views,
        .meal-card:hover .meal-description {
            color: white; /* Change the text color to white on hover */
        }
        .views {
            color: gray;
            font-size: 15px;
        }
        .testimonial-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 2em;
        }
        .testimonial-card p {
            margin-right: 20px; /* Adjust the margin-top value as needed */
            margin-left: 20px; /* Adjust the margin-bottom value as needed */
        }

        .testimonial-card {
            color: black;
            flex: 0 0 calc(50% - 20px);
            border: 1px solid #D6EDE3;
            padding: 10px;
            margin-bottom: 1em;
            background-color: #D6EDE3;
            border-radius: 15px;
            box-sizing: border-box; /* Add this line to fix width calculation */
        }
        .get-started-button {       
            display: inline-block;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.9); /* Adjusted alpha value for transparency */
            color:#16b978;
            text-decoration: none;
            border-radius: 50px;
            margin-top: 1em;
            text-align: left;
            margin: 10px;
            padding-left: 35px;
            padding-right: 35px;
            padding-top: 18px;
            padding-bottom: 18px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1), 0 0 8px rgba(0, 0, 0, 0.1); /* Added box shadow */
        }

        h3 {
            font-size: 17px;
            color: #16b978;
            margin-top: 1em;
        }

        section {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Hide Navbar on Scroll Down */
        .container.hide-navbar {
            margin-top: 0;
        }
        .hot-recipes-button {
            background-color: white; /* Fire color */
            color: #ff6347;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Added box shadow */
            margin-bottom: 20px; /* Added margin to separate button from the image */
            margin-left: 520px;
        }

        .hot-recipes-button i {
            margin-right: 10px;
        }
        h4{
            font-size: 14px;
            font-weight: 100px;
        }
        
        section {
            max-width: 1200px;
            margin: 0 auto;
        }


        h2 {
            margin-top: 40px;
            font-size: 28px;
            color: #16b978;
            margin-bottom: 20px;
        }
        .most-viewed-section h2 {
        font-size: 24px;
        color: #16b978;
        margin-bottom: 10px;
    }

    .most-viewed-section p {
        font-size: 16px;
        color: #555;
    }
        .about-container {
            margin-right: 150px;
            top:150%;
            background-color: white;
            margin-top: 150px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

            .about-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .about-image img {
                        width: 150%;
                        height: 450px; /* Updated height */
                        object-fit: cover;
                        border-radius: 10px;
            }

            .about-text {
                flex: 1;
                margin-left: 20px;
            }

            .about-text h2 {
                text-align: left;
                margin-left: 20px;
                margin-right: 20px;
            }   
            .about-text p {
                text-align: left;
                margin-left: 20px;
                margin-right: 20px;
            }  
            .faq{
            padding: 20px;
            margin-top: 190px;
            margin-bottom: 90px;

        }
        .contact-container {
            width: 100vw; /* Set the width to 100% of the viewport width */
            background-color: #D6EDE3;
            margin-bottom: 70px;
            padding-left: 100px;
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .contact-container h3 {
            color: black;
        }
</style>
</head>

<body>
    <div class="logo-container">
        <div class="logo">
            <img src="logo.png" alt="Tastebud Logo">
            <h1>Tastebud</h1>
        </div>
    </div>
    <div class="container">
    <div class="topnav">
    <a href="#home"<?php echo (basename($_SERVER['PHP_SELF']) == '9customer.php') ? 'class="active"' : ''; ?>>
        <i class="fa fa-fw fa-home"></i>Home</a>

    <a href="#about"<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'class="active"' : ''; ?>>
        <i class="fa fa-fw fa-info-circle"></i>About</a>

    <a href="#faqs"<?php echo (basename($_SERVER['PHP_SELF']) == 'faqs.php') ? 'class="active"' : ''; ?>>
        <i class="fa fa-fw fa-question"></i>FAQs</a>

    <a href="#contact"<?php echo (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'class="active"' : ''; ?>>
        <i class="fa fa-fw fa-envelope"></i>Contact</a>

    <?php if (isset($_SESSION['username'])) : ?>
        <!-- ... Your existing navbar links ... -->
    <?php else : ?>
        <a href="3login.php"<?php echo (basename($_SERVER['PHP_SELF']) == '3login.php') ? 'class="active"' : ''; ?>>
            <i class="fa fa-fw fa-sign-in-alt"></i>Login</a>
    <?php endif; ?>
</div>

        <div class="left-panel">
        <h2>SOMETHING HEALTHY,   SOMETHING TASTY</h2>
            <p>Savor the extraordinary at Tastebud, where recipes come to life with a symphony of flavors. Uncover culinary wonders and immerse yourself in the tales of our satisfied customers, sharing their love for our delectable creations.</p>
            <h4><a href="1registration.php" class="get-started-button">Get Started</a></h4>
            <h3>Breakfast</h3>
            <div class="dish-section">
                <div class="dish">
                    <img src="image1.jpg" alt="Dish 1" class="dish-image">
                    <div class="favorite-icon"><i class="fas fa-heart"></i></div>
                    <div class="dish-name">Avocado Veggie Egg Bliss Bowl</div>
                </div>
                <div class="dish">
                    <img src="image2.jpeg" alt="Dish 2" class="dish-image">
                    <div class="favorite-icon"><i class="fas fa-heart"></i></div>
                    <div class="dish-name">Tomato Egg Fiesta</div>
                </div>
                <div class="dish">
                    <img src="image3.jpg" alt="Dish 3" class="dish-image">
                    <div class="favorite-icon"><i class="fas fa-heart"></i></div>
                    <div class="dish-name">Classic Egg & Waffle Delight</div>
                </div>
            </div>
        </div>
        <div class="right-panel">
        <button class="hot-recipes-button">
            <i class="fas fa-fire"></i>Hot Recipes
        </button>
        </div>
    </div>
    <section>
    <h1 style="text-align: center; margin-top: 130px;">MOST VIEWED DISHES</h1>
<p style="text-align: center;">Explore the most popular recipes loved by our community.</p>

    <div class="meal-container">
    <?php foreach ($topMeals as $meal): ?>
        <div class="meal-card" onclick="window.location='1registration.php';">
            <?php
                $fetchImagesStmt = $pdo->prepare("SELECT * FROM meal_images WHERE meal_id = ? LIMIT 1");
                $fetchImagesStmt->execute([$meal['meal_id']]);
                $image = $fetchImagesStmt->fetch(PDO::FETCH_ASSOC);
            ?>

            <?php if ($image): ?>
                <div class="favorite-icon"><i class="fas fa-heart"></i></div>
                <img src="<?php echo $image['image_link']; ?>" alt="Meal Image">
            <?php endif; ?>
            <br><br>
            <h3><?php echo $meal['meal_name']; ?></h3>
                    <p class="views">Views: <?php echo $meal['views']; ?></p>
                    <div class="meal-description">
                        <?php echo substr($meal['description'], 0, 100); ?>
                    </div>
                    <br>

                </div>
            <?php endforeach; ?>
        </div>
        <h3 style="text-align: center; margin-top: 120px;">TESTEMONIALS</h3>
        <h1 style="text-align: center; margin-top: 12px;">WHAT THE CUSTOMERS SAYS</h1>
        <p style="text-align: center; margin-top: 20px;">Checkout the testimonials from users who are already using this platform.</p>
    <div class="testimonial-container">
    <?php foreach ($recentTestimonies as $testimonial): ?>
    <div class="testimonial-card">
        <p style="text-align: center;">"<?php echo $testimonial['testimonial_text']; ?>"</p>
        <br>
        <p style="text-align: center; text-transform: uppercase;"><strong><?php echo $testimonial['username']; ?></strong></p>
        <p style="text-align: center;"><?php echo $testimonial['date_posted']; ?></p>
    </div>
<?php endforeach; ?>
</div>

        
<section id="about" class="about-container">
    <div class="about-content">
        <div class="about-text">
            <h2>About Tastebud</h2>
            <p>
                Welcome to Tastebud, where culinary excellence meets innovation. Our mission is to delight your taste buds
                with a diverse range of recipes crafted to perfection. Whether you're a seasoned chef or a passionate
                home cook, Tastebud is your companion on a flavorful journey.
            </p>
            <p>
                At Tastebud, we believe in the power of good food to bring people together. Explore our collection of
                mouth-watering recipes, cooking tips, and culinary inspiration. Join our community and elevate your
                cooking experience with Tastebud.
            </p>
        </div>
        <div class="about-image">
            <img src="image3.jpg" alt="About Image">
        </div>
    </div>
</section>

<section id="faqs" class="faq">
    <h2>Frequently Asked Questions (FAQs)</h2>
    <div>
        <h3>1. How can I submit my own recipe to Tastebud?</h3>
        <p>
            To share your delicious recipes with the Tastebud community, simply log in to your account, go to the "User
            Recipes" section, and follow the submission instructions. Your culinary creations could inspire others!
        </p>
    </div>

    <div>
        <h3>2. How do I save my favorite recipes?</h3>
        <p>
            Saving your favorite recipes is easy! Just click on the heart icon located on each recipe card. You can find
            your saved recipes in the "Favorites" section of your profile.
        </p>
    </div>

    <!-- Add more FAQs as needed -->

</section>

<section id="contact" class="contact-container">
    <h2>Contact Tastebud</h2>
    <div>
        <h3>Email:</h3>
        <p>tastebud123@tastebud.com</p>
    </div>

    <div>
        <h3>Phone:</h3>
        <p>+1 (555) 123-4567</p>
    </div>

    <div>
        <h3>Address:</h3>
        <p>123 Recipe Lane, Culinary City, TASTY-123</p>
    </div>

</section>

    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
</script>
</body>

</html>
