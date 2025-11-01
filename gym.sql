-- Use your database
USE `Gym-management-system`;

-- Members table (already created)
CREATE TABLE IF NOT EXISTS `member_registration` (
    `MemberID` INT(11) NOT NULL AUTO_INCREMENT,
    `Name` VARCHAR(100) NOT NULL,
    `Age` INT(3) NOT NULL,
    `Contact` VARCHAR(15) NOT NULL,
    `Email` VARCHAR(100) NOT NULL UNIQUE,
    `Address` VARCHAR(255) NOT NULL,
    `Gender` ENUM('male','female') NOT NULL,
    `Password` VARCHAR(255) NOT NULL,
    `Role` ENUM('member','trainer','admin') NOT NULL DEFAULT 'member',
    `Created_At` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`MemberID`)
);

-- Membership plan details
CREATE TABLE IF NOT EXISTS `membership` (
    `MembershipID` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `MemberID` INT(11),
    `PlanType` VARCHAR(50),
    `StartDate` DATE,
    `EndDate` DATE,
    `NextPaymentDate` DATE,
    FOREIGN KEY (`MemberID`) REFERENCES `member_registration`(`MemberID`) ON DELETE CASCADE
);

-- Trainer assignment
CREATE TABLE IF NOT EXISTS `trainer_assignment` (
    `AssignmentID` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `MemberID` INT(11),
    `TrainerName` VARCHAR(100),
    `Specialization` VARCHAR(100),
    `SessionTime` VARCHAR(50),
    FOREIGN KEY (`MemberID`) REFERENCES `member_registration`(`MemberID`) ON DELETE CASCADE
);

-- Progress log
CREATE TABLE IF NOT EXISTS `progress_log` (
    `ProgressID` INT(11) AUTO_INCREMENT PRIMARY KEY,
    `MemberID` INT(11),
    `GoalType` VARCHAR(50),
    `ProgressPercent` INT(3),
    `Attendance` INT(3),
    `TotalSessions` INT(3),
    FOREIGN KEY (`MemberID`) REFERENCES `member_registration`(`MemberID`) ON DELETE CASCADE
);

-- Example data for one user
INSERT INTO membership (MemberID, PlanType, StartDate, EndDate, NextPaymentDate)
VALUES (1, 'Gold (6 Months)', '2025-07-15', '2026-01-15', '2026-01-15');

INSERT INTO trainer_assignment (MemberID, TrainerName, Specialization, SessionTime)
VALUES (1, 'Alex Johnson', 'Strength & Conditioning', '7:00 AM - 8:30 AM');

INSERT INTO progress_log (MemberID, GoalType, ProgressPercent, Attendance, TotalSessions)
VALUES (1, 'Weight Loss', 70, 18, 25);
