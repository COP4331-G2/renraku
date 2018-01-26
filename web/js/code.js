const API = "API/API.php";

/**
 * Attempt to login with the supplied username and password
 */
function doLogin()
{
    // Get the username and password from the HTML fields
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    // Ensure that the HTML login result message is blank
    document.getElementById("loginResult").innerHTML = "";

    // Setup the JSON payload to send to the API
    var jsonPayload = '{"function": "loginAttempt", "username": "' + username + '", "password": "' + password + '"}';
    console.log("JSON Payload: " + jsonPayload);

    // Setup the HMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, false);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    // Attempt to login and catch any error message
    try {
        // Send the XMLHttpRequest
        xhr.send(jsonPayload);
        console.log("***" + xhr.responseText);

        // Parse the JSON returned from the request
        var jsonObject = JSON.parse(xhr.responseText);

        // If the returned JSON contains an error then set the HTML login result message
        if (jsonObject.error) {
            document.getElementById("loginResult").innerHTML = jsonObject.error;
            return;
        }

        // Reset the HTML fields to blank
        document.getElementById("username").value = "";
        document.getElementById("password").value = "";

        // Hide the login HTML elements
        hideOrShow( "loginDiv", false);

        // Show the post-login HTML elements
        hideOrShow( "loggedInDiv", true);
        hideOrShow( "accessUIDiv", true);

        // Fill the user's contacts table
        // This also currently sets the userID for fillTable()
        fillTable(jsonObject.results);
    } catch(e) {
        // If there is an error parsing the JSON, attempt to set the HTML login result message
        document.getElementById("loginResult").innerHTML = e.message;
    }

}

/**
 * Logout of a user's account
 */
function doLogout()
{
    // Hide the post-login HTML elements
    hideOrShow("loggedInDiv", false);
    hideOrShow("accessUIDiv", false);

    // Show the login HTML elements
    hideOrShow("loginDiv", true);
}

/**
 * Hide or show an HTML element
 *
 * @param string elementId HTML element to be made visible/hidden
 * @param boolean showState Whether or not to show an element
 */
function hideOrShow(elementId, showState)
{
    // Set the visibility based on showState
    document.getElementById(elementId).style.visibility = showState ? "visible" : "hidden";

    // Set the display based on showState
    document.getElementById(elementId).style.display = showState ? "block" : "none";
}

function fillTable(userID)
{
    var jsonPayload = '{"function": "getContacts", "userID" : "' + userID + '"}';
    console.log('hey there im in the function');
    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    console.log('hey there im in the functio12n');
    try {
        xhr.onreadystatechange = function() {
            console.log('hey there im in the function12425');

            if (this.readyState == 4 && this.status == 200) {
                console.log('hey there im in the function1242533');
                var jsonObject = JSON.parse( xhr.responseText );
                buildTableHeader();
                buildTableData(jsonObject);
            }
        };

        console.log('hey there im in the function12555');
        xhr.send(jsonPayload);
        console.log('hey there im in the function1234');
    } catch(err) {
        console.log(err);
    }
}

function testData()
{
  buildTableHeader();
  var contacts = [];
  var contact = {firstName: "kevin", lastName: "santana", phoneNumber: "954-661-8004",
                  emailAddress: "kevinsantana132@gmail.com"};
  contacts.push(contact);
  var contact = {firstName: "john", lastName: "doe", phoneNumber: "954-661-8004",
                  emailAddress: "kevinsantana11@gmail.com"};
  contacts.push(contact);
  buildTableData(contacts);
}

function buildTableHeader()
{
    var tud = document.getElementById("contactsTable");
    var thr = document.createElement('tr');
    var firstNameHeader = document.createElement('th');
    firstNameHeader.innerHTML = 'First Name';
    var lastNameHeader = document.createElement('th');
    lastNameHeader.innerHTML = 'Last Name';
    var phoneNumberHeader = document.createElement('th');
    phoneNumberHeader.innerHTML = 'Phone Number';
    var emailHeader = document.createElement('th');
    emailHeader.innerHTML = 'Email Address';
    thr.appendChild(firstNameHeader);
    thr.appendChild(lastNameHeader);
    thr.appendChild(phoneNumberHeader);
    thr.appendChild(emailHeader);
    tud.appendChild(thr);
}

function buildTableData(data)
{
    var tud = document.getElementById("contactsTable");
    var i;
    for (i = 0; i < data.results.length; i++) {
        var tableRow = document.createElement('tr');
        var firstName = document.createElement('td');
        firstName.innerHTML = data.results[i].firstName;
        var lastName = document.createElement('td');
        lastName.innerHTML = data.results[i].lastName;
        var phoneNumber = document.createElement('td');
        phoneNumber.innerHTML = data.results[i].phoneNumber;
        var emailAddress = document.createElement('td');
        emailAddress.innerHTML = data.results[i].emailAddress;
        tableRow.appendChild(firstName);
        tableRow.appendChild(lastName);
        tableRow.appendChild(phoneNumber);
        tableRow.appendChild(emailAddress);
        tud.appendChild(tableRow);
    }
}
