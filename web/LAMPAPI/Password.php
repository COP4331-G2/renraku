<?php

require 'Connection.php';

// THIS FILE IS JUST FOR TESTING
// Password test using PHP's default parameters

// Open a MySQL connection
$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

// Create (hard-coded) and store the password
$password = "COP4331";
storePassword($password, $conn);

// Verify that the password entered matches the DB's hashed password
// $result = verifyPassword($password, $conn);

// print($result . "\n");

$conn->close();

function storePassword($password, $conn)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $conn->query("INSERT INTO Users (username, password) VALUES ('rickl', '$hashedPassword')");
}

function verifyPassword($password, $conn)
{
    $hashedPassword = $conn->query("SELECT * FROM Users WHERE username='testing'");
    $hashedPassword = $hashedPassword->fetch_assoc();
    $hashedPassword = $hashedPassword['password'];
    $hashedPassword = substr($hashedPassword, 0, 60);

    return password_verify($password, $hashedPassword);
}
