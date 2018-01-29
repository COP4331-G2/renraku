<?php

/**
 * Establish MySQL connection
 */
function establishConnection()
{
    // Get MySQL authentication information via a secrets file
    $secrets = readSecrets();

    // Establish MySQL database connection
    try {
        // Try to establish a connection
        // The @ suppresses warning messages so they don't print on the page
        $dbConnection = @new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);
    } catch (Exception $e) {
        // If there is an exception (not warning or error), return exception message as JSON string
        returnError('Connection exception caught.');

        // This more verbose exception message can be enabled for debug purposes
        // returnError('Connection exception caught: ' . $e->getMessage());
    }

    // Check for a connection error
    if ($dbConnection->connect_error) {
        // If there was a connection error, reutrn error as JSON response
        returnError('Connection error.');

        // This more verbose error message can be enabled for debug purposes
        // returnError('Connection error: ' . $dbConnection->connect_error);
    } else {
        // If the connection is good, return the connection object
        return $dbConnection;
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
    $secretsFile = fopen('../secrets', 'r');

    // If secrets file cannot be opened, return JSON error response
    if (!$secretsFile) {
        returnError('Connection error (cannot open database credentials).');
    }

    // While we haven't reached the EOF (end of file)...
    while (!feof($secretsFile)) {
        // Read each line of the file into a string
        $secretsString = fgets($secretsFile);
    }

    // Close secrets file
    fclose($secretsFile);

    // Create an array (delimited by a comma) from the retrieved string
    $secretsArray = explode(',', $secretsString);

    // Setup the array with key-value pairs (for more user-friendly interaction)
    $secrets['host']     = $secretsArray[0];
    $secrets['username'] = $secretsArray[1];
    $secrets['passwd']   = $secretsArray[2];
    $secrets['dbname']   = $secretsArray[3];

    // Return the array
    return $secrets;
}

/**
 * Receive decoded JSON payload from client-side application
 *
 *  @return array Array containing decoded JSON information
 */
function getJSONPayload()
{
    // Get the JSON decoded string from the client-side application
    $jsonPayload = json_decode(file_get_contents('php://input'), true);

    // Return the array
    return $jsonPayload;
}

/**
 * Echo JSON response back to client-side application
 *
 * @param string $json JSON encoded string
 */
function sendJSONResponse($jsonResponse)
{
    // Header information (needed for Heroku)
    header('Content-type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, origin');

    // Send the JSON as a string back to the client-side
    echo $jsonResponse;

    // Grab the variable from global scope
    global $dbConnection;

    // Ensure that the connection to the database (if available) is closed
    if ($dbConnection) {
        $dbConnection->close();
    }

    // Ensure that the PHP server-side stops here for this request
    die;
}

/**
 * Setup an encoded JSON response to send back to the client-side application
 *
 * @param string $successMessage String for success message
 * @param string|array $results String or array for results
 */
function returnSuccess($message = '', $results = '')
{
    // Encode the JSON information
    $json = json_encode([
        'success' => true,
        'message' => $message,
        'results' => $results,
    ]);

    // Send JSON response back to the client-side application
    sendJSONResponse($json);
}

/**
 * Setup an encoded JSON error response to send back to the client-side application
 *
 * @param string $message String for error message
 */
function returnError($message)
{
    // Encode the JSON information
    $json = json_encode([
        'success' => false,
        'error'   => $message,
    ]);

    // Send JSON information back to the client-side application
    sendJSONResponse($json);
}
