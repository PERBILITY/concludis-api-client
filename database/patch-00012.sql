ALTER TABLE `tbl_project`
    ADD COLUMN `date_from_internal` DATE NULL DEFAULT NULL AFTER `data`,
    CHANGE COLUMN `date_from` `date_from_public` DATE NULL DEFAULT NULL AFTER `date_from_internal`,
    DROP INDEX `idx_date_from`,
    ADD INDEX `idx_date_from_public` (`date_from_public`) USING BTREE,
    ADD INDEX `idx_date_from_internal` (`date_from_internal`);
