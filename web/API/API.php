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
 * TODO: This should check against a whitelist
 *
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
    $username = trim($jsonPayload['username']);
    $password = $jsonPayload['password'];

    // This block uses prepared statements and parameterized queries to protect against SQL injection
    // MySQL query to check if the username exists in the database
    $query = $dbConnection->prepare("SELECT * FROM Users WHERE username = ?");
    $query->bind_param('s', $username);
    $query->execute();

    // Result from the query
    $result = $query->get_result();

    // Verify if the username exists
    if ($result->num_rows > 0) {
        // If the username exists...
        // Get the other coloumn information for the user account
        $row = $result->fetch_assoc();

        // Verify if the password is correct
        if (password_verify($password, $row['password'])) {
            // If the password is correct...
            // Setup a JSON response
            $response = [
                'id'       => $row['id'],
                'username' => $row['username'],
            ];
            // Return the JSON success response (including user's id)
            returnSuccess('Login successful.', $response);
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
    $username = trim($jsonPayload['username']);
    $password = $jsonPayload['password'];

    // Check for various error-inducing situations
    if (strlen($username) > 60) {
        returnError('Username cannot exceed 60 characters.');
    } else if (strlen($username) <= 0) {
        returnError('Username cannot be empty.');
    } else if (strlen($password) <= 0) {
        returnError('Password cannot be empty.');
    } else {
        // This block uses prepared statements and parameterized queries to protect against SQL injection
        // MySQL query to check if a username already exists in the database
        $query = $dbConnection->prepare("SELECT * FROM Users WHERE username = ?");
        $query->bind_param('s', $username);
        $query->execute();

        // Result from the query
        $result = $query->get_result();

        // If a username already exists...
        // Return a JSON error response
        if ($result->num_rows > 0) {
            returnError('Username already exists.');
        }

        // Encrypt the password (using PHP defaults)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // This block uses prepared statements and parameterized queries to protect against SQL injection
        // MySQL query to add the username and password into the database
        $query = $dbConnection->prepare("INSERT INTO Users (username, password) VALUES (?, ?)");
        $query->bind_param('ss', $username, $hashedPassword);
        $query->execute();

        // Result from the query
        $result = $query->get_result();

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
 * Add a contact to a user's account
 *
 * @param mysqli $dbConnection MySQL connection instance
 * @param object $jsonPayload Decoded JSON stdClass object
 */
function addContact($dbConnection, $jsonPayload)
{
    // Get the contact information from the JSON payload
    $firstName    = $jsonPayload['firstName'];
    $lastName     = $jsonPayload['lastName'];
    $phoneNumber  = $jsonPayload['phoneNumber'];
    $emailAddress = $jsonPayload['emailAddress'];
    $userID       = $jsonPayload['userID'];

    // This block uses prepared statements and parameterized queries to protect against SQL injection
    // MySQL query to add the contact to the database
    $query = $dbConnection->prepare("INSERT INTO Contacts (firstName, lastName, phoneNumber, emailAddress, userID) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param('ssssi', $firstName, $lastName, $phoneNumber, $emailAddress, $userID);
    $query->execute();

    // Result from the query
    $result = $query->get_result();

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
    $result = $dbConnection->query("DELETE FROM Contacts WHERE id = $contactID");

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
    $result = $dbConnection->query("SELECT * FROM Contacts WHERE userID = $userID");

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
 * TESTING FUNCTION FOR MUSU
 */
function musuTest($dbConnection, $jsonPayload)
{
    $result = $dbConnection->query("SELECT username FROM Users WHERE userID = 1");

    returnSuccess('MUSU TEST', $result);
}
