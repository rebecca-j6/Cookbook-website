<?php
// start or resume session
session_start();

// check if the user is logged in and if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// get username and user_id from session
$username = $_SESSION["username"];
$user_id = $_SESSION["id"];

require_once 'db_recipe.php';

// SQL statemenent to fetch the user's recipes
$query = "SELECT * FROM recipes WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// get total number of recipes
$total_recipes = $result->num_rows;

// get success message from statement if any
$success_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Personal Cookbook</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>My Personal Cookbook</h1>
        <p>Welcome, <?php echo htmlspecialchars($username); ?>!</p>
        <a href="add_recipe.php" class="add-recipe-btn">Add New Recipe</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </header>

    <!-- display any success message from recipe operations -->
    <?php if ($success_message): ?>
        <div id="success-message" class="success-message show">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <!-- display total number of recipes -->
    <div class="recipe-counter">
        <?php
        if ($total_recipes > 0) {
            echo "<p>Showing " . $total_recipes . " " . ($total_recipes == 1 ? "recipe" : "recipes") . "</p>";
        }
        ?>
    </div>

    <!-- display recipes in grid -->
    <main class="recipe-grid">
        <?php
        if ($total_recipes > 0) {
            while ($row = $result->fetch_assoc()) {
                $image = $row['image'] ? htmlspecialchars($row['image']) : '/api/placeholder/300/200';
                echo "
<a href='view_recipe.php?id={$row['id']}' class='recipe-card'>
<img src='{$image}' alt='" . htmlspecialchars($row['recipe_name']) . "' class='recipe-image'>
<div class='recipe-content'>
<h2 class='recipe-title'>" . htmlspecialchars($row['recipe_name']) . "</h2>
<div class='recipe-info'>
<span>Prep: " . htmlspecialchars($row['preptime']) . " mins</span>
<span>Cook: " . htmlspecialchars($row['cooktime']) . " mins</span>
<span class='recipe-difficulty'>" . htmlspecialchars($row['difficulty']) . "</span>
</div>
</div>
</a>";
            }
        } else {
            echo "
<div class='empty-state'>
<p>You haven't added any recipes yet!</p>
<p>Click the 'Add New Recipe' button above to get started.</p>
</div>";
        }
        ?>
    </main>

    <script src="recipes_script.js"></script>
</body>

</html>