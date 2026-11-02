-- Google OAuth: store Google subject id on users (nullable; unique when set)
ALTER TABLE users
    ADD COLUMN google_id VARCHAR(64) NULL UNIQUE;
