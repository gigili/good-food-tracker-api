CREATE TABLE review(
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	guid VARCHAR(50) NOT NULL DEFAULT UUID(),
	restaurantID INT NOT NULL,
	userID INT NOT NULL,
	dish_name VARCHAR(100) NULL,
	price DECIMAL(12,4) DEFAULT 0.0,
	comment VARCHAR(300) NULL,
	type ENUM('0','1','2') DEFAULT '0' COMMENT '0 - In person, 1 - Delivery, 2 - Takeout',
	private ENUM('0','1') DEFAULT '1' COMMENT '0 - Public, 1 - Private',
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)Engine=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE review ADD CONSTRAINT FK_Review_Restaurant FOREIGN KEY(restaurantID) REFERENCES restaurant(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE review ADD CONSTRAINT FK_Review_User FOREIGN KEY(userID) REFERENCES user(id) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE TABLE review_image (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	reviewID INT NOT NULL,
	userID INT NOT NULL,
	file VARCHAR(255),
	created_at DATETIME
)Engine=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE review_image ADD CONSTRAINT FK_ReviewImage_Review FOREIGN KEY (reviewID) REFERENCES review(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE review_image ADD CONSTRAINT FK_ReviewImage_User FOREIGN KEY (userID) REFERENCES user(id) ON UPDATE CASCADE ON DELETE CASCADE;