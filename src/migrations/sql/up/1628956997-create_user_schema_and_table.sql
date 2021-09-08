CREATE SCHEMA IF NOT EXISTS users;

DROP TYPE IF EXISTS users.user_status CASCADE;
CREATE TYPE users.user_status AS ENUM ('0', '1');

CREATE TABLE IF NOT EXISTS users.user
(
    id             UUID              NOT NULL
        CONSTRAINT PK_Users_User PRIMARY KEY,
    name           varchar(255)      NOT NULL,
    email          varchar(255)      NOT NULL
        CONSTRAINT UQ_Users_Email UNIQUE,
    username       varchar(255)      NOT NULL
        CONSTRAINT UQ_Users_Username UNIQUE,
    password       varchar(255)      NOT NULL,
    image          varchar(255)      NULL,
    status         users.user_status NOT NULL DEFAULT '0',
    activation_key varchar(100) NULL
        CONSTRAINT UQ_Users_Activation_Key UNIQUE
);