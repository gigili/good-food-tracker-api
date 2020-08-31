CREATE TABLE restaurant(
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(100) NULL,
    city VARCHAR(50) NULL, --TODO: This should be an INT and a foreign key to city table
    phone VARCHAR(50) NULL,
    delivery ENUM('0','1') DEFAULT '0',
    geo_lat DECIMAL(15,6) NULL,
    geo_long DECIMAL(15,6) NULL,
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;