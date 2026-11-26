ALTER TABLE users
    ADD COLUMN password_reset_hash CHAR(64) NULL,
    ADD COLUMN password_reset_expires_at BIGINT UNSIGNED NULL;
