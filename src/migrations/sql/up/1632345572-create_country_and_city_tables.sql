-- Migration created on: 2021-09-22 23:19:32

CREATE SCHEMA IF NOT EXISTS locations;
CREATE TABLE IF NOT EXISTS locations.country
(
    id         uuid         NOT NULL
        CONSTRAINT PK_Country_ID PRIMARY KEY,
    name       varchar(255) NOT NULL,
    created_at timestamptz
        CONSTRAINT DF_Country_CreatedAt DEFAULT current_timestamp
);

CREATE TABLE IF NOT EXISTS locations.city
(
    id         UUID         NOT NULL
        CONSTRAINT PK_City_ID PRIMARY KEY,
    country_id UUID         NOT NULL
        CONSTRAINT FK_City_Country REFERENCES locations.country (id) ON UPDATE CASCADE ON DELETE CASCADE,
    name       varchar(255) NOT NULL,
    create_at  timestamptz
        CONSTRAINT DF_City_CreatedAt DEFAULT current_timestamp
);