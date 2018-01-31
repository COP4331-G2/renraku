// Constant value for API path (for ease of use)
const API = "API/API.php";

var currentUserID;
var tableData;

/**
 * Attempt to login with the supplied username and password
 */
function login() {
    // Get the username and password from the HTML fields
    var username = document.getElementById("loginName").value;
    var password = document.getElementById("loginPassword").value;

    // Ensure that the HTML login result message is blank
    document.getElementById("loginResult").innerHTML = "";

    // Fail Whale (easter egg)
    if (username === "failwhale") {
        window.location.replace("fail_whale.html");
    }

    // Setup the JSON payload to send to the API
    var jsonPayload = {
        function: "loginAttempt",
        username: username,
        password: password,
    };
    jsonPayload = JSON.stringify(jsonPayload);
    console.log("JSON Payload: " + jsonPayload);

    // Setup the HMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, false);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    // Attempt to login and catch any error message
    try {
        // Send the XMLHttpRequest
        xhr.send(jsonPayload);
        console.log("JSON Response: " + xhr.responseText);

        // Parse the JSON returned from the request
        var jsonObject = JSON.parse(xhr.responseText);

        // If the returned JSON contains an error then set the HTML login result message
        if (jsonObject.error || !jsonObject.success) {
            document.getElementById("loginResult").innerHTML = jsonObject.error;
            return false;
        }

        // Set current user data
        currentUserID = jsonObject.results.id;
        console.log("Current UserID: " + currentUserID);

        // Reset the HTML fields to blank
        document.getElementById("loginName").value = "";
        document.getElementById("loginPassword").value = "";

        // Hide the login HTML elements
        hideOrShow("loginDiv", false);

        // Hide the landing page
        hideOrShow("landingPageDiv", false);

        // Show the post-login HTML elements
        hideOrShow("loggedinDiv", true);
        hideOrShow("accessUIDiv", true);

        // Fill the user's contacts table
        fillTable();
    } catch (e) {
        // If there is an error parsing the JSON, attempt to set the HTML login result message
        document.getElementById("loginResult").innerHTML = e.message;
    }

    document.getElementById("currentUserName").innerHTML = jsonObject.results.username;

    return true;
}

/**
 * Log of a user's account
 */
function doLogout() {
    // Hide the post-login HTML elements
    hideOrShow("loggedinDiv", false);
    hideOrShow("accessUIDiv", false);

    // Show the login HTML elements
    hideOrShow("loginDiv", true);
    hideOrShow("landingPageDiv", true);
}

/**
 * Hide or show an HTML element
 *
 * @param string elementId HTML element to be made visible/hidden
 * @param boolean showState Whether or not to show an element
 */
function hideOrShow(elementId, showState) {
    var componentToChange = document.getElementById(elementId);

    // Set the visibility based on showState
    if (!componentToChange) {
        console.log("Element (" + elementId + ") is either not currently available or is not a valid id name");
        return;
    }

    // Set the visibility based on showState
    componentToChange.style.visibility = showState ? "visible" : "hidden";

    // Set the display based on showState
    componentToChange.style.display = showState ? "block" : "none";
}

function hideOrShowByClass(elementClass, showState) {
    var nodeList = document.getElementsByClassName(elementClass);

    if (!nodeList) {
        console.log("Element (" + elementClass + ") is either not currently available or is not a valid id name");
        return;
    }

    for (var i = 0; i < nodeList.length; i++) {
        var node = nodeList[i];
        node.style.visibility = showState ? "visible" : "hidden";
        node.style.display = showState ? "block" : "none";
    }
}

function showAccessUIDiv() {
    hideOrShow("accessUIDiv", true);
    unSelectContactsToDelete();
}

function addContact() {
    var firstName = document.getElementById("firstNameNewEntry").value;
    var lastName = document.getElementById("lastNameNewEntry").value;
    var phoneNumber = document.getElementById("phoneNewEntry").value;
    var emailAddress = document.getElementById("emailNewEntry").value;

    if (!firstName && !lastName && !phoneNumber && !emailAddress) {
        document.getElementById("contactAddResult").innerHTML = "Please fill in information.";
        return false;
    }

    var jsonPayload = {
        function: "addContact",
        firstName: firstName,
        lastName: lastName,
        phoneNumber: phoneNumber,
        emailAddress: emailAddress,
        userID: currentUserID,
    };
    jsonPayload = JSON.stringify(jsonPayload);

    CallServerSide(jsonPayload);
    document.getElementById("contactAddResult").innerHTML = "";
    hideOrShow("accessUIDiv", true);

    document.getElementById("firstNameNewEntry").value = "";
    document.getElementById("lastNameNewEntry").value = "";
    document.getElementById("emailNewEntry").value = "";
    document.getElementById("phoneNewEntry").value = "";

    return true;
}

function CallServerSide(jsonPayload) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try {
        xhr.onreadystatechange = function() {

            if (this.readyState == 4 && this.status == 200) {
                var jsonObject = JSON.parse(xhr.responseText);
                fillTable();
            }
        };
        xhr.send(jsonPayload);
    } catch (err) {
        console.log(err);
    }
}

function fillTable() {
    if (!currentUserID) {
        return;
    }

    var jsonPayload = {
        function: "getContacts",
        userID: currentUserID,
    };
    jsonPayload = JSON.stringify(jsonPayload);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        xhr.onreadystatechange = function() {

            if (this.readyState == 4 && this.status == 200) {
                var jsonObject = JSON.parse(xhr.responseText);
                buildTableHeader();
                buildTableData(jsonObject.results);
                tableData = jsonObject.results;
            }
        };

        xhr.send(jsonPayload);
    } catch (err) {
        console.log(err);
    }
}

