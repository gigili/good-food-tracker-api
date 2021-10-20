-- Migration created on: 2021-10-15 21:32:40
ALTER TABLE users."user"
    DROP IF EXISTS currency_id CASCADE;
DROP TABLE IF EXISTS currency CASCADE;