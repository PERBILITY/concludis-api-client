ALTER TABLE `tbl_local_company`
    DROP COLUMN `name`,
    ADD COLUMN `name` VARCHAR(255)
        GENERATED ALWAYS AS (
            COALESCE(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.name')), '')
            )
        STORED
        AFTER `company_id`,
    ADD INDEX `idx_name` (`name`);