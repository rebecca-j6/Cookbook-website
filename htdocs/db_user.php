<?php
// Database configuration
define('DB_SERVER', 'sql306.infinityfree.com');
define('DB_USERNAME', 'if0_37258377');
define('DB_PASSWORD', 'QpGbzlpxhycIeo');
 
// Create database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);
 
// create database
$db = "CREATE DATABASE IF NOT EXISTS user_authentication";
 
// create user table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
 
?>

