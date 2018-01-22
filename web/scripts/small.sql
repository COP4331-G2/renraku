# Create Users table
CREATE TABLE `innodb`.`Users`
(
    `ID` INT NOT NULL AUTO_INCREMENT,
    `Username` VARCHAR(50) NOT NULL DEFAULT '',
    `Password` VARCHAR(50) NOT NULL DEFAULT '',
    PRIMARY KEY (`ID`)
)
ENGINE = InnoDB;

# Create Contacts table
CREATE TABLE `innodb`.`Contacts`
(
    `ID` INT NOT NULL AUTO_INCREMENT,
    `FirstName` VARCHAR(50) NOT NULL DEFAULT '',
    `LastName` VARCHAR(50) DEFAULT '',
    `PhoneNumber` BIGINT,
    `EmailAddress` VARCHAR(50) NOT NULL DEFAULT '',
    `UserID` INT NOT NULL,
    PRIMARY KEY (`ID`),
    FOREIGN KEY (`UserID`) REFERENCES Users(`ID`)
)
ENGINE = InnoDB;

# Populate Users table with test users
insert into Users (Username,Password) VALUES ('username1','password');
insert into Users (Username,Password) VALUES ('username2','password');
insert into Users (Username,Password) VALUES ('username3','password');
insert into Users (Username,Password) VALUES ('username4','password');

# Populate Contacts table with test contacts
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first1','last','5555555555','person1@ucf.edu',1);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first2','last','5555555555','person2@ucf.edu',1);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first3','last','5555555555','person3@ucf.edu',2);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first4','last','5555555555','person4@ucf.edu',2);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first5','last','5555555555','person5@ucf.edu',3);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first6','last','5555555555','person6@ucf.edu',3);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first7','last','5555555555','person7@ucf.edu',4);
insert into Contacts (FirstName,LastName,PhoneNumber,EmailAddress,UserID) VALUES ('first8','last','5555555555','person8@ucf.edu',4);
