const API = "API/API.php";
var userCurrentlyLogged;

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
        console.log('current user:' + jsonObject.results);
        userCurrentlyLogged = jsonObject.results;

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
        fillTable();
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
    var componentToChange = document.getElementById(elementId);
    // Set the visibility based on showState
    if(!componentToChange)
    {
      console.log("element: "+elementId+" is either not currently available or is not a valid id name");
      return;
    }
    componentToChange.style.visibility = showState ? "visible" : "hidden";

    // Set the display based on showState
    componentToChange.style.display = showState ? "block" : "none";
}

function hideOrShowByClass(elementClass, showState)
{
  var  nodeList = document.getElementsByClassName(elementClass);

  if(!nodeList)
  {
    console.log("element: "+elementClass+" is either not currently available or is not a valid id name");
    return;
  }

  for(var i =0; i < nodeList.length; i++)
  {
    var node = nodeList[i];
    node.style.visibility = showState ? "visible" : "hidden";
    node.style.display = showState ? "block" : "none";
  }
}

function showAddContactDiv()
{
  hideOrShow("addContactUIDiv", true);
}

function addContact()
{
  var firstName = document.getElementById("firstN").value;
  var lastName = document.getElementById("lastN").value;
  var phoneNumber = document.getElementById("phoneN").value;
  var email = document.getElementById("email").value;
  if(!firstName | !lastName | !phoneNumber | !email)
  {
    console.log("must fill out all of the fields in order to add a contact");
    var errorMessage = document.getElementById("addingContactsErrorMessage");
    errorMessage.innerHTML = "must fill out all of the fields in order to add a contact";
    return;
  }

  var fName = '"firstName" : "' + firstName +'",';
  var lName = '"lastName" : "' + lastName +'",';
  var phone = '"phoneNumber" : "' + phoneNumber +'",';
  var emailAddress = '"emailAddress" : "' + email +'",';
  var functionName = '"function" : "addContact",';
  var user = '"userID" : "' + userCurrentlyLogged +'"';

  var jsonPayload = "{"+functionName+fName+lName+phone+emailAddress+user+"}";
  console.log("the payload for add user was: "+jsonPayload);
  CallServerSide(jsonPayload);
  var errorMessage = document.getElementById("addingContactsErrorMessage");
  errorMessage.innerHTML = "";
  setTimeout(fillTable, 3000);
  hideOrShow("addContactUIDiv", false);

  console.log(jsonPayload);

}

