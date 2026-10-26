<?php
// start session
session_start();

require_once "db_user.php";

// create database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// create user_authentication database if it does not exist
$db1 = "CREATE DATABASE IF NOT EXISTS user_authentication";
if ($conn->query($db1) === FALSE) {
    die("Error creating user_authentication database: " . $conn->error);
}

// create recipes database if it does not exist
$db2 = "CREATE DATABASE IF NOT EXISTS recipes";
if ($conn->query($db2) === FALSE) {
    die("Error creating recipes database: " . $conn->error);
}

// select user_authentication database
$conn->select_db("user_authentication");

// create users table if it does not exist
$sql1 = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql1) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// select recipes database
$conn->select_db("recipes");

// create recipes table if it does not exist
$sql2 = "CREATE TABLE IF NOT EXISTS recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_name VARCHAR(100) NOT NULL,
    cooktime VARCHAR(20),
    preptime VARCHAR(20),
    duration VARCHAR(20),
    difficulty VARCHAR(20),
    ingredients TEXT NOT NULL,
    instructions TEXT NOT NULL,
    tips TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user_authentication.users(id)
)";

if ($conn->query($sql2) === FALSE) {
    die("Error creating recipes table: " . $conn->error);
}

// switch to user_authentication database to handle registration
$conn->select_db("user_authentication");

// define variables to store user data and error messages and initialize with empty values
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";
$success_msg = "";

// process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // check if the username field is empty or already taken
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        // SQL statement to check if the username already exists
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            // link input username to the SQL statement
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    // if username is already taken, set an error message
                    $username_err = "This username is already taken.";
                } else {
                    $username = trim($_POST["username"]);
                }
            }
            $stmt->close();
        }
    }

    // check if email is empty and validate format
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }

    // check if password empty and validate minimum length
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // check if confirm password match entered password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        // SQL statement to insert new user into database       
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            // link variables to SQL statement
            $stmt->bind_param("sss", $param_username, $param_email, $param_password);
            $param_username = $username;
            $param_email = $email;
            // hash password for security
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            if ($stmt->execute()) {
                // set session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $conn->insert_id; // get ID of the newly registered user
                $_SESSION["username"] = $username;
                // redirect to homepage with success message
                header("Location: personal_cookbook.php?message=" . urlencode("Welcome! Registration successful!"));
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="wrapper">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Register</h2>
            <?php
            if (!empty($success_msg)) {
                echo '<div class="success">' . $success_msg . '</div>';
            }
            ?>
            <div class="input-field">
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <label>Enter your username</label>
                <?php if (!empty($username_err)) echo '<span class="error">' . $username_err . '</span>'; ?>
            </div>
            <div class="input-field">
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label>Enter your email</label>
                <?php if (!empty($email_err)) echo '<span class="error">' . $email_err . '</span>'; ?>
            </div>
            <div class="input-field">
                <input type="password" name="password" required>
                <label>Enter your password</label>
                <?php if (!empty($password_err)) echo '<span class="error">' . $password_err . '</span>'; ?>
            </div>
            <div class="input-field">
                <input type="password" name="confirm_password" required>
                <label>Confirm Password</label>
                <?php if (!empty($confirm_password_err)) echo '<span class="error">' . $confirm_password_err . '</span>'; ?>
            </div>
            <button type="submit">Register</button>
            <div class="register">
                <p>Already have an account? <a href="login.php" style="color: #800020;">Login</a></p>
            </div>
        </form>
    </div>
</body>

</html>