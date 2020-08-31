CREATE TABLE restaurant(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(100) NULL,
    city VARCHAR(50) NULL,
    phone VARCHAR(50) NULL,
    delivery ENUM('0','1') DEFAULT '0',
    geo_lat DECIMAL(15,6) NULL,
    geo_long DECIMAL(15,6) NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `restaurant` (`id`, `name`, `address`, `city`, `phone`, `delivery`, `geo_lat`, `geo_long`) VALUES
(1, 'Verdi', NULL, 'Banja Luka', NULL, '1', NULL, NULL),
(2, 'Bajka', NULL, 'Banja Luka', NULL, '0', NULL, NULL),
(3, 'Pizza House', NULL, 'Banja Luka', '051-212-414', '1', NULL, NULL),
(4, 'Le Coq', NULL, 'Banja Luka', NULL, '1', NULL, NULL);