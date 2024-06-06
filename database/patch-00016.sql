ALTER TABLE `tbl_local_location`
    ADD COLUMN IF NOT EXISTS `address` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `locality`,
    ADD COLUMN IF NOT EXISTS `geocoding_source` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `lon`;

CREATE TABLE IF NOT EXISTS `tbl_cache` (
   `key` varchar(50) NOT NULL,
   `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
   PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
