-- Require email confirmation before sign-in.
-- Existing rows get email_confirmed = 0 when the column is added (DEFAULT 0),
-- so current sessions are kicked until users confirm / sign in again.
ALTER TABLE users
    ADD COLUMN email_confirmed TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS email_confirmations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Google-linked accounts are treated as already verified
UPDATE users SET email_confirmed = 1 WHERE google_id IS NOT NULL AND google_id != '';
