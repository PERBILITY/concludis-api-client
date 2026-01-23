ALTER TABLE `tbl_project`
    ADD COLUMN `date_to_public_utc` DATETIME
        AS (
            IF(
                JSON_VALUE(`data`, '$.date_to_public')
                    REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}$',
                CONVERT_TZ(
                    STR_TO_DATE(
                        SUBSTRING(JSON_VALUE(`data`, '$.date_to_public'), 1, 19),
                        '%Y-%m-%dT%H:%i:%s'
                    ),
                    SUBSTRING(JSON_VALUE(`data`, '$.date_to_public'), 20),
                    '+00:00'
                ),
                NULL
            )
        )
        STORED,
    ADD COLUMN `date_to_internal_utc` DATETIME
        AS (
            IF(
                JSON_VALUE(`data`, '$.date_to_internal')
                    REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}[+-][0-9]{2}:[0-9]{2}$',
                CONVERT_TZ(
                    STR_TO_DATE(
                        SUBSTRING(JSON_VALUE(`data`, '$.date_to_internal'), 1, 19),
                        '%Y-%m-%dT%H:%i:%s'
                    ),
                    SUBSTRING(JSON_VALUE(`data`, '$.date_to_internal'), 20),
                    '+00:00'
                ),
                NULL
            )
        )
        STORED,
    ADD KEY `idx_date_to_public_utc` (`date_to_public_utc`),
    ADD KEY `idx_date_to_internal_utc` (`date_to_internal_utc`);