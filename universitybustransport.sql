-- University Bus Transport Database Export
-- Generated on: 2026-08-13 21:22:38

CREATE DATABASE IF NOT EXISTS `universitybustransport`;
USE `universitybustransport`;

DROP TABLE IF EXISTS `admin`;


CREATE TABLE `admin` (
  `AdminID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`AdminID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` VALUES("0","System Administrator","admin@university.edu","admin123","+1234567890");



DROP TABLE IF EXISTS `bus`;


CREATE TABLE `bus` (
  `BusID` int(11) NOT NULL AUTO_INCREMENT,
  `BusNumber` varchar(50) NOT NULL,
  `BusType` enum('Student','Faculty') NOT NULL,
  `Capacity` int(11) NOT NULL,
  `DriverID` int(11) NOT NULL,
  PRIMARY KEY (`BusID`),
  UNIQUE KEY `BusNumber` (`BusNumber`),
  KEY `DriverID` (`DriverID`),
  CONSTRAINT `bus_ibfk_1` FOREIGN KEY (`DriverID`) REFERENCES `driver` (`DriverID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bus` VALUES("1","EWU-S01","Student","50","4");
INSERT INTO `bus` VALUES("2","EWU-S02","Student","50","3");
INSERT INTO `bus` VALUES("3","EWU-F01","Faculty","30","1");
INSERT INTO `bus` VALUES("4","EWU-F02","Faculty","40","2");



DROP TABLE IF EXISTS `busschedule`;


CREATE TABLE `busschedule` (
  `ScheduleID` int(11) NOT NULL AUTO_INCREMENT,
  `BusID` int(11) NOT NULL,
  `RouteID` int(11) NOT NULL,
  `UniversityStartTime` varchar(50) NOT NULL,
  `LastStopStartTime` varchar(50) NOT NULL,
  PRIMARY KEY (`ScheduleID`),
  KEY `BusID` (`BusID`),
  KEY `RouteID` (`RouteID`),
  CONSTRAINT `busschedule_ibfk_1` FOREIGN KEY (`BusID`) REFERENCES `bus` (`BusID`),
  CONSTRAINT `busschedule_ibfk_2` FOREIGN KEY (`RouteID`) REFERENCES `route` (`RouteID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `busschedule` VALUES("1","1","1","5:00 PM","6:30 PM");
INSERT INTO `busschedule` VALUES("2","2","2","5:00 PM","06:30 PM");
INSERT INTO `busschedule` VALUES("3","3","3","04:00 PM","05:30 PM");
INSERT INTO `busschedule` VALUES("4","4","4","05:00 PM","07:00 PM");



DROP TABLE IF EXISTS `busstop`;


CREATE TABLE `busstop` (
  `StopID` int(11) NOT NULL AUTO_INCREMENT,
  `StopName` varchar(100) NOT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `RouteID` int(11) DEFAULT NULL,
  PRIMARY KEY (`StopID`),
  UNIQUE KEY `StopName` (`StopName`),
  KEY `fk_busstop_route` (`RouteID`),
  CONSTRAINT `fk_busstop_route` FOREIGN KEY (`RouteID`) REFERENCES `route` (`RouteID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `busstop` VALUES("1","Meradiya Stop","Meradiya Bazar / Main Road","1");
INSERT INTO `busstop` VALUES("2","Banasree Stop","Banasree Main Road / Block-B","1");
INSERT INTO `busstop` VALUES("3","Ideal School Stop","Ideal School Banasree Campus","1");
INSERT INTO `busstop` VALUES("4","Trimohoni Stop","Trimohoni Bridge / Junction","1");
INSERT INTO `busstop` VALUES("5","Mostomahazi Stop","Mostomahazi Area","1");
INSERT INTO `busstop` VALUES("6","Demra Stop","Demra Staff Quarter / Bus Stand","1");
INSERT INTO `busstop` VALUES("7","Rampura Bridge Stop","Rampura Bridge DIT Road","2");
INSERT INTO `busstop` VALUES("8","Khilgaon Stop","Khilgaon Flyover / Taltola","2");
INSERT INTO `busstop` VALUES("9","Bashabo Stop","Bashabo Crossing","2");
INSERT INTO `busstop` VALUES("10","Sayedabad Stop","Sayedabad Bus Terminal","2");
INSERT INTO `busstop` VALUES("11","Jatrabari Stop","Jatrabari Bus Stand","2");
INSERT INTO `busstop` VALUES("12","Badda Link Road Stop","Merul Badda Link Road","3");
INSERT INTO `busstop` VALUES("13","Notun Bazar Stop","Notun Bazar Gulshan Link Road","3");
INSERT INTO `busstop` VALUES("14","Gulshan-1 Stop","Gulshan-1 Circle","3");
INSERT INTO `busstop` VALUES("15","Gulshan-2 Stop","Gulshan-2 Circle","3");
INSERT INTO `busstop` VALUES("16","Mohakhali Stop","Mohakhali Bus Stand","4");
INSERT INTO `busstop` VALUES("17","Banani Stop","Banani Chairman Bari / Kakoli","4");
INSERT INTO `busstop` VALUES("18","Airport Stop","Hazrat Shahjalal Airport Gate","4");
INSERT INTO `busstop` VALUES("19","Uttara Stop","Uttara Sector-7 Rajlokkhi","4");



DROP TABLE IF EXISTS `driver`;


CREATE TABLE `driver` (
  `DriverID` int(11) NOT NULL AUTO_INCREMENT,
  `FullName` varchar(100) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `LicenseNumber` varchar(50) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DriverID`),
  UNIQUE KEY `Phone` (`Phone`),
  UNIQUE KEY `LicenseNumber` (`LicenseNumber`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `driver` VALUES("1","Md. Karim Hossain","+8801711000001","DL-DHK-2022-1045","Mirpur-11, Dhaka");
INSERT INTO `driver` VALUES("2","Md. Rahim Uddin","+8801711000002","DL-DHK-2021-0987","Uttara Sector-3, Dhaka");
INSERT INTO `driver` VALUES("3","Md. Jamal Sheikh","+8801711000003","DL-DHK-2023-1122","Mohammadpur, Dhaka");
INSERT INTO `driver` VALUES("4","Md. Faruk Ahmed","+8801711000004","DL-DHK-2020-0765","Badda, Dhaka");



DROP TABLE IF EXISTS `faculty`;


CREATE TABLE `faculty` (
  `FacultyID` varchar(20) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Department` varchar(100) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `StopID` int(11) DEFAULT NULL,
  PRIMARY KEY (`FacultyID`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Phone` (`Phone`),
  KEY `StopID` (`StopID`),
  CONSTRAINT `faculty_ibfk_1` FOREIGN KEY (`StopID`) REFERENCES `busstop` (`StopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `faculty` VALUES("FAC001","Dr. Robert Vance","Computer Science","faculty@university.edu","+1987654321","faculty123","Faculty Housing Block A",NULL);
INSERT INTO `faculty` VALUES("FAC002","Antu Chowdhury","CSE","antu.chowdhury@ewubd.edu","01872157298","12345",NULL,"14");



DROP TABLE IF EXISTS `route`;


CREATE TABLE `route` (
  `RouteID` int(11) NOT NULL AUTO_INCREMENT,
  `RouteName` varchar(100) NOT NULL,
  `StartLocation` varchar(100) NOT NULL,
  `EndLocation` varchar(100) NOT NULL,
  `Distance` decimal(5,2) DEFAULT NULL,
  `EstimatedDuration` int(11) DEFAULT NULL,
  PRIMARY KEY (`RouteID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `route` VALUES("1","Aftabnagar - Demra Route","EWU Campus, Aftabnagar","Demra Bus Stand","13.00","55");
INSERT INTO `route` VALUES("2","Aftabnagar - Jatrabari Route","EWU Campus, Aftabnagar","Jatrabari Bus Stand","7.00","30");
INSERT INTO `route` VALUES("3","Aftabnagar - Gulshan Route","Gulshan-2 Circle","Gulshan-2 Circle","6.00","25");
INSERT INTO `route` VALUES("4","Aftabnagar - Uttara Route","EWU Campus, Aftabnagar","Uttara Sector-7","22.00","65");



DROP TABLE IF EXISTS `semester`;


CREATE TABLE `semester` (
  `SemesterID` int(11) NOT NULL AUTO_INCREMENT,
  `SemesterName` varchar(50) NOT NULL,
  `DurationDays` int(11) DEFAULT 90,
  `TotalCredits` int(11) DEFAULT 90,
  PRIMARY KEY (`SemesterID`),
  UNIQUE KEY `SemesterName` (`SemesterName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;




DROP TABLE IF EXISTS `student`;


CREATE TABLE `student` (
  `StudentID` varchar(20) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Department` varchar(100) NOT NULL,
  `AcademicSemester` int(11) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `StopID` int(11) DEFAULT NULL,
  PRIMARY KEY (`StudentID`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Phone` (`Phone`),
  KEY `StopID` (`StopID`),
  CONSTRAINT `student_ibfk_1` FOREIGN KEY (`StopID`) REFERENCES `busstop` (`StopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `student` VALUES("ST001","annona","geb","3","annona956@university.edu","01787000776","12345","demra dhaka","4");
INSERT INTO `student` VALUES("ST002","masud bhuyan","CSE","7","masudbhuyan@ewubd.edu","01516539189","123456",NULL,"5");


