-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: event_management
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `event_categories`
--

DROP TABLE IF EXISTS `event_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_categories`
--

LOCK TABLES `event_categories` WRITE;
/*!40000 ALTER TABLE `event_categories` DISABLE KEYS */;
INSERT INTO `event_categories` VALUES (1,'Workshop','Technical and practical learning workshops'),(2,'Hackathon','Competitive software and technology events'),(3,'Seminar','Academic and professional knowledge sessions'),(4,'Cultural','Cultural and entertainment events'),(5,'Sports','Sports and athletic competitions');
/*!40000 ALTER TABLE `event_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_schedules`
--

DROP TABLE IF EXISTS `event_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_schedules` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `fk_schedule_event` (`event_id`),
  KEY `fk_schedule_venue` (`venue_id`),
  CONSTRAINT `fk_schedule_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_schedule_venue` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_schedule_time` CHECK (`end_time` > `start_time`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_schedules`
--

LOCK TABLES `event_schedules` WRITE;
/*!40000 ALTER TABLE `event_schedules` DISABLE KEYS */;
INSERT INTO `event_schedules` VALUES (1,1,2,'2026-10-15','10:00:00','13:00:00'),(2,2,1,'2026-10-17','09:00:00','18:00:00'),(3,3,3,'2026-10-20','11:00:00','14:00:00'),(4,4,1,'2026-10-25','17:00:00','21:00:00'),(5,5,5,'2026-11-02','08:00:00','17:00:00');
/*!40000 ALTER TABLE `event_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `registration_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_participants` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Upcoming',
  PRIMARY KEY (`event_id`),
  KEY `fk_event_category` (`category_id`),
  KEY `fk_event_organizer` (`organizer_id`),
  CONSTRAINT `fk_event_category` FOREIGN KEY (`category_id`) REFERENCES `event_categories` (`category_id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_event_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`organizer_id`) ON UPDATE CASCADE,
  CONSTRAINT `chk_event_fee` CHECK (`registration_fee` >= 0),
  CONSTRAINT `chk_max_participants` CHECK (`max_participants` > 0),
  CONSTRAINT `chk_event_status` CHECK (`status` in ('Upcoming','Ongoing','Completed','Cancelled'))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'AI and Machine Learning Workshop','Hands-on workshop covering fundamentals of AI and ML.',1,1,0.00,100,'Upcoming'),(2,'Woxsen Tech Hackathon 2026','24-hour technology and innovation hackathon.',2,1,200.00,150,'Upcoming'),(3,'Future of Artificial Intelligence Seminar','Expert seminar discussing emerging AI technologies.',3,4,0.00,200,'Upcoming'),(4,'Woxsen Cultural Night 2026','Annual cultural and entertainment celebration.',4,2,100.00,500,'Upcoming'),(5,'Inter-College Sports Meet 2026','Annual university sports competition.',5,3,50.00,300,'Upcoming');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizers`
--

DROP TABLE IF EXISTS `organizers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizers` (
  `organizer_id` int(11) NOT NULL AUTO_INCREMENT,
  `organizer_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`organizer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizers`
--

LOCK TABLES `organizers` WRITE;
/*!40000 ALTER TABLE `organizers` DISABLE KEYS */;
INSERT INTO `organizers` VALUES (1,'Tech Club','techclub@woxsen.edu.in','9876500001','Computer Science'),(2,'Cultural Club','culturalclub@woxsen.edu.in','9876500002','Student Affairs'),(3,'Sports Club','sportsclub@woxsen.edu.in','9876500003','Sports Department'),(4,'Innovation Cell','innovation@woxsen.edu.in','9876500004','Innovation and Entrepreneurship'),(5,'AIESEC Woxsen','aiesec@woxsen.edu.in','9876500005','Student Organizations');
/*!40000 ALTER TABLE `organizers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participants`
--

DROP TABLE IF EXISTS `participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participants` (
  `participant_id` int(11) NOT NULL AUTO_INCREMENT,
  `participant_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `college` varchar(150) NOT NULL,
  PRIMARY KEY (`participant_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participants`
--

LOCK TABLES `participants` WRITE;
/*!40000 ALTER TABLE `participants` DISABLE KEYS */;
INSERT INTO `participants` VALUES (1,'Rahul Sharma','rahul.sharma@gmail.com','9876510001','Woxsen University'),(2,'Ananya Singh','ananya.singh@gmail.com','9876510002','Woxsen University'),(3,'Arjun Kumar','arjun.kumar@gmail.com','9876510003','Woxsen University'),(4,'Priya Mehta','priya.mehta@gmail.com','9876510004','Woxsen University'),(5,'Rohan Verma','rohan.verma@gmail.com','9876510005','Woxsen University');
/*!40000 ALTER TABLE `participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `registration_date` date NOT NULL DEFAULT curdate(),
  `registration_status` varchar(20) NOT NULL DEFAULT 'Confirmed',
  `payment_status` varchar(20) NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `uq_event_participant` (`event_id`,`participant_id`),
  KEY `fk_registration_participant` (`participant_id`),
  CONSTRAINT `fk_registration_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registration_participant` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`participant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_registration_status` CHECK (`registration_status` in ('Confirmed','Pending','Cancelled')),
  CONSTRAINT `chk_payment_status` CHECK (`payment_status` in ('Paid','Pending','Refunded','Not Required'))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registrations`
--

LOCK TABLES `registrations` WRITE;
/*!40000 ALTER TABLE `registrations` DISABLE KEYS */;
INSERT INTO `registrations` VALUES (1,1,1,'2026-10-01','Confirmed','Not Required'),(2,1,2,'2026-10-02','Confirmed','Not Required'),(3,2,3,'2026-10-02','Confirmed','Paid'),(4,3,4,'2026-10-03','Confirmed','Not Required'),(5,4,5,'2026-10-04','Confirmed','Paid');
/*!40000 ALTER TABLE `registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venues`
--

DROP TABLE IF EXISTS `venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `venues` (
  `venue_id` int(11) NOT NULL AUTO_INCREMENT,
  `venue_name` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `capacity` int(11) NOT NULL,
  `venue_type` varchar(50) NOT NULL,
  PRIMARY KEY (`venue_id`),
  UNIQUE KEY `venue_name` (`venue_name`),
  CONSTRAINT `chk_venue_capacity` CHECK (`capacity` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venues`
--

LOCK TABLES `venues` WRITE;
/*!40000 ALTER TABLE `venues` DISABLE KEYS */;
INSERT INTO `venues` VALUES (1,'Main Auditorium','Main Academic Block',1000,'Auditorium'),(2,'Seminar Hall A','Academic Block A',200,'Seminar Hall'),(3,'Seminar Hall B','Academic Block B',150,'Seminar Hall'),(4,'CSE Lab 1','CSE Block',60,'Computer Lab'),(5,'University Sports Ground','Sports Complex',500,'Open Ground');
/*!40000 ALTER TABLE `venues` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-06 13:19:48
