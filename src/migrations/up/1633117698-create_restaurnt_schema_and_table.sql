-- Migration created on: 2021-10-01 21:48:18

CREATE SCHEMA IF NOT EXISTS places;

CREATE TABLE IF NOT EXISTS places.restaurant
(
    id         UUID         NOT NULL
        CONSTRAINT PK_Restaurant PRIMARY KEY,
    city_id    UUID         NOT NULL
        CONSTRAINT FK_Restaurant_City REFERENCES locations.city (id) ON UPDATE CASCADE ON DELETE CASCADE,
    name       varchar(255) NOT NULL,
    address    varchar(150) NULL,
    phone      varchar(50)  null,
    email      varchar(100) null,
    delivery   bool
        CONSTRAINT DF_Restaurant_Delivery  DEFAULT false,
    takeout    bool
        CONSTRAINT DF_Restaurant_Takeout   DEFAULT false,
    geo_lat    numeric      null,
    geo_long   numeric      null,
    created_at timestamptz
        CONSTRAINT DF_Restaurant_CreatedAt DEFAULT current_timestamp
);