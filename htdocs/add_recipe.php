<?php
// start or resume session
session_start();

// check if the user is logged in and if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db_recipe.php';

// check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // initialize a variable to store the path of the uploaded image
    $image_path = null;

    // check if a file was uploaded without errors
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // set the directory where uploaded images will be stored
        $upload_dir = 'uploads/';

        // create the directory if it does not exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true); // 0777 = full permissions mode
        }

        // get the file extension of the uploaded file
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        // generate a unique name for the file before saving it to prevent filename conflicts
        $file_name = uniqid() . '.' . $file_extension;

        // concatenate directory path and file name to get the full path where the uploaded file will be saved
        $target_path = $upload_dir . $file_name;

        // move the uploaded file from temporary location to target path
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) { 
            // tmp_name is an attribute of $_FILES and holds the temporary filename of the file stored on the server after it is uploaded
            $image_path = $target_path; // store the final path of the uploaded file
        }
    }

    // SQL statement to insert the recipe data into the database
    $stmt = $conn->prepare("INSERT INTO recipes (user_id, recipe_name, cooktime, preptime, difficulty, ingredients, instructions, tips, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // fetch and store user input from the form and session
    $user_id = $_SESSION["id"];
    $recipe_name = $_POST['recipe_name'];
    $cooktime = $_POST['cooktime'];
    $preptime = $_POST['preptime'];
    $difficulty = $_POST['difficulty'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $tips = $_POST['tips'];

    // link PHP variables to previous SQL statement and execute it
    $stmt->bind_param("issssssss", $user_id, $recipe_name, $cooktime, $preptime, $difficulty, $ingredients, $instructions, $tips, $image_path);
    if ($stmt->execute()) {
        // redirect user to their cookbook page with a success message if insertion was successful
        $message = urlencode("Recipe added successfully!");
        header("Location: personal_cookbook.php?message=" . $message);
        exit();
    } else {
        // display an error message if the insertion failed
        echo "Error: " . $stmt->error;
    }

    // close the statement
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Recipe</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <header>
        <h1>Add New Recipe</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p> <!-- display user's username after sanitizing -->
    </header>

    <div class="form-container">
        <form id="recipeForm" action="" method="POST" enctype="multipart/form-data">
            <!-- collect recipe details using form fields -->
            <div class="form-group">
                <label for="recipe_name">Recipe Name</label>
                <input type="text" id="recipe_name" name="recipe_name" required>
            </div>

            <div class="form-group time-inputs">
                <div>
                    <label for="preptime">Prep Time (minutes)</label>
                    <input type="number" id="preptime" name="preptime" required min="1">
                </div>
                <div>
                    <label for="cooktime">Cook Time (minutes)</label>
                    <input type="number" id="cooktime" name="cooktime" required min="0">
                </div>
                <div>
                    <label for="difficulty">Difficulty</label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="ingredients">Ingredients (one per line)</label>
                <textarea id="ingredients" name="ingredients" required></textarea>
            </div>

            <div class="form-group">
                <label for="instructions">Instructions</label>
                <textarea id="instructions" name="instructions" required></textarea>
            </div>

            <div class="form-group">
                <label for="tips">Tips and Notes (optional)</label>
                <textarea id="tips" name="tips"></textarea>
            </div>

            <div class="form-group">
                <label for="image">Recipe Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit">Add Recipe</button>
        </form>

        <a href="personal_cookbook.php" class="back-link">← Back to Recipes</a>
    </div>

    <script src="recipes_script.js"></script>
</body>

</html>