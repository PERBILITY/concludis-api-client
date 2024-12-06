/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE = @@TIME_ZONE */;
/*!40103 SET TIME_ZONE = '+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0 */;


CREATE TABLE IF NOT EXISTS `tbl_global_category`
(
    `global_id` int(11)      NOT NULL,
    `locale`    varchar(5)   NOT NULL DEFAULT 'de_DE',
    `name`      varchar(255) NOT NULL,
    PRIMARY KEY (`global_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_global_classification`
(
    `global_id` int(11)      NOT NULL,
    `locale`    varchar(5)   NOT NULL DEFAULT 'de_DE',
    `name`      varchar(255) NOT NULL,
    PRIMARY KEY (`global_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_global_geo`
(
    `global_id`      int(10) unsigned NOT NULL AUTO_INCREMENT,
    `country_code`   varchar(2)       NOT NULL,
    `postal_code`    varchar(20)      NOT NULL,
    `place_name`     varchar(180)     NOT NULL,
    `state_name`     varchar(100)     NOT NULL,
    `state_code`     varchar(20)      NOT NULL,
    `province_name`  varchar(100)     NOT NULL,
    `province_code`  varchar(20)      NOT NULL,
    `community_name` varchar(100)     NOT NULL,
    `community_code` varchar(20)      NOT NULL,
    `latitude`       decimal(10, 6)   NOT NULL COMMENT 'wgs84',
    `longitude`      decimal(10, 6)   NOT NULL COMMENT 'wgs84',
    `accuracy`       tinyint(1)       NOT NULL,
    PRIMARY KEY (`global_id`),
    KEY `idx_country_code` (`country_code`),
    KEY `idx_postal_code` (`postal_code`),
    KEY `idx_country_postal` (`country_code`, `postal_code`),
    KEY `idx_lat_lon` (`latitude`, `longitude`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci COMMENT ='geonames.org postal code database';

CREATE TABLE IF NOT EXISTS `tbl_global_occupation`
(
    `global_id` int(11)      NOT NULL,
    `name`      varchar(255) NOT NULL,
    PRIMARY KEY (`global_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_global_schedule`
(
    `global_id` int(11)      NOT NULL,
    `locale`    varchar(5)   NOT NULL DEFAULT 'de_DE',
    `name`      varchar(255) NOT NULL,
    PRIMARY KEY (`global_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_global_seniority`
(
    `global_id` int(11)      NOT NULL,
    `locale`    varchar(5)   NOT NULL DEFAULT 'de_DE',
    `name`      varchar(255) NOT NULL,
    PRIMARY KEY (`global_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_i18n`
(
    `model`       varchar(96) NOT NULL,
    `field`       varchar(96) NOT NULL,
    `key`         varchar(96) NOT NULL,
    `locale`      varchar(5)  NOT NULL,
    `translation` mediumtext  NOT NULL,
    PRIMARY KEY (`model`, `field`, `key`, `locale`) USING BTREE,
    KEY `FAST_TRANSLATION` (`model`, `key`) USING BTREE,
    KEY `FAST_TRANSLATION2` (`model`, `field`, `key`) USING BTREE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_board`
(
    `source_id`      varchar(255)     NOT NULL,
    `board_id`       int(11) unsigned NOT NULL,
    `name`           varchar(255)                                       DEFAULT NULL,
    `external_id`    varchar(255)                                       DEFAULT NULL,
    `extended_props` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`source_id`, `board_id`) USING BTREE,
    CONSTRAINT `extended_props` CHECK (json_valid(`extended_props`))
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_category`
(
    `source_id`          varchar(255)     NOT NULL,
    `category_id`        int(10) unsigned NOT NULL,
    `global_category_id` int(10) unsigned NOT NULL,
    `locale`             varchar(5) DEFAULT NULL,
    `name`               varchar(255)     NOT NULL,
    PRIMARY KEY (`source_id`, `category_id`),
    KEY `idx_global_category` (`global_category_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_classification`
(
    `source_id`                varchar(255)     NOT NULL,
    `classification_id`        int(10) unsigned NOT NULL,
    `global_classification_id` int(10) unsigned NOT NULL,
    `locale`                   varchar(5) DEFAULT NULL,
    `name`                     varchar(255)     NOT NULL,
    PRIMARY KEY (`source_id`, `classification_id`),
    KEY `idx_global_classification` (`global_classification_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_company`
(
    `source_id`  varchar(255)     NOT NULL,
    `company_id` int(10) unsigned NOT NULL,
    `name`       varchar(255) DEFAULT NULL,
    PRIMARY KEY (`source_id`, `company_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_group`
(
    `source_id` varchar(255)     NOT NULL,
    `group_id`  int(10) unsigned NOT NULL,
    `group_key` int(10) unsigned NOT NULL,
    `locale`    varchar(5)   DEFAULT NULL,
    `name`      varchar(255) DEFAULT NULL,
    PRIMARY KEY (`source_id`, `group_id`, `group_key`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_location`
(
    `source_id`         varchar(255)     NOT NULL,
    `location_id`       int(10) unsigned NOT NULL,
    `name`              varchar(255)     DEFAULT NULL,
    `country_code`      char(2)          NOT NULL,
    `postal_code`       varchar(255)     NOT NULL,
    `locality`          varchar(255)     NOT NULL,
    `address`           varchar(255)     NOT NULL,
    `external_id`       varchar(255)     DEFAULT NULL,
    `region_id`         int(10) unsigned DEFAULT NULL,
    `custom_text1`      varchar(255)     NOT NULL,
    `custom_text2`      varchar(255)     NOT NULL,
    `custom_text3`      varchar(255)     NOT NULL,
    `lat`               decimal(7, 5)    DEFAULT NULL,
    `lon`               decimal(8, 5)    DEFAULT NULL,
    `geocoding_source`  TINYINT UNSIGNED NOT NULL DEFAULT '0',
    PRIMARY KEY (`source_id`, `location_id`),
    KEY `idx_geo` (`country_code`, `postal_code`),
    KEY `idx_region` (`region_id`),
    KEY `idx_external` (`external_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_occupation`
(
    `source_id`            varchar(255)     NOT NULL,
    `occupation_id`        int(10) unsigned NOT NULL,
    `global_occupation_id` int(10) unsigned NOT NULL,
    `name`                 varchar(255)     NOT NULL,
    PRIMARY KEY (`source_id`, `occupation_id`),
    KEY `idx_global_occupation_id` (`global_occupation_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_region`
(
    `source_id` varchar(255)     NOT NULL,
    `region_id` int(11) unsigned NOT NULL,
    `name`      varchar(255)     NOT NULL,
    PRIMARY KEY (`source_id`, `region_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_schedule`
(
    `source_id`          varchar(255)     NOT NULL,
    `schedule_id`        int(10) unsigned NOT NULL,
    `global_schedule_id` int(10) unsigned NOT NULL,
    `locale`             varchar(5) DEFAULT NULL,
    `name`               varchar(255)     NOT NULL,
    PRIMARY KEY (`source_id`, `schedule_id`),
    KEY `idx_global_schedule` (`global_schedule_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_local_seniority`
(
    `source_id`           varchar(255)     NOT NULL,
    `seniority_id`        int(10) unsigned NOT NULL,
    `global_seniority_id` int(10) unsigned NOT NULL,
    `locale`              varchar(5) DEFAULT NULL,
    `name`                varchar(255)     NOT NULL,
    PRIMARY KEY (`source_id`, `seniority_id`),
    KEY `idx_global_seniority` (`global_seniority_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project`
(
    `source_id`  varchar(255)     NOT NULL,
    `project_id` int(11) unsigned NOT NULL,
    `data`       longtext         NOT NULL,
    `date_from_internal` DATE NULL DEFAULT NULL,
    `date_from_public`  DATE NULL DEFAULT NULL,
    `listed` tinyint signed NOT NULL DEFAULT '1',
    `unsolicited` tinyint signed NOT NULL DEFAULT '0',
    `published_internal` tinyint(1) unsigned NOT NULL DEFAULT '0',
    `published_public` tinyint(1) unsigned NOT NULL DEFAULT '1',
    `lastupdate` datetime         NOT NULL,
    `priority` tinyint signed NOT NULL DEFAULT '0',
    PRIMARY KEY (`source_id`, `project_id`),
    KEY `idx_date_from_public` (`date_from_public`),
    KEY `idx_date_from_internal` (`date_from_internal`),
    KEY `idx_published_internal` (`published_internal`),
    KEY `idx_published_public` (`published_public`),
    KEY `idx_priority` (`priority`),
    KEY `idx_listed` (`listed`),
    KEY `idx_unsolicited` (`unsolicited`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_ad_container`
(
    `source_id`        varchar(255)         NOT NULL,
    `project_id`       int(10) unsigned     NOT NULL,
    `datafield_id`     int(10) unsigned     NOT NULL,
    `locale`           varchar(5)           NOT NULL DEFAULT 'de_DE',
    `type`             varchar(255)                  DEFAULT NULL,
    `sortorder`        smallint(5) unsigned NOT NULL DEFAULT 0,
    `container_type`   varchar(255)         NOT NULL,
    `content_external` mediumtext           NOT NULL,
    `content_internal` mediumtext           NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `datafield_id`, `locale`) USING BTREE,
    KEY `idx_locale` (`locale`),
    FULLTEXT INDEX `fulltext_content_external` (`content_external`),
    FULLTEXT INDEX `fulltext_content_internal` (`content_internal`),
    CONSTRAINT `fk_project_adcontainer_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_board`
(
    `source_id`  varchar(255)     NOT NULL,
    `project_id` int(11) unsigned NOT NULL,
    `board_id`   int(11) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `board_id`) USING BTREE,
    KEY `idx_project` (`source_id`, `project_id`) USING BTREE,
    KEY `idx_board` (`source_id`, `board_id`) USING BTREE,
    CONSTRAINT `fk_project_board_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_category`
(
    `source_id`     varchar(255)     NOT NULL,
    `project_id`    int(10) unsigned NOT NULL,
    `category_id`   int(10) unsigned NOT NULL,
    `occupation_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `category_id`, `occupation_id`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_category` (`source_id`, `category_id`),
    KEY `idx_occupation` (`source_id`, `occupation_id`),
    CONSTRAINT `fk_project_category_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_classification`
(
    `source_id`         varchar(255)     NOT NULL,
    `project_id`        int(10) unsigned NOT NULL,
    `classification_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `classification_id`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_classification` (`source_id`, `classification_id`),
    CONSTRAINT `fk_project_classification_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_company`
(
    `source_id`  varchar(255)     NOT NULL,
    `project_id` int(10) unsigned NOT NULL,
    `company_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `company_id`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_company` (`source_id`, `company_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_group`
(
    `source_id`  varchar(255)     NOT NULL,
    `project_id` int(10) unsigned NOT NULL,
    `group_id`   int(10) unsigned NOT NULL,
    `group_key`  int(10) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `group_id`, `group_key`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_group` (`source_id`, `group_id`, `group_key`),
    CONSTRAINT `fk_project_jobgroup_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_location`
(
    `source_id`   varchar(255)     NOT NULL,
    `project_id`  int(10) unsigned NOT NULL,
    `location_id` int(10) unsigned NOT NULL,
    `map_data`    longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`map_data`)),
    PRIMARY KEY (`source_id`, `project_id`, `location_id`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_location` (`source_id`, `location_id`),
    CONSTRAINT `fk_project_location_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_schedule`
(
    `source_id`   varchar(255)     NOT NULL,
    `project_id`  int(10) unsigned NOT NULL,
    `schedule_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `schedule_id`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_schedule` (`source_id`, `schedule_id`),
    CONSTRAINT `fk_project_schedule_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_project_seniority`
(
    `source_id`    varchar(255)     NOT NULL,
    `project_id`   int(10) unsigned NOT NULL,
    `seniority_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`source_id`, `project_id`, `seniority_id`),
    KEY `idx_project` (`source_id`, `project_id`),
    KEY `idx_seniority` (`source_id`, `seniority_id`),
    CONSTRAINT `fk_project_seniority_project` FOREIGN KEY (`source_id`, `project_id`) REFERENCES `tbl_project` (`source_id`, `project_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_setup`
(
    `key`   varchar(255) NOT NULL,
    `value` mediumtext   NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_cache` (
    `key` varchar(50) NOT NULL,
    `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
    PRIMARY KEY (`key`)
) ENGINE = InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE = utf8mb4_unicode_ci;

INSERT INTO `tbl_setup` (`key`, `value`)
VALUES ('db_version', '{"revision":17}');

/*!40103 SET TIME_ZONE = IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE = IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS = IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES = IFNULL(@OLD_SQL_NOTES, 1) */;
