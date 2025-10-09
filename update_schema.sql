ALTER TABLE `user_addresses` 
ADD `region_code` VARCHAR(255) NULL DEFAULT NULL AFTER `country`,
ADD `province_code` VARCHAR(255) NULL DEFAULT NULL AFTER `region_code`,
ADD `city_code` VARCHAR(255) NULL DEFAULT NULL AFTER `province_code`,
ADD `barangay_code` VARCHAR(255) NULL DEFAULT NULL AFTER `city_code`;
