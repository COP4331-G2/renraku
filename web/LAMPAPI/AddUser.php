<?php

require 'Connection.php';

$inData = getRequestInfo();

$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

sendQuery($conn);

$conn->close();

function sendQuery($conn, $inData)
{
    $username = protectInjection($inData["username"]);
    $password = $inData["password"];

    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else if (strlen($username) > 60) {
        returnWithError("Username needs to be equal to or less than 60 characters.");
    } else if (strlen($username) <= 0) {
        returnWithError("Username cannot be empty.");
    } else if (strlen($password) <= 0) {
        returnWithError("Password cannot be empty.");
    }

    $result = $conn->query("SELECT username FROM Users WHERE username='$username'");

    if ($result->num_rows != 0) {
        returnWithError("Username already exists!");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $result = $conn->query("INSERT INTO Users (username, password) VALUES ('$username', '$hashedPassword')");

    if ($result) {
        returnWithError("");
    } else {
        returnWithError($conn->error);
    }
}

function getRequestInfo()
{
    return json_decode(file_get_contents('php://input'), true);
}

function sendResultInfoAsJson($obj)
{
    header('Content-type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, origin');
    echo $obj;
}

function returnWithError($err)
{
    $retValue = '{"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function protectInjection($string)
{
    $result  = str_replace("'", "", $string);
    $result2 = str_replace(";", "", $result);
    return trim($result2);
}
