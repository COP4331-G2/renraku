const API = "API/API.php";
var userCurrentlyLogged;
var tableData;

// var searchBox = document.getElementById("searchText");
// searchBox.addEventListener("change", searchContacts());

/**
 * Attempt to login with the supplied username and password
 */
function doLogin()
{
    // Get the username and password from the HTML fields
    var username = document.getElementById("loginName").value;
    var password = document.getElementById("loginPassword").value;

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
        document.getElementById("loginName").value = "";
        document.getElementById("loginPassword").value = "";

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
 * Log of a user's account
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
  hideOrShow("addContactDiv", true);
  hideOrShow( "accessUIDiv", false);
}

function showAccessUIDiv()
{
  hideOrShow("accessUIDiv", true);
  hideOrShow( "addContactDiv", false);
  unSelectContactsToDelete();

}

function addContact()
{
  var firstName = document.getElementById("firstNameNewEntry").value;
  var lastName = document.getElementById("lastNameNewEntry").value;
  var phoneNumber = document.getElementById("phoneNewEntry").value;
  var email = document.getElementById("emailNewEntry").value;
  if(!firstName | !lastName | !phoneNumber | !email)
  {
    console.log("must fill out all of the fields in order to add a contact");
    var errorMessage = document.getElementById("loginResult");
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
  var errorMessage = document.getElementById("loginResult");
  errorMessage.innerHTML = "";
  hideOrShow("addContactDiv", false);
  hideOrShow( "accessUIDiv", true);

  document.getElementById("firstNameNewEntry").value = "";
  document.getElementById("lastNameNewEntry").value = "";
  document.getElementById("emailNewEntry").value = "";
  document.getElementById("phoneNewEntry").value = "";

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
              fillTable();
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
                buildTableData(jsonObject.results);
                tableData = jsonObject.results;
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
  var errorMessage = document.getElementById("loginResult");
  errorMessage.innerHTML = "";
  hideOrShow("confirmDelete", false);
  hideOrShow("showDeleteMarks", true);

  // To prevent page refresh
  return false;
}

function searchContacts()
{
  var typedSearch = document.getElementById("searchText").value;
  var filteredData = tableData.filter(function (item) {
      return (stringContains(item.contactId, typedSearch) || stringContains(item.firstName, typedSearch) || stringContains(item.lastName, typedSearch) || stringContains(item.phoneNumber, typedSearch) || stringContains(item.emailAddress, typedSearch));
  });
  buildTableHeader();
  buildTableData(filteredData);
}


function selectContactsToDelete()
{
  hideOrShow("deleteHeader", true);
  hideOrShowByClass("deleteButton", true);
  hideOrShow("confirmDelete", true);
  hideOrShow("showDeleteMarks", false);

  // To prevent page refresh
  return false;
}
function unSelectContactsToDelete()
{
  hideOrShow("deleteHeader", false);
  hideOrShowByClass("deleteButton", false);
  hideOrShow("confirmDelete", false);
  hideOrShow("showDeleteMarks", true);
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

function stringContains(stringToCheck, substring)
{
    return stringToCheck.toLowerCase().indexOf(substring.toLowerCase()) != -1;
}
