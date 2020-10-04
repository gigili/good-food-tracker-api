CREATE TABLE refresh_token(
	userID INT NOT NULL,
	token VARCHAR(320),
	is_revoked ENUM('0','1') DEFAULT '0' COMMENT '0 - Still active; 1 - Revoked',
	created_at datetime DEFAULT NOW(),
	revoked_at datetime NULL
)ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

ALTER TABLE refresh_token ADD CONSTRAINT FK_RefreshToken_User FOREIGN KEY (userID) REFERENCES user (id);