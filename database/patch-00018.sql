ALTER TABLE `tbl_project`
    ADD COLUMN IF NOT EXISTS `priority` TINYINT SIGNED NOT NULL DEFAULT '0' AFTER `lastupdate`,
    ADD INDEX IF NOT EXISTS `idx_priority` (`priority`);