DROP TABLE IF EXISTS `child`;
DROP TABLE IF EXISTS `parent`;

CREATE TABLE `parent` (
    `id` INT PRIMARY KEY
) ENGINE=INNODB;

CREATE TABLE `child` (
    `id` INT PRIMARY KEY,
    `parent_id` INT NOT NULL,
    CONSTRAINT `fk_child_parent` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`)
) ENGINE=INNODB;

INSERT INTO `parent` (`id`) VALUES (1);
INSERT INTO `child` (`id`, `parent_id`) VALUES (1, 1);