function CallServerSide(jsonPayload)
{
  var xhr = new XMLHttpRequest();
  xhr.open("POST", API, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  try {
      xhr.onreadystatechange = function() {

          if (this.readyState == 4 && this.status == 200) {
            console.log(xhr.responseText);
              var jsonObject = JSON.parse( xhr.responseText );
          }
      };
      xhr.send(jsonPayload);
  } catch(err) {
      console.log(err);
  }
}

function fillTable()
{
    var id = userCurrentlyLogged;

    if(!id)
    {
      console.log("no user is currently logged on");
      return;
    }
    var jsonPayload = '{"function": "getContacts", "userID" : "' + id + '"}';
    var xhr = new XMLHttpRequest();
    xhr.open("POST", API, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try {
        xhr.onreadystatechange = function() {

            if (this.readyState == 4 && this.status == 200) {
              console.log(xhr.responseText);
                var jsonObject = JSON.parse( xhr.responseText );
                buildTableHeader();
                buildTableData(jsonObject);
            }
        };
        xhr.send(jsonPayload);
    } catch(err) {
        console.log(err);
    }
}

function deleteContacts()
{
  var nodeList = document.getElementsByClassName("deleteButton");

  if(!nodeList)
  {
    console.log("table hasnt loaded yet");
    return;
  }
  for(var i = 0; i < nodeList.length; i++)
  {
    if(nodeList[i].checked)
    {
      var value = nodeList[i].parentNode.id;
      console.log("value is : " + value);
      var contactId = '"id" : "' + value + '"' ;
      var functionName = '"function" : "deleteContact",';
      var jsonPayload = "{"+functionName+contactId+"}";
      CallServerSide(jsonPayload);
    }
    if(i == nodeList.length - 1) break;
  }
  var errorMessage = document.getElementById("deletingContactsErrorMessage");
  errorMessage.innerHTML = "";
  setTimeout(fillTable, 3000);
  hideOrShow("confirmDelete", false);
  hideOrShow("showDeleteMarks", true);
}

function searchContacts()
{
  var nodeList = document.getElementsByClassName("searchByRadioButton");
  var chosenSearchOption;

  if(!nodeList)
  {
    console.log("radio buttons not currently available");
    return;
  }

  for(var i = 0; i < nodeList.length; i++)
  {
    if(nodeList[i].checked)
    {
      chosenSearchOption = nodeList[i];
      break;
    }
    if(i == 3 && !nodeList[i].checked)
    {
      console.log("the user has not selected any radio button to search by");
      var errorMessage = document.getElementById("searchingContactsErrorMessage");
      errorMessage.innerHTML = "must select an option to search by";
      return;
    }
  }
  var typedSearch = document.getElementById("searchBox").value;
  var searchOption = chosenSearchOption.id;

  if(!typedSearch)
  {
    console.log("you are searching for a blank string");
    var errorMessage = document.getElementById("searchingContactsErrorMessage");
    errorMessage.innerHTML = "you are searching for a blank string";
    return;
  }

  var searchOp = '"searchOption" : "' + searchOption +'"';
  var search = '"searchFor" : "' + typedSearch +'",';
  var user = '"userID" : "' + userCurrentlyLogged +'",';
  var functionName = '"function" : "searchContacts",';
  var jsonPayload = "{"+functionName+user+search+searchOp+"}";
  console.log(jsonPayload);

  var xhr = new XMLHttpRequest();
  xhr.open("POST", API, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  try {
      xhr.onreadystatechange = function() {

          if (this.readyState == 4 && this.status == 200) {
            console.log(xhr.responseText);
              var jsonObject = JSON.parse( xhr.responseText );
              var errorMessage = document.getElementById("searchingContactsErrorMessage");
              errorMessage.innerHTML = "";
              buildTableHeader();
              buildTableData(jsonObject);
          }
      };
      xhr.send(jsonPayload);
  } catch(err) {
      console.log(err);
  }
}


function selectContactsToDelete()
{
  hideOrShow("deleteHeader", true);
  hideOrShowByClass("deleteButton", true);
  hideOrShow("confirmDelete", true);
  hideOrShow("showDeleteMarks", false);
}

function buildTableHeader()
{
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

function buildTableData(data)
{
    var tud = document.getElementById("contactsTable");
    var i;
    if(!data)
    {
      console.log("data is not available");
      return;
    }
    for (i = 0; i < data.results.length; i++) {
        var tableRow = document.createElement('tr');
        tableRow.id = data.results[i].contactId;
        var firstName = document.createElement('td');
        firstName.innerHTML = data.results[i].firstName;
        var lastName = document.createElement('td');
        lastName.innerHTML = data.results[i].lastName;
        var phoneNumber = document.createElement('td');
        phoneNumber.innerHTML = data.results[i].phoneNumber;
        var emailAddress = document.createElement('td');
        emailAddress.innerHTML = data.results[i].emailAddress;
        var deleteButton = document.createElement('input');
        deleteButton.type = "checkbox";
        deleteButton.style.visibility = "hidden";
        deleteButton.style.display = "none";
        deleteButton.className = "deleteButton";
        tableRow.appendChild(firstName);
        tableRow.appendChild(lastName);
        tableRow.appendChild(phoneNumber);
        tableRow.appendChild(emailAddress);
        tableRow.appendChild(deleteButton);
        tud.appendChild(tableRow);
    }
}
