ALTER TABLE review_image DROP CONSTRAINT FK_ReviewImage_Review;
ALTER TABLE review_image DROP CONSTRAINT FK_ReviewImage_User;
DROP TABLE review_image;

ALTER TABLE review DROP CONSTRAINT FK_Review_Restaurant;
ALTER TABLE review DROP CONSTRAINT FK_Review_User;
DROP TABLE review;