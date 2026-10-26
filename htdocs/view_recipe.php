<?php
// start or resume session
session_start();

// check if the user is logged in and if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db_recipe.php';

// get recipe ID from URL and if not present set to 0
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// get user ID from session
$user_id = $_SESSION["id"];

// SQL statement to fetch user recipe details
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

// if recipe doesn't exist or doesn't belong to user, redirect to home page
if (!$recipe) {
    header("Location: personal_cookbook.php");
    exit();
}

// convert ingredients string to array
$ingredients = explode("\n", $recipe['ingredients']);
// convert instructions string to array
$instructions = explode("\n", $recipe['instructions']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['recipe_name']); ?> - My Cookbook</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <header>
        <h1>My Personal Cookbook</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
        <a href="personal_cookbook.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Recipes</a>
    </header>

    <div class="recipe-detail-container">
        <div class="recipe-detail-header">
            <div class="recipe-detail-image">
                <img src="<?php echo $recipe['image'] ? htmlspecialchars($recipe['image']) : '/api/placeholder/800/400'; ?>"
                    alt="<?php echo htmlspecialchars($recipe['recipe_name']); ?>">
            </div>
            <div class="recipe-detail-info">
                <h1><?php echo htmlspecialchars($recipe['recipe_name']); ?></h1>
                <div class="recipe-meta">
                    <div class="meta-item">
                        <span class="meta-label">Prep Time</span>
                        <span class="meta-value">
                            <i class="far fa-clock"></i> <?php echo htmlspecialchars($recipe['preptime']); ?> mins
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Cook Time</span>
                        <span class="meta-value">
                            <i class="fas fa-fire"></i> <?php echo htmlspecialchars($recipe['cooktime']); ?> mins
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Total Time</span>
                        <span class="meta-value">
                            <i class="fas fa-hourglass-half"></i>
                            <?php echo (intval($recipe['preptime']) + intval($recipe['cooktime'])); ?> mins
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Difficulty</span>
                        <span class="meta-value difficulty-badge <?php echo strtolower($recipe['difficulty']); ?>">
                            <?php echo htmlspecialchars($recipe['difficulty']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="recipe-detail-content">
            <div class="recipe-section">
                <h2><i class="fas fa-list"></i> Ingredients</h2>
                <ul class="ingredients-list">
                    <?php foreach ($ingredients as $ingredient): ?>
                        <?php if (trim($ingredient) !== ''): ?>
                            <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="recipe-section">
                <h2><i class="fas fa-tasks"></i> Instructions</h2>
                <ol class="instructions-list">
                    <?php foreach ($instructions as $instruction): ?>
                        <?php if (trim($instruction) !== ''): ?>
                            <li><?php echo htmlspecialchars(trim($instruction)); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </div>

            <?php if (!empty(trim($recipe['tips']))): ?>
                <div class="recipe-section">
                    <h2><i class="fas fa-lightbulb"></i> Tips and Notes</h2>
                    <div class="tips-content">
                        <?php echo nl2br(htmlspecialchars($recipe['tips'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="action-buttons">
            <a href="edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="action-btn edit-btn">
                <i class="fas fa-edit"></i> Edit Recipe
            </a>
            <a href="delete_recipe.php?id=<?php echo $recipe['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this recipe? This action cannot be undone.');">
                <i class="fas fa-trash-alt"></i> Delete Recipe
            </a>
            <a href="#" onclick="window.print()" class="action-btn print-btn">
                <i class="fas fa-print"></i> Print Recipe
            </a>
        </div>
    </div>

    <script src="recipe-scripts.js"></script>

</body>

</html>