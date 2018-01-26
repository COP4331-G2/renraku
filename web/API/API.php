<?php

require 'Connection.php';

establishConnection();

/**
 * Retrieve JSON and establish MySQL connection
 *
 */
function establishConnection()
{
    // Receive decoded JSON info from client
    $inData = getRequestInfo();

    // Get MySQL authentication information via a secrets file
    $secrets = readSecrets();

    // Establish MySQL database connection
    $conn = new mysqli($secrets['host'], $secrets['username'], $secrets['passwd'], $secrets['dbname']);

    // Unicorn Magic
    // Use the JSON payload 'function' field to call a PHP function
    $function = $inData['function'];
    if (is_callable($function)) {
        $function($conn, $inData);
    } else {
        returnWithError("JSON payload tried to call undefined PHP function $function().");
    }

    // Ensure that the connection is closed
    $conn->close();
}

/**
 * Verify username/password information and (perhaps) login to a user's account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function loginAttempt($conn, $inData)
{
    // Check for a connection error
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError($conn->connect_error);
    } else {
        // Get the username and password from the JSON information
        $username = protectAgainstInjection($inData['username']);
        $password = $inData['password'];

        // MySQL query to check if the username exists in the database
        $result = $conn->query("SELECT * FROM Users WHERE username='$username'");

        // Verify if the username exists
        if ($result->num_rows > 0) {
            // If the username exists...
            // Get the other coloumn information for the user account
            $row = $result->fetch_assoc();

            // Verify if the password is correct
            if (password_verify($password, $row['password'])) {
                // If the password is correct...
                // Return the info as a JSON string
                returnWithInfo($row['id']);
            } else {
                // If the password isn't correct...
                // Return an error as a JSON string
                returnWithError("Password incorrect.");
            }
        } else {
            // If the username doesn't exist...
            // Return an error as a JSON string
            returnWithError("Username not found.");
        }
    }
}

/**
 * Create a new user account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function createUser($conn, $inData)
{
    // Get the username and password from the JSON information
    $username = protectAgainstInjection($inData["username"]);
    $password = $inData["password"];

    // Check for various error-inducing situations
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError($conn->connect_error);
    } else if (strlen($username) > 60) {
        returnWithError("Username cannot exceed 60 characters.");
    } else if (strlen($username) <= 0) {
        returnWithError("Username cannot be empty.");
    } else if (strlen($password) <= 0) {
        returnWithError("Password cannot be empty.");
    } else {
        // MySQL query to check if a username already exists in the database
        $result = $conn->query("SELECT * FROM Users WHERE username='$username'");

        // If a username already exists...
        // Return an error as a JSON string
        if ($result->num_rows > 0) {
            returnWithError("Username already exists!");
        }

        // Encrypt the password (using PHP defaults)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // MySQL query to add the username/password to the database
        $result = $conn->query("INSERT INTO Users (username, password) VALUES ('$username', '$hashedPassword')");

        // Check to see if the insertion was successful...
        if ($result) {
            // If successful, return success message as JSON string
            returnWithSuccess('User created.');
        } else {
            // If not successful, return error as JSON string
            returnWithError($conn->error);
        }
    }
}

/**
 * Delete a user account (and all associated contacts)
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function deleteUser($conn, $inData)
{
    /* Not yet implemented */

    // Will need to get the user's id
    // Then iterate through all contacts and delete them (via deleteContact())
    // Then delete the user itself

}

/**
 * Add a contact to a user's account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function addContact($conn, $inData)
{
    // Get the contact information from the JSON information
    $firstName    = $inData["firstName"];
    $lasttName    = $inData["lastName"];
    $phoneNumber  = $inData["phoneNumber"];
    $emailAddress = $inData["emailAddress"];
    $userID       = $inData["userID"];

    // Check for a connection error
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError($conn->connect_error);
    } else {
        // MySQL query to add the contact to the database
        $result = $conn->query("INSERT INTO Contacts (firstName, lastName, phoneNumber, emailAddress, userID) VALUES ('$firstName', '$lastName', '$phoneNumber', '$emailAddress', '$userID')");

        // Check to see if the insertion was successful...
        if ($result) {
            // If successful, return success message as JSON string
            returnWithSuccess('Contact created.');
        } else {
            // If not successful, return error as JSON string
            returnWithError($conn->error);
        }
    }
}

/**
 * Delete a contact from a user's account
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function deleteContact($conn, $inData)
{
    // Get contact's id from JSON information
    $contactID = $inData["id"];

    // Check for a connection error
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError($conn->connect_error);
    } else {
        // MySQL query to delete the contact from the database
        $result = $conn->query("DELETE FROM Contacts WHERE id='$contactID'");

        // Check to see if the deletion was successful...
        if ($result) {
            // If successful, return success message as JSON string
            returnWithSuccess('Contact deleted.');
        } else {
            // If not successful, return error as JSON string
            returnWithError($conn->error);
        }
    }
}

/**
 * Get all contacts from a user's account foratted as a JSON string
 *
 * @param mysqli $conn MySQL connection instance
 * @param object $inData Decoded JSON stdClass object
 *
 */
function getContacts($conn, $inData)
{
    // Get user's id from JSON information
    $userID = $inData['userID'];

    // Check for a connection error
    if ($conn->connect_error) {
        // If there was a connection error, reutrn error as JSON string
        returnWithError($conn->connect_error);
    } else {
        // MySQL query to get ALL contacts associated with the user in the database
        $result = $conn->query("SELECT * FROM Contacts WHERE userID=$userID");

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

        // Return the built searchResults array as a JSON string
        returnWithInfo($searchResults);
    }
}

/**
 * Remove single-quotes and semicolons from a string to protect against SQL injection
 *
 * @param string $string A SQL-formatted string
 *
 * @return string
 *
 */
function protectAgainstInjection($string)
{
    // Remove any single-quote or semicolon from the string
    $result  = str_replace("'", "", $string);
    $result2 = str_replace(";", "", $result);

    // Remove any whitespace in the beginning or end of the string
    return trim($result2);
}
