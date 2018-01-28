<?php

/**
 * Retrieve JSON and establish MySQL connection
 */
function establishConnection()
{
    // Get MySQL authentication information via a secrets file
    $secrets = readSecrets();

    // Establish MySQL database connection
    try {
        // Try to establish a connection
        // The @ suppresses warning messages so they don't print on the page
        $conn = @new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);
    } catch (Exception $e) {
        // If there is an exception (not warning or error), return exception message as JSON string
        returnWithError('Connection exception caught: ' . $e->getMessage());
    }

    // Check for a connection error
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError('Connection error.');

        // This more verbose error message version can be enabled for debug purposes
        // returnWithError('Connection error: ' . $conn->connect_error);
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
    // Open secrets file
    $secretsFile = fopen("../secrets", "r");

    // While we haven't reached the EOF (end of file)...
    while (!feof($secretsFile)) {
        // Read each line of the file into a string
        $secretsString = fgets($secretsFile);
    }

    // Close secrets file
    fclose($secretsFile);

    // Create an array (delimited by a comma) from the retrieved string
    $secretsArray = explode(",", $secretsString);

    // Setup the array with key-value pairs (for more user-friendly interaction)
    $secrets['host']     = $secretsArray[0];
    $secrets['username'] = $secretsArray[1];
    $secrets['passwd']   = $secretsArray[2];
    $secrets['dbname']   = $secretsArray[3];

    // Return the array
    return $secrets;
}

/**
 * Receive decoded JSON info from client-side application
 *
 *  @return array Array containing decoded JSON information
 */
function getRequestInfo()
{
    // Get the JSON decoded string from the client-side application
    $inData = json_decode(file_get_contents('php://input'), true);

    // Return the array
    return $inData;
}

/**
 * Echo JSON information back to client-side application
 *
 * @param string $json JSON encoded string
 */
function sendResultInfoAsJson($json)
{
    // Header information (needed for Heroku)
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
    // Encode the JSON information
    $json = json_encode(['success' => 'true', 'message' => $successMessage]);

    // Send JSON information back to the client-side application
    sendResultInfoAsJson($json);
}

/**
 * Setup an encoded JSON error string to send back to the client-side application
 *
 * @param string $error String for error message
 */
function returnWithError($error)
{
    // Encode the JSON information
    $json = json_encode(['error' => $error]);

    // Send JSON information back to the client-side application
    sendResultInfoAsJson($json);
}

/**
 * Setup an encoded JSON info string to send back to the client-side application
 *
 * @param string|array $results String or array for results message
 */
function returnWithResults($results)
{
    // Encode the JSON information
    $json = json_encode(['results' => $results]);

    // Send JSON information back to the client-side application
    sendResultInfoAsJson($json);
}
