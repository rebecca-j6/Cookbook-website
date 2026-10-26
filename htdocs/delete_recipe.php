<?php
// start or resume session
session_start();

// check if the user is logged in and if not, redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db_recipe.php';

// check if ID is provided and if not redirect to home page with an error message
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: personal_cookbook.php?error=" . urlencode("No recipe specified for deletion."));
    exit;
}

// get recipe ID and user ID
$recipe_id = (int)$_GET['id'];
$user_id = $_SESSION["id"];

// SQL statement to select the recipe from the database using the provided recipe ID and user ID
$check_sql = "SELECT * FROM recipes WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $recipe_id, $user_id); // link PHP variables to previous SQL statement
$check_stmt->execute();
$result = $check_stmt->get_result(); // get the SQL result

// check if the query returned any rows and if not, the recipe does not exist or the user does not have permission to delete it
if ($result->num_rows === 0) {
    header("Location: personal_cookbook.php?error=" . urlencode("Recipe not found or access denied."));
    exit;
}

// fetch recipe details from the SQL result
$recipe = $result->fetch_assoc();

// SQL statement to delete the recipe from the database
$delete_sql = "DELETE FROM recipes WHERE id = ? AND user_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("ii", $recipe_id, $user_id);

// execute SQL statement and check if it was successful
if ($delete_stmt->execute()) {
    // delete associated image if it exists on the server
    if (!empty($recipe['image']) && file_exists($recipe['image'])) {
        unlink($recipe['image']);
    }
    // redirect to home page with a success message
    header("Location: personal_cookbook.php?message=" . urlencode("Recipe deleted successfully!"));
    exit;
} else {
    // redirect to home page with an error message
    header("Location: personal_cookbook.php?error=" . urlencode("Failed to delete recipe. Please try again."));
    exit;
}

// close statement and database connection
$delete_stmt->close();
$db->close();
