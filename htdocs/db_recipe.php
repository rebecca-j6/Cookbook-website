<?php
// database configuration
define('DB_SERVER', 'sql306.infinityfree.com');
define('DB_USERNAME', 'if0_37258377');
define('DB_PASSWORD', 'QpGbzlpxhycIeo');
 
// create database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
  
// create database
$db = "CREATE DATABASE IF NOT EXISTS recipes";

// select database
$conn->select_db("recipes");

// create recipes table
$sql = "CREATE TABLE IF NOT EXISTS recipes (
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
?>
