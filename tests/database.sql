CREATE DATABASE IF NOT EXISTS `reconmap_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;
CREATE USER IF NOT EXISTS 'reconmapper'@'%' IDENTIFIED BY 'reconmapped';
GRANT ALL ON `reconmap_test`.* TO 'reconmapper'@'%';

