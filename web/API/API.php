<?php

// Add file with connection-related functions
require 'Connection.php';

// Receive decoded JSON payload from client
$jsonPayload = getJSONPayload();

// Establish a connection to the database
$dbConnection = establishConnection();

// Call the client-requested function
callVariableFunction($dbConnection, $jsonPayload);

/* *************** */
/* Functions Below */
/* *************** */

/**
 * Call a variable function passed as a string from the client-side
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function callVariableFunction($dbConnection, $jsonPayload)
{
    // Get function name (as string) from the JSON payload
    $function = $jsonPayload['function'];

    // Ensure that the function exists and is callable
    if (is_callable($function)) {
        // Use the JSON payload 'function' string field to call a PHP function
        $function($dbConnection, $jsonPayload);
    } else {
        // If the function is not callable, return a JSON error response
        returnError('JSON payload tried to call undefined PHP function ' . $function . '()');
    }
}

/**
 * Verify username/password information and (perhaps) login to a user's account
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function loginAttempt($dbConnection, $jsonPayload)
{
    // Get the username and password from the JSON payload
    $username = protectAgainstInjection($jsonPayload['username']);
    $password = $jsonPayload['password'];

    // MySQL query to check if the username exists in the database
    $result = $dbConnection->query("SELECT * FROM Users WHERE username='$username'");

    // Verify if the username exists
    if ($result->num_rows > 0) {
        // If the username exists...
        // Get the other coloumn information for the user account
        $row = $result->fetch_assoc();

        // Verify if the password is correct
        if (password_verify($password, $row['password'])) {
            // If the password is correct...
            // Return the JSON success response (including user's id)
            returnSuccess('Login successful.', $row['id']);
        } else {
            // If the password isn't correct...
            // Return a JSON error response
            returnError('Password incorrect.');
        }
    } else {
        // If the username doesn't exist...
        // Return a JSON error response
        returnError('Username not found.');
    }
}

/**
 * Create a new user account
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function createUser($dbConnection, $jsonPayload)
{
    // Get the username and password from the JSON payload
    $username = protectAgainstInjection($jsonPayload['username']);
    $password = $jsonPayload['password'];

    // Check for various error-inducing situations
    if (strlen($username) > 60) {
        returnError('Username cannot exceed 60 characters.');
    } else if (strlen($username) <= 0) {
        returnError('Username cannot be empty.');
    } else if (strlen($password) <= 0) {
        returnError('Password cannot be empty.');
    } else {
        // MySQL query to check if a username already exists in the database
        $result = $dbConnection->query("SELECT * FROM Users WHERE username='$username'");

        // If a username already exists...
        // Return a JSON error response
        if ($result->num_rows > 0) {
            returnError('Username already exists.');
        }

        // Encrypt the password (using PHP defaults)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // MySQL query to add the username and password into the database
        $result = $dbConnection->query("INSERT INTO Users (username, password) VALUES ('$username', '$hashedPassword')");

        // Check to see if the insertion was successful...
        if ($result) {
            // If successful, return JSON success response
            returnSuccess('User created.');
        } else {
            // If not successful, return JSON error response
            returnError($dbConnection->error);
        }
    }
}

/**
 * Delete a user account (and all associated contacts)
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function deleteUser($dbConnection, $jsonPayload)
{
    /* Not yet implemented */

    // Will need to get the user's id
    // Then iterate through all contacts and delete them (via deleteContact())
    // Then delete the user itself

}

/**
 * Add a contact to a user's account
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function addContact($dbConnection, $jsonPayload)
{
    // Get the contact information from the JSON payload
    $firstName    = $jsonPayload['firstName'];
    $lasttName    = $jsonPayload['lastName'];
    $phoneNumber  = $jsonPayload['phoneNumber'];
    $emailAddress = $jsonPayload['emailAddress'];
    $userID       = $jsonPayload['userID'];

    // MySQL query to add the contact to the database
    $result = $dbConnection->query("INSERT INTO Contacts (firstName, lastName, phoneNumber, emailAddress, userID) VALUES ('$firstName', '$lastName', '$phoneNumber', '$emailAddress', '$userID')");

    // Check to see if the insertion was successful...
    if ($result) {
        // If successful, return success message as JSON string
        returnSuccess('Contact created.');
    } else {
        // If not successful, return error as JSON string
        returnError($dbConnection->error);
    }
}

/**
 * Delete a contact from a user's account
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function deleteContact($dbConnection, $jsonPayload)
{
    // Get the contact's id from JSON payload
    $contactID = $jsonPayload['id'];

    // MySQL query to delete the contact from the database
    $result = $dbConnection->query("DELETE FROM Contacts WHERE id=$contactID");

    // Check to see if the deletion was successful...
    if ($result) {
        // If successful, return JSON success response
        returnSuccess('Contact deleted.');
    } else {
        // If not successful, return JSON error response
        returnError($dbConnection->error);
    }
}

/**
 * Get all contacts from a user's account prepared for a JSON repsonse
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function getContacts($dbConnection, $jsonPayload)
{
    // Get the user's id from JSON payload
    $userID = $jsonPayload['userID'];

    // MySQL query to get ALL contacts associated with the user in the database
    $result = $dbConnection->query("SELECT * FROM Contacts WHERE userID=$userID");

    // Setup an array to store multiple contact information
    $searchResults = [];

    // Iterate through all found contacts to store their information
    while ($row = $result->fetch_assoc()) {
        // Column information for a contact
        $contactInformation = [
            'contactId'    => $row['id'],
            'firstName'    => $row['firstName'],
            'lastName'     => $row['lastName'],
            'phoneNumber'  => $row['phoneNumber'],
            'emailAddress' => $row['emailAddress'],
        ];

        // Append this information to the searchResults array
        $searchResults[] = $contactInformation;
    }

    // Return the built searchResults array prepared for a JSON response
    returnSuccess('Contacts found.', $searchResults);
}

/**
 * Remove single-quotes and semicolons from a string to protect against SQL injection
 *
 * @param string $jsonString A SQL-formatted string
 *
 * @return string Updated string
 */
function protectAgainstInjection($jsonString)
{
    // Remove any single-quote or semicolon from the string
    $result  = str_replace("'", "", $jsonString);
    $result2 = str_replace(";", "", $result);

    // Remove any whitespace in the beginning or end of the string
    return trim($result2);
}
