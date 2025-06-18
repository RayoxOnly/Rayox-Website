-- Add activity_logs table for security monitoring
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for anonymous activities',
  `action` varchar(100) NOT NULL COMMENT 'Action performed (login, logout, registration, etc.)',
  `details` text DEFAULT NULL COMMENT 'Additional details about the action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of the user',
  `user_agent` text DEFAULT NULL COMMENT 'User agent string',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log of user activities for security monitoring';

-- Add failed_login_attempts table for brute force protection
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_username_ip` (`username`, `ip_address`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Track failed login attempts for brute force protection';

-- Add user_sessions table for better session management
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Track user sessions for security';

-- Add indexes to existing tables for better performance
ALTER TABLE `users` ADD INDEX `idx_username` (`username`);
ALTER TABLE `users` ADD INDEX `idx_role` (`role`);
ALTER TABLE `users` ADD INDEX `idx_created_at` (`created_at`);

ALTER TABLE `suggestions` ADD INDEX `idx_submitted_at` (`submitted_at`);

-- Clean up old failed login attempts (older than 24 hours)
DELETE FROM `failed_login_attempts` WHERE `attempt_time` < DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Clean up old sessions (older than 24 hours)
DELETE FROM `user_sessions` WHERE `last_activity` < DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Clean up old activity logs (older than 90 days)
DELETE FROM `activity_logs` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 90 DAY); 