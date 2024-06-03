ALTER TABLE `tbl_project`
    ADD COLUMN IF NOT EXISTS `date_from` DATE NULL AFTER `data`,
    ADD INDEX IF NOT EXISTS `idx_date_from` (`date_from`);
