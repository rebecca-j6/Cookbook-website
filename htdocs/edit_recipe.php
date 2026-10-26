<?php
// start or resume session
session_start();

// check if the user is logged in and if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db_recipe.php';

// get recipe ID from URL and ensure it's an integer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

//get user ID from session variables to ensure user ownership of the recipe
$user_id = $_SESSION["id"];

// SQL statement to fetch recipe details that match recipe ID and user ID
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();

// redirect to home page if recipe doesn't exist or doesn't belong to user
if (!$recipe) {
    header("Location: personal_cookbook.php");
    exit();
}

// process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // handle file upload
    $image_path = $recipe['image']; // Keep existing image by default
    // check if a new image file was uploaded without errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'uploads/'; // define the directory to store uploaded images
        // create the directory if it does not exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        // extract the file extension from the uploaded file
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        // generate a unique filename to prevent conflicts
        $file_name = uniqid() . '.' . $file_extension;
        // build the full target path for the uploaded file
        $target_path = $upload_dir . $file_name;
        // move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            // delete old image if it exists
            if ($recipe['image'] && file_exists($recipe['image'])) {
                unlink($recipe['image']);
            }
            // update the image path to the new file path
            $image_path = $target_path;
        }
    }

    // SQL statement to update recipe details in the database
    $update_sql = "UPDATE recipes SET recipe_name=?, cooktime=?, preptime=?, difficulty=?, ingredients=?, instructions=?, tips=?, image=? WHERE id=? AND user_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param(
        "ssssssssii",
        $_POST['recipe_name'],
        $_POST['cooktime'],
        $_POST['preptime'],
        $_POST['difficulty'],
        $_POST['ingredients'],
        $_POST['instructions'],
        $_POST['tips'],
        $image_path,
        $id,
        $user_id
    );

    // execute SQL statement and redirect to the recipe page with a success message
    if ($stmt->execute()) {
        header("Location: view_recipe.php?id=" . $id . "&message=" . urlencode("Recipe updated successfully!"));
        exit();
    } else {
        // display error message if the update fails
        $error = "Error updating recipe: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recipe - <?php echo htmlspecialchars($recipe['recipe_name']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>Edit Recipe</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
    </header>

    <div class="form-container">
        <!-- display error message if any -->
        <?php if (isset($error)) echo "<div class='error-message'>$error</div>"; ?>
        <!-- form to edit and submit udpdated recipe details -->
        <form id="recipeForm" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="recipe_name">Recipe Name</label>
                <input type="text" id="recipe_name" name="recipe_name"

                    value="<?php echo htmlspecialchars($recipe['recipe_name']); ?>" required>
            </div>

            <div class="form-group time-inputs">
                <div>
                    <label for="preptime">Prep Time (minutes)</label>
                    <input type="number" id="preptime" name="preptime"

                        value="<?php echo htmlspecialchars($recipe['preptime']); ?>" required min="1">
                </div>
                <div>
                    <label for="cooktime">Cook Time (minutes)</label>
                    <input type="number" id="cooktime" name="cooktime"

                        value="<?php echo htmlspecialchars($recipe['cooktime']); ?>" required min="0">
                </div>
                <div>
                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="Easy" <?php if ($recipe['difficulty'] == 'Easy') echo 'selected'; ?>>Easy</option>
                        <option value="Medium" <?php if ($recipe['difficulty'] == 'Medium') echo 'selected'; ?>>Medium</option>
                        <option value="Hard" <?php if ($recipe['difficulty'] == 'Hard') echo 'selected'; ?>>Hard</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="ingredients">Ingredients (one per line)</label>
                <textarea id="ingredients" name="ingredients" required><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" required><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="tips">Tips and Notes (optional)</label>
                <textarea id="tips" name="tips"><?php echo htmlspecialchars($recipe['tips']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Recipe Image</label>
                <!-- display current image if available -->
                <?php if ($recipe['image']): ?>
                    <div class="current-image">
                        <img src="<?php echo htmlspecialchars($recipe['image']); ?>" alt="Current recipe image" style="max-width: 200px;">
                        <p>Current image. Upload a new one to replace it.</p>
                    </div>
                <?php endif; ?>
                <div id="imagePreview"></div>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="action-buttons">
                <button type="submit" class="save-btn">Save Changes</button>
                <a href="view_recipe.php?id=<?php echo $id; ?>" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>

    <script src="recipe-scripts.js"></script>
</body>

</html>