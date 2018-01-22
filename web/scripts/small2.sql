# Create Users table
CREATE TABLE small.Users
(
    id           INT NOT NULL AUTO_INCREMENT,
    username     VARCHAR(50) NOT NULL DEFAULT '',
    password     VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY  (id)
);

# Create Contacts table
CREATE TABLE small.Contacts
(
    id           INT NOT NULL AUTO_INCREMENT,
    firstName    VARCHAR(50) NOT NULL DEFAULT '',
    lastName     VARCHAR(50) DEFAULT '',
    phoneNumber  BIGINT,
    emailAddress VARCHAR(50) NOT NULL DEFAULT '',
    userID       INT NOT NULL,
    PRIMARY KEY  (id),
    FOREIGN KEY  (userID) REFERENCES users(id)
);

# Populate Users table with test users
insert into Users (Username,Password) VALUES ('username1','password');
insert into Users (Username,Password) VALUES ('username2','password');
insert into Users (Username,Password) VALUES ('username3','password');
insert into Users (Username,Password) VALUES ('username4','password');

# Populate Contacts table with test contacts
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first1','last','5555555555','person1@ucf.edu',1);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first2','last','5555555555','person2@ucf.edu',1);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first3','last','5555555555','person3@ucf.edu',2);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first4','last','5555555555','person4@ucf.edu',2);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first5','last','5555555555','person5@ucf.edu',3);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first6','last','5555555555','person6@ucf.edu',3);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first7','last','5555555555','person7@ucf.edu',4);
insert into Contacts (firstName,lastName,phoneNumber,emailAddress,userID) VALUES ('first8','last','5555555555','person8@ucf.edu',4);
