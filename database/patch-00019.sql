ALTER TABLE `tbl_project`
    ADD COLUMN IF NOT EXISTS `listed` TINYINT SIGNED NOT NULL DEFAULT '1' AFTER `date_from_public`,
    ADD INDEX IF NOT EXISTS `idx_listed` (`listed`);
ALTER TABLE `tbl_project`
    ADD COLUMN IF NOT EXISTS `unsolicited` TINYINT SIGNED NOT NULL DEFAULT '0' AFTER `listed`,
    ADD INDEX IF NOT EXISTS `idx_unsolicited` (`unsolicited`);