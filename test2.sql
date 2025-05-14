-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 03 فبراير 2025 الساعة 16:47
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test2`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(10) NOT NULL,
  `JobTitle` varchar(100) DEFAULT NULL,
  `Adm_Email` varchar(255) DEFAULT NULL,
  `Adm_Phone` varchar(15) DEFAULT NULL,
  `Adm_FirstName` varchar(100) DEFAULT NULL,
  `Adm_MiddleName` varchar(100) DEFAULT NULL,
  `Adm_LastName` varchar(100) DEFAULT NULL,
  `Adm_Password` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(10) NOT NULL,
  `CUS_Phone` varchar(15) DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `Cus_Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `event`
--

CREATE TABLE `event` (
  `EventID` int(10) NOT NULL,
  `Description` text DEFAULT NULL,
  `EventDateTime` datetime DEFAULT NULL,
  `EventType` varchar(255) DEFAULT NULL,
  `Classes` text DEFAULT NULL,
  `FacilityID` int(10) DEFAULT NULL,
  `OrganizerID` int(10) DEFAULT NULL,
  `BannerImage` text DEFAULT NULL,
  `Status` text DEFAULT 'Pending',
  `EventName` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `eventorganizer`
--

CREATE TABLE `eventorganizer` (
  `OrganizerID` int(10) NOT NULL,
  `Eo_FirstName` varchar(100) DEFAULT NULL,
  `Eo_MiddleName` varchar(100) DEFAULT NULL,
  `Eo_LastName` varchar(100) DEFAULT NULL,
  `Eo_Email` varchar(255) DEFAULT NULL,
  `Eo_Phone` varchar(15) DEFAULT NULL,
  `Eo_Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `facility`
--

CREATE TABLE `facility` (
  `FacilityID` int(10) NOT NULL,
  `FacilityName` varchar(255) NOT NULL,
  `Country` varchar(100) NOT NULL,
  `Region` varchar(100) NOT NULL,
  `City` varchar(100) NOT NULL,
  `GMLocationLink` varchar(255) DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Description` text DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `Amenities` text DEFAULT NULL,
  `FacilityImages` varchar(255) DEFAULT NULL,
  `BannerImage` varchar(255) DEFAULT NULL,
  `OwnerID` int(10) NOT NULL,
  `Status` varchar(50) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `facilityowner`
--

CREATE TABLE `facilityowner` (
  `OwnerID` int(10) NOT NULL,
  `Fo_Phone` varchar(15) DEFAULT NULL,
  `Fo_Email` varchar(255) DEFAULT NULL,
  `Fo_FirstName` varchar(100) DEFAULT NULL,
  `Fo_MiddleName` varchar(100) DEFAULT NULL,
  `Fo_LastName` varchar(100) DEFAULT NULL,
  `Fo_Password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `ticket`
--

CREATE TABLE `ticket` (
  `TicketID` int(11) NOT NULL,
  `EventID` int(11) DEFAULT NULL,
  `EventDate` datetime DEFAULT current_timestamp(),
  `EventType` varchar(255) DEFAULT NULL,
  `Class` varchar(255) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Price` int(11) DEFAULT NULL,
  `Status` text NOT NULL DEFAULT 'Valid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`EventID`),
  ADD KEY `FacilityID` (`FacilityID`),
  ADD KEY `OrganizerID` (`OrganizerID`);

--
-- Indexes for table `eventorganizer`
--
ALTER TABLE `eventorganizer`
  ADD PRIMARY KEY (`OrganizerID`);

--
-- Indexes for table `facility`
--
ALTER TABLE `facility`
  ADD PRIMARY KEY (`FacilityID`),
  ADD KEY `fk_facility_owner` (`OwnerID`);

--
-- Indexes for table `facilityowner`
--
ALTER TABLE `facilityowner`
  ADD PRIMARY KEY (`OwnerID`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`TicketID`),
  ADD KEY `EventID` (`EventID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `CustomerID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `EventID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eventorganizer`
--
ALTER TABLE `eventorganizer`
  MODIFY `OrganizerID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facility`
--
ALTER TABLE `facility`
  MODIFY `FacilityID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilityowner`
--
ALTER TABLE `facilityowner`
  MODIFY `OwnerID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `TicketID` int(11) NOT NULL AUTO_INCREMENT;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `FacilityID` FOREIGN KEY (`FacilityID`) REFERENCES `facility` (`FacilityID`),
  ADD CONSTRAINT `OrganizerID` FOREIGN KEY (`OrganizerID`) REFERENCES `eventorganizer` (`OrganizerID`);

--
-- قيود الجداول `facility`
--
ALTER TABLE `facility`
  ADD CONSTRAINT `fk_facility_owner` FOREIGN KEY (`OwnerID`) REFERENCES `facilityowner` (`OwnerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- قيود الجداول `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`),
  ADD CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
