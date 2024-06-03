CREATE TABLE IF NOT EXISTS `tbl_local_group` (
   `source_id` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
   `group_id` INT(10) UNSIGNED NOT NULL,
   `group_key` INT(10) UNSIGNED NOT NULL,
   `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
   PRIMARY KEY (`source_id`, `group_id`, `group_key`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_group` (
    `source_id` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `project_id` INT(10) UNSIGNED NOT NULL,
    `group_id` INT(10) UNSIGNED NOT NULL,
    `group_key` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `group_id`, `group_key`) USING BTREE,
    INDEX `idx_project` (`source_id`, `project_id`) USING BTREE,
    INDEX `idx_group` (`source_id`, `group_id`, `group_key`) USING BTREE,
    CONSTRAINT `FK_tbl_project_group_tbl_project` FOREIGN KEY (`source_id`, `project_id`)
        REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci;
