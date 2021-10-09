-- Migration created on: 2021-10-09 21:56:47

ALTER TABLE users."user"
    DROP COLUMN role_id;
DROP TABLE IF EXISTS auth.role CASCADE;
DROP SCHEMA IF EXISTS auth CASCADE;