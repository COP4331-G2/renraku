<?php

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

/**
 * Reads MySQL database login information through a 'secrets' file
 *
 *  @return array (array containing database login information)
 */
function readSecrets()
{
    $secretsFile = fopen("../secrets", "r");

    while (!feof($secretsFile)) {
        $secretsString = fgets($secretsFile);
    }

    fclose($secretsFile);

    $secretsArray = explode(",", $secretsString);

    $secrets['host']     = $secretsArray[0];
    $secrets['username'] = $secretsArray[1];
    $secrets['passwd']   = $secretsArray[2];
    $secrets['dbname']   = $secretsArray[3];

    return $secrets;
}

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
