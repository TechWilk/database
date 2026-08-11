DROP TABLE IF EXISTS `table`;

CREATE TABLE `table` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date` DATETIME NOT NULL,
    `string` VARCHAR(100) NOT NULL,
    CONSTRAINT `chk_table_string_nonempty` CHECK (CHAR_LENGTH(`string`) > 0)
) ENGINE=INNODB;

INSERT INTO `table` (`id`, `date`, `string`) VALUES (1, '2020-01-01', 'this is some text');
INSERT INTO `table` (`id`, `date`, `string`) VALUES (2, '2021-02-02', 'some more test text');