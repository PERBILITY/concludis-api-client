ALTER TABLE `tbl_local_board`
    CHANGE COLUMN `meta` `extended_props` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin' AFTER `external_id`;