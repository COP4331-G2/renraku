<?php

/**
 * Retrieve JSON and establish MySQL connection
 */
function establishConnection()
{
    // Get MySQL authentication information via a secrets file
    $secrets = readSecrets();

    // Establish MySQL database connection
    $conn = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

    // Check for a connection error
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError($conn->connect_error);
    } else {
        // If the connection is good, return the connection object
        return $conn;
    }
}

/**
 * Reads MySQL database login information through a 'secrets' file
 *
 *  @return array Array containing database login information
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

/**
 * Receive decoded JSON info from client-side application
 *
 *  @return array Array containing decoded JSON information
 */
function getRequestInfo()
{
    $inData = json_decode(file_get_contents('php://input'), true);

    return $inData;
}

/**
 * Echo JSON information back to client-side application
 *
 * @param string $json JSON encoded string
 */
function sendResultInfoAsJson($json)
{
    header('Content-type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, origin');

    // Send the JSON as a string back to the client-side
    echo $json;

    // Grab the variable from global scope
    global $conn;

    // Ensure that the connection to the database (if available) is closed
    if ($conn) {
        $conn->close();
    }

    // Ensure that the PHP server-side stops here for this request
    die;
}

/**
 * Setup an encoded JSON success string to send back to the client-side application
 *
 * @param string $successMessage String for success message
 */
function returnWithSuccess($successMessage)
{
    $json = json_encode(['success' => 'true', 'message' => $successMessage]);
    sendResultInfoAsJson($json);
}

/**
 * Setup an encoded JSON error string to send back to the client-side application
 *
 * @param string $error String for error message
 */
function returnWithError($error)
{
    $json = json_encode(['error' => $error]);
    sendResultInfoAsJson($json);
}

/**
 * Setup an encoded JSON info string to send back to the client-side application
 *
 * @param string|array $results String or array for results message
 */
function returnWithResults($results)
{
    $json = json_encode(['results' => $results]);
    sendResultInfoAsJson($json);
}
