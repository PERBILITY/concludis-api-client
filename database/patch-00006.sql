CREATE TABLE IF NOT EXISTS `tbl_local_board` (
    `source_id` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `board_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `external_id` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `meta` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
    PRIMARY KEY (`source_id`, `board_id`) USING BTREE,
    CONSTRAINT `meta` CHECK (json_valid(`meta`))
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_board` (
    `source_id` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `project_id` INT(11) UNSIGNED NOT NULL,
    `board_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `board_id`) USING BTREE,
    INDEX `idx_project` (`source_id`, `project_id`) USING BTREE,
    INDEX `idx_board` (`source_id`, `board_id`) USING BTREE,
    CONSTRAINT `fk_project_board_project` FOREIGN KEY (`source_id`, `project_id`)
    REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;