<?php
$inData = getRequestInfo();

$username = protectInjection($inData["username"]);
$password = $inData["password"];

$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

sendQuery($conn);

$conn->close();

function sendQuery($conn)
{
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

/**
 * Reads MySQL database login information through a 'secrets' file
 *
 *  @return array (array containing database login information)
 */
function readSecrets()
{
    $secretsFile = fopen("../secrets", "r");

    if (!$secretsFile) {
        returnWithError("Cannot access secrets file.");
    }

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
