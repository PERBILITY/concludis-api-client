CREATE TABLE IF NOT EXISTS  `tbl_i18n` (
    `model` VARCHAR(96) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `field` VARCHAR(96) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `key` VARCHAR(96) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `locale` VARCHAR(5) NOT NULL COLLATE 'utf8mb4_unicode_ci',
    `translation` MEDIUMTEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
    PRIMARY KEY (`model`, `field`, `key`, `locale`) USING BTREE,
    INDEX `FAST_TRANSLATION` (`model`, `key`) USING BTREE,
    INDEX `FAST_TRANSLATION2` (`model`, `field`, `key`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tbl_local_schedule`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `global_schedule_id`;
ALTER TABLE `tbl_global_schedule`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NOT NULL DEFAULT 'de_DE' COLLATE 'utf8mb4_unicode_ci' AFTER `global_id`;

ALTER TABLE `tbl_local_classification`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `global_classification_id`;
ALTER TABLE `tbl_global_classification`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NOT NULL DEFAULT 'de_DE' COLLATE 'utf8mb4_unicode_ci' AFTER `global_id`;

ALTER TABLE `tbl_local_seniority`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `global_seniority_id`;
ALTER TABLE `tbl_global_seniority`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NOT NULL DEFAULT 'de_DE' COLLATE 'utf8mb4_unicode_ci' AFTER `global_id`;

ALTER TABLE `tbl_local_category`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `global_category_id`;
ALTER TABLE `tbl_global_category`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NOT NULL DEFAULT 'de_DE' COLLATE 'utf8mb4_unicode_ci' AFTER `global_id`;

ALTER TABLE `tbl_local_group`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `group_key`;

ALTER TABLE `tbl_project_ad_container`
    ADD COLUMN IF NOT EXISTS `locale` VARCHAR(5) NOT NULL DEFAULT 'de_DE' COLLATE 'utf8mb4_unicode_ci' AFTER `datafield_id`,
    ADD INDEX `idx_locale` (`locale`),
    ADD FULLTEXT INDEX `fulltext_content_external` (`content_external`),
    DROP PRIMARY KEY,
	ADD PRIMARY KEY (`source_id`, `project_id`, `datafield_id`, `locale`) USING BTREE;


INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  1, 'en_GB', 'Accounting/Finance');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  2, 'en_GB', 'Administration/Clerical');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  3, 'en_GB', 'Property Manager - Real Estate Agent');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  4, 'en_GB', 'Craftsmanship');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  5, 'en_GB', 'Executive/Strategic Management');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  6, 'en_GB', 'Design & Styling');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  7, 'en_GB', 'Customer Service');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  8, 'en_GB', 'Editorial/Documentation');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  9, 'en_GB', 'Engineering/Development and Construction');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  10, 'en_GB', 'Hotel and Gastronomy');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  11, 'en_GB', 'Human Resources');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  12, 'en_GB', 'Maintenance');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  13, 'en_GB', 'IT/Information Technology');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  14, 'en_GB', 'Legal');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  15, 'en_GB', 'Logistics and Transportation');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  16, 'en_GB', 'Marketing/Product');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  17, 'en_GB', 'Medical and Health');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  18, 'en_GB', 'Other Professions');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  19, 'en_GB', 'Production');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  20, 'en_GB', 'Project Management');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  21, 'en_GB', 'Quality Management');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  22, 'en_GB', 'Scientific Research');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  23, 'en_GB', 'Sales');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  24, 'en_GB', 'Security/Civil Protection');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_category', 'name',  25, 'en_GB', 'Education and Training');

INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 1, 'en_GB', 'Permanent Employment');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 2, 'en_GB', 'Freelancer');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 3, 'en_GB', 'Apprenticeship');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 4, 'en_GB', 'Trainee');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 5, 'en_GB', 'Internship/Student Assistant');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 6, 'en_GB', 'Assistant');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 9, 'en_GB', 'Marginal Employment/Mini Job');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 10, 'en_GB', 'Artist');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_classification', 'name', 11, 'en_GB', 'Self-employed');

INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 1, 'en_GB', 'Full-time');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 2, 'en_GB', 'Part-time - Shift');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 3, 'en_GB', 'Weekend');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 4, 'en_GB', 'Night Shift');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 5, 'en_GB', 'Part-time - Morning');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 6, 'en_GB', 'Part-time - Afternoon');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 7, 'en_GB', 'Part-time - Evening');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 8, 'en_GB', 'Part-time - Flexible');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 9, 'en_GB', 'Remote Work');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_schedule', 'name', 10, 'en_GB', 'Shift');

INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 1, 'en_GB', 'Pupils');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 2, 'en_GB', 'Student');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 3, 'en_GB', 'Graduate');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 4, 'en_GB', 'Entry Level');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 5, 'en_GB', 'Experienced');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 6, 'en_GB', 'Manager');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 7, 'en_GB', 'Director');
INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`) VALUES ('global_seniority', 'name', 8, 'en_GB', 'Executive');



