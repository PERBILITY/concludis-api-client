/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE = @@TIME_ZONE */;
/*!40103 SET TIME_ZONE = '+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0 */;

ALTER TABLE `tbl_project_ad_container` DROP FOREIGN KEY `fk_project_adcontainer_project`;
ALTER TABLE `tbl_project_ad_container`
    ADD CONSTRAINT `fk_project_adcontainer_project`
        FOREIGN KEY (`source_id`, `project_id`)
        REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;


ALTER TABLE `tbl_project_category` DROP FOREIGN KEY `fk_project_category_project`;
ALTER TABLE `tbl_project_category`
    ADD CONSTRAINT `fk_project_category_project`
        FOREIGN KEY (`source_id`, `project_id`)
            REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tbl_project_classification` DROP FOREIGN KEY `fk_project_classification_project`;
ALTER TABLE `tbl_project_classification`
    ADD CONSTRAINT `fk_project_classification_project`
        FOREIGN KEY (`source_id`, `project_id`)
            REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tbl_project_group` DROP FOREIGN KEY `FK_tbl_project_group_tbl_project`;
ALTER TABLE `tbl_project_group`
    ADD CONSTRAINT `fk_project_jobgroup_project`
        FOREIGN KEY (`source_id`, `project_id`)
            REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tbl_project_location` DROP FOREIGN KEY `fk_project_location_project`;
ALTER TABLE `tbl_project_location`
    ADD CONSTRAINT `fk_project_location_project`
        FOREIGN KEY (`source_id`, `project_id`)
            REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tbl_project_schedule` DROP FOREIGN KEY `fk_project_schedule_project`;
ALTER TABLE `tbl_project_schedule`
    ADD CONSTRAINT `fk_project_schedule_project`
        FOREIGN KEY (`source_id`, `project_id`)
            REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tbl_project_seniority` DROP FOREIGN KEY `fk_project_seniority_project`;
ALTER TABLE `tbl_project_seniority`
    ADD CONSTRAINT `fk_project_seniority_project`
        FOREIGN KEY (`source_id`, `project_id`)
            REFERENCES `tbl_project` (`source_id`, `project_id`) ON UPDATE NO ACTION ON DELETE CASCADE;

/*!40103 SET TIME_ZONE = IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE = IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS = IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES = IFNULL(@OLD_SQL_NOTES, 1) */;