function deleteContacts() {
    var nodeList = document.getElementsByClassName("deleteButton");

    if (nodeList) {
        for (var i = 0; i < nodeList.length; i++) {
            if (nodeList[i].checked) {
                var contactID = nodeList[i].parentNode.parentNode.parentNode.id;

                var jsonPayload = {
                    function: "deleteContact",
                    id: contactID,
                };
                jsonPayload = JSON.stringify(jsonPayload);

                CallServerSide(jsonPayload);
            }
            if (i == nodeList.length - 1) break;
        }
    }

    // hideOrShow("confirmDelete", false);
    // hideOrShow("showDeleteMarks", true);
    unSelectContactsToDelete();

    return true;
}

function createAccount() {
    var username = document.getElementById("createUser").value;
    var password = document.getElementById("createPassword").value;
    var confirm = document.getElementById("confirmPassword").value;

    document.getElementById("createResult").innerHTML = "";

    // Ensure that the HTML login result message is blank
    // document.getElementById("createResult").innerHTML = "";

    if (password !== confirm) {
        // document.getElementById("createResult").innerHTML = "Passwords don't match";
        return;
    }

    var jsonPayload = {
        function: "createUser",
        username: username,
        password: password,
    }

    jsonPayload = JSON.stringify(jsonPayload);
    console.log("JSON Payload: " + jsonPayload);

    //setup
    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, false);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");

    try {
        //send the xml request
        xhr.send(jsonPayload);

        var jsonObject = JSON.parse(xhr.responseText);

        if (jsonObject.error) {
            document.getElementById("createResult").innerHTML = jsonObject.error;
            return false;
        }

        //make forms blank
        document.getElementById("createUser").innerHTML = "";
        document.getElementById("createPassword").innerHTML = "";
        document.getElementById("confirmPassword").innerHTML = "";

        //hide sign up
        // hideOrShow("signupDiv", false);

        //go back to login page
        // hideOrShow("homepageWelcomeDiv",true);

    } catch (e) {
        // If there is an error parsing the JSON, attempt to set the HTML login result message
        document.getElementById("loginResult").innerHTML = e.message;
    }

    return true;
}

function searchContacts() {
    var typedSearch = document.getElementById("searchText").value;
    var filteredData = tableData.filter(function(item) {
        return (stringContains(item.contactId, typedSearch) || stringContains(item.firstName, typedSearch) || stringContains(item.lastName, typedSearch) || stringContains(item.phoneNumber, typedSearch) || stringContains(item.emailAddress, typedSearch));
    });
    buildTableHeader();
    buildTableData(filteredData);
}


function selectContactsToDelete() {
    hideOrShow("deleteHeader", true);
    hideOrShowByClass("deleteButton", true);
    hideOrShow("confirmDelete", true);
    hideOrShow("showDeleteMarks", false);
}

function unSelectContactsToDelete() {
    hideOrShow("deleteHeader", false);
    hideOrShowByClass("deleteButton", false);
    hideOrShow("confirmDelete", false);
    hideOrShow("showDeleteMarks", true);
}

function buildTableHeader() {
    var tud = document.getElementById("contactsTable");
    tud.innerHTML = "";
    var thr = document.createElement('tr');
    var firstNameHeader = document.createElement('th');
    firstNameHeader.innerHTML = 'First Name';
    var lastNameHeader = document.createElement('th');
    lastNameHeader.innerHTML = 'Last Name';
    var phoneNumberHeader = document.createElement('th');
    phoneNumberHeader.innerHTML = 'Phone Number';
    var emailHeader = document.createElement('th');
    emailHeader.innerHTML = 'Email Address';
    var deleteHeader = document.createElement('th');
    deleteHeader.innerHTML = 'Delete';
    deleteHeader.style.visibility = 'hidden';
    deleteHeader.style.display = 'none';
    deleteHeader.id = "deleteHeader"

    thr.appendChild(firstNameHeader);
    thr.appendChild(lastNameHeader);
    thr.appendChild(phoneNumberHeader);
    thr.appendChild(emailHeader);
    thr.appendChild(deleteHeader);
    tud.appendChild(thr);
}

function buildTableData(data) {
    var tud = document.getElementById("contactsTable");
    var i;
    if (!data) {
        return;
    }
    for (i = 0; i < data.length; i++) {
        var tableRow = document.createElement('tr');
        tableRow.id = data[i].contactId;
        var firstName = document.createElement('td');
        firstName.innerHTML = data[i].firstName;
        var lastName = document.createElement('td');
        lastName.innerHTML = data[i].lastName;
        var phoneNumber = document.createElement('td');
        phoneNumber.innerHTML = data[i].phoneNumber;
        var emailAddress = document.createElement('td');
        emailAddress.innerHTML = data[i].emailAddress;
        var deleteButton = document.createElement('input');
        var deleteData = document.createElement('td');
        var deleteDiv = document.createElement('div');
        deleteDiv.className = "checkbox checkbox-success";

        deleteButton.type = "checkbox";
        deleteButton.style.visibility = "hidden";
        deleteButton.style.display = "none";
        deleteButton.className = "deleteButton styled ml-3";
        deleteDiv.appendChild(deleteButton);
        deleteData.appendChild(deleteDiv);

        tableRow.appendChild(firstName);
        tableRow.appendChild(lastName);
        tableRow.appendChild(phoneNumber);
        tableRow.appendChild(emailAddress);
        tableRow.appendChild(deleteData);
        tud.appendChild(tableRow);
    }
}

function stringContains(stringToCheck, substring) {
    return stringToCheck.toLowerCase().indexOf(substring.toLowerCase()) != -1;
}