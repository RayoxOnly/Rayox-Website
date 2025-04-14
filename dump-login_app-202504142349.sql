/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: login_app
-- ------------------------------------------------------
-- Server version	10.11.11-MariaDB-0+deb12u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `suggestions`
--

DROP TABLE IF EXISTS `suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `suggestion_text` text NOT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suggestions`
--

LOCK TABLES `suggestions` WRITE;
/*!40000 ALTER TABLE `suggestions` DISABLE KEYS */;
INSERT INTO `suggestions` VALUES
(1,1,'lorem ipsum sit amet','2025-04-13 13:05:59'),
(2,2,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eu libero vel libero ornare molestie at ac nunc. Donec pellentesque augue non ante porttitor tincidunt. Donec posuere dui dolor, in bibendum felis varius ut. Aliquam id cursus felis, hendrerit efficitur arcu. Ut venenatis, metus et fermentum lobortis, neque mi rhoncus erat, quis pellentesque leo justo ornare arcu. Duis molestie dignissim orci, id blandit ipsum suscipit at. Nullam pretium magna nec ante consectetur luctus. Maecenas consectetur accumsan neque, eu euismod dui pulvinar quis. Fusce ut elit orci.\r\n\r\nAliquam aliquet, libero non varius vestibulum, magna eros lobortis neque, sit amet molestie est enim non lorem. Aliquam tincidunt at purus et semper. Sed nec mattis mauris. Suspendisse potenti. Aliquam luctus mi lacinia, facilisis mi nec, pretium mauris. Aliquam et lacus et magna semper pulvinar in facilisis mauris. Cras ultrices efficitur eros vitae vestibulum. Curabitur in ante in erat consequat pellentesque. Suspendisse potenti. Nullam eleifend enim eu elementum varius. Donec consequat dolor sagittis egestas rutrum. Mauris faucibus odio vitae risus consectetur suscipit quis fermentum lacus. In nec nulla ipsum. Etiam ultrices mollis pellentesque. Mauris bibendum lacus nibh, vulputate posuere elit lacinia eu.\r\n\r\nCras mattis hendrerit metus, a mattis neque consectetur id. Morbi massa nibh, hendrerit placerat elementum nec, porta vitae ex. Vestibulum viverra, neque vel finibus rutrum, libero mauris tincidunt leo, nec euismod enim tellus id odio. Duis nec sollicitudin dolor, at ultrices nibh. In at turpis id dui hendrerit mattis nec id tortor. Duis lacinia ligula ac eros pulvinar pellentesque. Aliquam aliquam ipsum libero, sed blandit lectus volutpat sed. Morbi ut turpis sit amet quam commodo porttitor non id lacus.\r\n\r\nDonec vel nulla leo. Fusce tempus in odio at lobortis. Morbi volutpat consequat diam, hendrerit consequat quam pharetra eget. Duis dapibus ultricies massa, in mattis ipsum semper semper. Donec consequat non nibh sed lobortis. Integer quis turpis felis. Quisque neque mauris, iaculis nec sodales quis, pretium eget nunc. Phasellus lacinia felis ac erat hendrerit malesuada. Proin sodales, mauris sed tristique bibendum, sapien erat venenatis elit, eu rutrum nunc nisl at erat. Fusce porta, tortor egestas iaculis vestibulum, lorem mauris dictum odio, sit amet malesuada lacus nisi quis lorem. Mauris faucibus tellus vitae metus iaculis dictum. Proin tempor enim massa, sed tincidunt elit malesuada id.\r\n\r\nDuis rhoncus lacus at velit maximus malesuada. Quisque in commodo mi. Cras feugiat iaculis ex sed finibus. Nam placerat ante sit amet pharetra vulputate. Nullam porttitor dapibus neque, varius sodales lectus porta sit amet. Nunc vitae ligula id dui aliquet tincidunt ut eu dolor. Fusce bibendum orci ac ligula lobortis, suscipit accumsan sem euismod. Praesent et velit lacus. Aenean rutrum nisi et erat congue, in hendrerit turpis pretium. In nec interdum libero, in ullamcorper tortor. Interdum et malesuada fames ac ante ipsum primis in faucibus.','2025-04-13 13:09:53'),
(3,5,'bikin live view pengunjung kak rayokk (today, yesterday, this week, all time)','2025-04-14 04:57:03'),
(4,5,'Link promosi judi bisa gak bang rayox','2025-04-14 05:00:12'),
(5,5,'bg rayox\r\nkontol','2025-04-14 05:00:41'),
(6,5,'Fitur bagi tugas','2025-04-14 05:01:11');
/*!40000 ALTER TABLE `suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'test','$2y$10$Rsjn0x6VpvmNHrE/0bEwC.ewX4RpEqYuajpr7/0XqDsmEPL08mMFu','user','2025-04-13 12:54:37'),
(2,'RayhanAdmin','$2y$10$Qyf7Ye1z3rRlFtwatFl3OevorOQ2lmBTtoehfWZ6gO0rXxCTsJ2iW','admin','2025-04-13 13:06:57'),
(3,'Kontol ','$2y$10$b1Dpk.YVcHxDNHn/lb0UsOBBMRb8BTUBOKwQgXQl96qMBIdCV8aV.','user','2025-04-13 13:53:56'),
(5,'pekobb','$2y$10$NsGOgSzLuZDFj71BI.xGnuOwqDVM5nmBiiutbs31fVlWEUnwLxFky','user','2025-04-14 04:55:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'login_app'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-04-14 23:49:36
