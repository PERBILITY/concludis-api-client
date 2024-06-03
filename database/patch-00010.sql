ALTER TABLE `tbl_project_ad_container`
    ADD COLUMN `sortorder` SMALLINT UNSIGNED NOT NULL DEFAULT '0' AFTER `type`;
