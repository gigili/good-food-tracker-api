-- Migration created on: 2021-10-15 21:32:40

CREATE TABLE IF NOT EXISTS currency
(
    id         UUID         NOT NULL
        CONSTRAINT PK_Currency PRIMARY KEY,
    name       varchar(150) NOT NULL,
    iso_code   varchar(10)  NOT NULL
        CONSTRAINT UQ_Currency_ShortName UNIQUE,
    created_at timestamptz  NOT NULL
        CONSTRAINT DF_Currency_CreatedAt DEFAULT current_timestamp
);

ALTER TABLE users."user"
    ADD currency_id UUID NULL
        CONSTRAINT FK_User_Currency REFERENCES currency (id) ON UPDATE CASCADE ON DELETE SET NULL;