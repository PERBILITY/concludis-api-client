/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

DROP TABLE IF EXISTS
    `tbl_global_category`,
    `tbl_global_classification`,
    `tbl_global_geo`,
    `tbl_global_occupation`,
    `tbl_global_schedule`,
    `tbl_global_seniority`,
    `tbl_local_category`,
    `tbl_local_classification`,
    `tbl_local_company`,
    `tbl_local_group`,
    `tbl_local_location`,
    `tbl_local_occupation`,
    `tbl_local_region`,
    `tbl_local_schedule`,
    `tbl_local_seniority`,
    `tbl_project`,
    `tbl_project_ad_container`,
    `tbl_project_category`,
    `tbl_project_classification`,
    `tbl_project_company`,
    `tbl_project_group`,
    `tbl_project_location`,
    `tbl_project_schedule`,
    `tbl_project_seniority`,
    `tbl_setup`
;

-- Daten Export vom Benutzer nicht ausgewählt
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;