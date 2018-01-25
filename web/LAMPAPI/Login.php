<?php

$inData = getRequestInfo();

$secrets = readSecrets();
$conn    = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

checkLoginInfo($conn, $inData);

$conn->close();

function checkLoginInfo($conn, $inData)
{
    if ($conn->connect_error) {
        returnWithError($conn->connect_error);
    } else {
        $username = protectInjection($inData['login']);
        $password = $inData['password'];

        $result = $conn->query("SELECT * FROM Users WHERE username='$username'");

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                returnWithInfo($row['id']);
            } else {
                returnWithError("Password incorrect");
            }
        } else {
            returnWithError("No Records Found");
        }
    }
}

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
    $retValue = '{"id":-1,"error":"' . $err . '"}';
    sendResultInfoAsJson($retValue);
}

function returnWithInfo($id)
{
    $retValue = '{"id":' . $id . '}';
    sendResultInfoAsJson($retValue);
}

function protectInjection($string)
{
    $string = str_replace("'", "", $string);
    $string = str_replace(";", "", $string);
    return trim($string);
}
