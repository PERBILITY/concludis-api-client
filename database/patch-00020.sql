ALTER TABLE `tbl_project`
    DROP COLUMN IF EXISTS `listed`,
    DROP INDEX IF EXISTS `idx_listed`;