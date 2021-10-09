-- Migration created on: 2021-10-09 21:56:47

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE SCHEMA IF NOT EXISTS auth;

CREATE TABLE IF NOT EXISTS auth.role
(
    ID         uuid
        CONSTRAINT PK_Roles PRIMARY KEY,
    name       varchar(100) NOT NULL,
    level      INT          NOT NULL
        CONSTRAINT DF_Role_Level     DEFAULT 0,
    created_at timestamptz
        CONSTRAINT DF_Role_CreatedAt DEFAULT current_timestamp
);

ALTER TABLE users."user"
    ADD COLUMN role_id UUID NULL
        CONSTRAINT FK_User_Role REFERENCES auth.role (id) ON UPDATE CASCADE ON DELETE SET NULL;

INSERT INTO auth.role (id, name, level)
VALUES (uuid_in(md5(random()::text || clock_timestamp()::text)::cstring), 'User', 0),
       (uuid_in(md5(random()::text || clock_timestamp()::text)::cstring), 'Moderator', 50),
       (uuid_in(md5(random()::text || clock_timestamp()::text)::cstring), 'Admin', 100),
       (uuid_in(md5(random()::text || clock_timestamp()::text)::cstring), 'Super Admin', 99999);


