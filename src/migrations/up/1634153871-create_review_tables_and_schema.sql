-- Migration created on: 2021-10-13 21:37:51

CREATE SCHEMA IF NOT EXISTS reviews;

/*
CREATE TABLE IF NOT EXISTS reviews.rating
(
    id   SERIAL       NOT NULL PRIMARY KEY,
    name VARCHAR(200) NOT NULL
);
*/

CREATE TABLE IF NOT EXISTS reviews.review
(
    id             UUID         NOT NULL
        CONSTRAINT PK_Review PRIMARY KEY,
    user_id        UUID         NOT NULL
        CONSTRAINT FK_Review_User REFERENCES users."user" (id) ON UPDATE CASCADE ON DELETE CASCADE,
    restaurant_id  UUID         NOT NULL
        CONSTRAINT FK_Review_Restaurant REFERENCES places.restaurant (id) ON UPDATE CASCADE ON DELETE CASCADE,
    rating_id      int          NULL,
    --CONSTRAINT FK_Review_Rating REFERENCES reviews.rating (id) ON UPDATE CASCADE ON DELETE SET NULL,
    name           VARCHAR(255) NOT NULL,
    price          numeric(2)   NULL,
    comment        text         NULL,
    delivery       bool
        CONSTRAINT DF_Review_Delivery  DEFAULT false,
    delivery_price numeric(2)   NULL,
    delivery_time  numeric      NULL,
    takeout        bool
        CONSTRAINT DF_Review_Takeout   DEFAULT false,
    private        bool
        CONSTRAINT DF_Review_Private   DEFAULT true,
    order_date     timestamptz
        CONSTRAINT DF_Review_OrderDate DEFAULT current_timestamp,
    created_at     timestamptz
        CONSTRAINT DF_Review_CreatedAt DEFAULT current_timestamp
);

CREATE TABLE reviews.review_image
(
    id         UUID         NOT NULL
        CONSTRAINT PK_ReviewImage PRIMARY KEY,
    review_id  UUID         NOT NULL
        CONSTRAINT FK_ReviewImage_Review REFERENCES reviews.review (id) ON UPDATE CASCADE ON DELETE CASCADE,
    user_id    UUID         NOT NULL
        CONSTRAINT FK_ReviewImage_User REFERENCES users."user" (id) ON UPDATE CASCADE ON DELETE CASCADE,
    image      varchar(255) NOT NULL,
    comment    VARCHAR(500) NULL,
    created_at timestamptz  NOT NULL
        CONSTRAINT DF_ReviewImage_CreatedAt DEFAULT current_timestamp
);