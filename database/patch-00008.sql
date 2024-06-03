ALTER TABLE `tbl_project_location`
    ADD COLUMN IF NOT EXISTS `map_data` JSON NULL AFTER `location_id`;