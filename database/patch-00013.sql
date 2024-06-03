INSERT INTO `tbl_global_classification` (`global_id`, `name`)
    VALUES
        (12, 'Werkstudent'),
        (13, 'Beamte'),
        (14, 'Abschlussarbeit'),
        (15, 'Befristet'),
        (16, 'Duales Studium')
;

INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`)
    VALUES
        ('global_classification', 'name', 12, 'en_GB', 'Working student'),
        ('global_classification', 'name', 13, 'en_GB', 'Civil servant'),
        ('global_classification', 'name', 14, 'en_GB', 'Thesis'),
        ('global_classification', 'name', 15, 'en_GB', 'Fixed term'),
        ('global_classification', 'name', 16, 'en_GB', 'Dual study program')
;

DELETE FROM `tbl_global_schedule` WHERE `global_id` IN (2,3,4,10);

INSERT INTO `tbl_global_schedule` (`global_id`, `name`)
VALUES
    (11, 'Schicht / Nacht / Wochenende'),
    (12, 'Vollzeit oder Teilzeit')
;

DELETE FROM `tbl_i18n` WHERE `model` = 'global_schedule' AND `key` IN (2,3,4,10);

INSERT INTO `tbl_i18n` (`model`, `field`, `key`, `locale`, `translation`)
VALUES
    ('global_schedule', 'name', 11, 'en_GB', 'Shift / night / weekend'),
    ('global_schedule', 'name', 12, 'en_GB', 'Full-time or part-time')
;