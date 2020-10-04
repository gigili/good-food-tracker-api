CREATE TABLE `user` (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL ,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    active ENUM('0','1') NOT NULL DEFAULT '0',
    created_at DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TRIGGER TRG_BI_User BEFORE INSERT ON user
FOR EACH ROW
BEGIN
	SET NEW.created_at = CURRENT_DATE;
END;

INSERT INTO `user`
    (name, email, username, password, active)
VALUES
    ('Igor Ilić', 'igorilicbl@gmail.com', 'gac', 'fac5b2c9d9fe1d6cc567798868a6e02124a50f4d3d4330ea96a95ddb62e9673b', '1');