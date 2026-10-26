<?php
// start session
session_start();

// check if the user is logged in and if yes, redirect to home page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: personal_cookbook.php");
    exit;
}

require_once "db_user.php";

// create new connection since we need it for login
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, "user_authentication");

// check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// define variables to store user credentials and error messages and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// check if form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // check if email is empty and if empty store error message
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else { // if not empty sanitize and store the email
        $email = trim($_POST["email"]);
    }
    // check if password is empty and if empty store error message
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else { // if not empty sanitize and store the password
        $password = trim($_POST["password"]);
    }
    // validate credentials only if no errors
    if (empty($email_err) && empty($password_err)) {
        // SQL statement to fetch user data based on email
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            // link variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            // execute the prepared statement
            if ($stmt->execute()) {
                // store result
                $stmt->store_result();
                // check if email exists
                if ($stmt->num_rows == 1) {
                    // link result variables
                    $stmt->bind_result($id, $username, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        // if email exists then verify password is correct
                        if (password_verify($password, $hashed_password)) {
                            // password is correct, start a new session
                            session_start();
                            // store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;

                            // redirect user to personal cookbook page
                            header("location: personal_cookbook.php");
                            exit;
                        } else {
                            // password is not valid
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // email doesn't exist
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // close statement
            $stmt->close();
        }
    }
    // close database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="wrapper">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Login</h2>
            <!-- display login error message if any -->
            <?php
            if (!empty($login_err)) {
                echo '<div class="error">' . $login_err . '</div>';
            }
            ?>
            <div class="input-field">
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <label>Enter your email</label>
                <!-- display email error message if any -->
                <?php if (!empty($email_err)) echo '<span class="error">' . $email_err . '</span>'; ?>
            </div>
            <div class="input-field">
                <input type="password" name="password" required>
                <label>Enter your password</label>
                <!-- display password error message if any -->
                <?php if (!empty($password_err)) echo '<span class="error">' . $password_err . '</span>'; ?>
            </div>
            <div class="forget">
                <label>
                    <input type="checkbox" name="remember" id="remember">
                    <p>Remember me</p>
                </label>
            </div>
            <button type="submit">Login</button>
            <div class="register">
                <p>Don't have an account? <a href="signUp.php" style="color: #800020;">Register</a></p>
            </div>
        </form>
    </div>
</body>

</html>