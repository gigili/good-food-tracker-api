CREATE TABLE refresh_token(
	userID INT NOT NULL,
	token VARCHAR(500),
	is_revoked ENUM('0','1') DEFAULT '0' COMMENT '0 - Still active; 1 - Revoked',
	created_at date DEFAULT NOW(),
	revoked_at date NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE refresh_token ADD CONSTRAINT FK_RefreshToken_User FOREIGN KEY (userID) REFERENCES user (id);