var urlBase = "https://renrokusmall.herokuapp.com/LAMPAPI";
var extension = "php";

var userId = 0;
var firstName = "";
var lastName = "";

function doLogin()
{
    userId = 0;
    firstName = "";
    lastName = "";

    var login = document.getElementById("loginName").value;
    var password = document.getElementById("loginPassword").value;

    document.getElementById("loginResult").innerHTML = "";

    var jsonPayload = '{"login" : "' + login + '", "password" : "' + password + '"}';
    var url = urlBase + '/Login.' + extension;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, false);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.send(jsonPayload);
        var jsonObject = JSON.parse( xhr.responseText );

        userId = jsonObject.id;

        if( userId < 1 )
        {
            document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
            return;
        }

        firstName = jsonObject.firstName;
        lastName = jsonObject.lastName;

        document.getElementById("userName").innerHTML = firstName + " " + lastName;

        document.getElementById("loginName").value = "";
        document.getElementById("loginPassword").value = "";

        hideOrShow( "loggedInDiv", true);
        hideOrShow( "accessUIDiv", true);
        hideOrShow( "loginDiv", false);
        fillTable();
    }
    catch(err)
    {
        document.getElementById("loginResult").innerHTML = err.message;
    }

}
function fillTable()
{
  var urlBase = "https://renrokusmall.herokuapp.com/LAMPAPI";
  var extension = "php";
  
  var id = 1;
  var jsonPayload = '{"userID" : "' + id + '"}';
  var url = urlBase + '/GetContacts.' + extension;
  console.log('hey there im in the function');
  var xhr = new XMLHttpRequest();
  xhr.open("POST", url, true);
  xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
  console.log('hey there im in the functio12n');
  try
  {
      xhr.onreadystatechange = function()
      {
        console.log('hey there im in the function12425');
          if (this.readyState == 4 && this.status == 200)
          {
            console.log('hey there im in the function1242533');
              var jsonObject = JSON.parse( xhr.responseText );
              buildTableHeader();
              buildTableData(jsonObject);
          }
      };
      console.log('hey there im in the function12555');
      xhr.send(jsonPayload);
      console.log('hey there im in the function1234');
  }
  catch(err)
  {
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
function doLogout()
{
    userId = 0;
    firstName = "";
    lastName = "";

    hideOrShow( "loggedInDiv", false);
    hideOrShow( "accessUIDiv", false);
    hideOrShow( "loginDiv", true);
}

function hideOrShow( elementId, showState )
{
    var vis = "visible";
    var dis = "block";
    if( !showState )
    {
        vis = "hidden";
        dis = "none";
    }

    document.getElementById( elementId ).style.visibility = vis;
    document.getElementById( elementId ).style.display = dis;
}

function addColor()
{
    var newColor = document.getElementById("colorText").value;
    document.getElementById("colorAddResult").innerHTML = "";

    var jsonPayload = '{"color" : "' + newColor + '", "userId" : ' + userId + '}';
    var url = urlBase + '/AddColor.' + extension;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function()
        {
            if (this.readyState == 4 && this.status == 200)
            {
                document.getElementById("colorAddResult").innerHTML = "Color has been added";
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        document.getElementById("colorAddResult").innerHTML = err.message;
    }

}

function searchColor()
{
    var srch = document.getElementById("searchText").value;
    document.getElementById("colorSearchResult").innerHTML = "";

    var colorList = document.getElementById("colorList");
    colorList.innerHTML = "";

    var jsonPayload = '{"search" : "' + srch + '"}';
    var url = urlBase + '/SearchColors.' + extension;

    var xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
    try
    {
        xhr.onreadystatechange = function()
        {
            if (this.readyState == 4 && this.status == 200)
            {
                hideOrShow( "colorList", true );

                document.getElementById("colorSearchResult").innerHTML = "Color(s) has been retrieved";
                var jsonObject = JSON.parse( xhr.responseText );

                var i;
                for( i=0; i<jsonObject.results.length; i++ )
                {
                    var opt = document.createElement("option");
                    opt.text = jsonObject.results[i];
                    opt.value = "";
                    colorList.options.add(opt);
                }
            }
        };
        xhr.send(jsonPayload);
    }
    catch(err)
    {
        document.getElementById("colorSearchResult").innerHTML = err.message;
    }

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
  for(i = 0; i < data.results.length; i++)
  {
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
