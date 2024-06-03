ALTER TABLE `tbl_project`
    ADD COLUMN `published_internal` TINYINT unsigned NOT NULL DEFAULT '0' AFTER `date_from`,
	ADD COLUMN `published_public` TINYINT unsigned NOT NULL DEFAULT '1' AFTER `published_internal`,
	ADD INDEX `idx_published_internal` (`published_internal`),
	ADD INDEX `idx_published_public` (`published_public`);

ALTER TABLE `tbl_project_ad_container`
    ADD FULLTEXT INDEX `fulltext_content_internal` (`content_internal`);