SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `car` (
  `PlateNumber` varchar(20) NOT NULL,
  `OwnerName` varchar(100) NOT NULL,
  `CarModel` varchar(50) DEFAULT NULL,
  `Color` varchar(30) DEFAULT NULL,
  `RegisteredDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `PaymentMethod` enum('Cash','Mobile Money','Card') NOT NULL,
  `TransactionRef` varchar(100) DEFAULT NULL,
  `PaymentStatus` enum('Completed','Pending') DEFAULT 'Completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `servicepackage` (
  `PackageID` int(11) NOT NULL,
  `PackageName` varchar(50) NOT NULL,
  `PackageDescription` text DEFAULT NULL,
  `PackagePrice` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `servicepackage` (`PackageID`, `PackageName`, `PackageDescription`, `PackagePrice`) VALUES
(1, 'Basic Wash', 'Body wash and drying', 5000),
(2, 'Classic Wash', 'Body wash, interior vacuum, and tire shine', 10000),
(3, 'Premium Wash', 'Full detail: Engine, Interior, Body, and Wax', 20000);

CREATE TABLE `servicerecord` (
  `RecordID` int(11) NOT NULL,
  `PlateNumber` varchar(20) DEFAULT NULL,
  `PackageID` int(11) DEFAULT NULL,
  `PaymentID` int(11) DEFAULT NULL,
  `AmountPaid` int(11) NOT NULL,
  `ServiceDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`UserID`, `Username`, `Password`) VALUES
(1, 'admin', 'admin123');

ALTER TABLE `car`
  ADD PRIMARY KEY (`PlateNumber`);

ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`);

ALTER TABLE `servicepackage`
  ADD PRIMARY KEY (`PackageID`);

ALTER TABLE `servicerecord`
  ADD PRIMARY KEY (`RecordID`),
  ADD KEY `PlateNumber` (`PlateNumber`),
  ADD KEY `PackageID` (`PackageID`),
  ADD KEY `PaymentID` (`PaymentID`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`);

ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `servicepackage`
  MODIFY `PackageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `servicerecord`
  MODIFY `RecordID` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `servicerecord`
  ADD CONSTRAINT `servicerecord_ibfk_1` FOREIGN KEY (`PlateNumber`) REFERENCES `car` (`PlateNumber`) ON UPDATE CASCADE,
  ADD CONSTRAINT `servicerecord_ibfk_2` FOREIGN KEY (`PackageID`) REFERENCES `servicepackage` (`PackageID`),
  ADD CONSTRAINT `servicerecord_ibfk_3` FOREIGN KEY (`PaymentID`) REFERENCES `payment` (`PaymentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;