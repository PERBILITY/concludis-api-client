CREATE TABLE IF NOT EXISTS `tbl_local_company` (
     `source_id` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
     `company_id` INT(10) UNSIGNED NOT NULL,
     `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
     PRIMARY KEY (`source_id`, `company_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `tbl_project_company` (
    `source_id` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `project_id` INT(10) UNSIGNED NOT NULL,
    `company_id` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `company_id`) USING BTREE,
    INDEX `idx_project` (`source_id`, `project_id`) USING BTREE,
    INDEX `idx_company` (`source_id`, `company_id`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci;
