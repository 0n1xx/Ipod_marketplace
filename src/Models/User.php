<?php
    class User{
        // Connecting to a database
        private $db;
        public $error = [];
        private $pendingUnconfirmedUser = null;

        public function __construct($db_connection){
            $this->db = $db_connection;
        }

        // Function that checks the input data during the registration
        public function validateUser($name, $email, $password, $confirm_password){
            // First of all, check if the user already exists
            $userExist = $this->db->prepare("SELECT id FROM users WHERE name = ?");
            $userExist->execute([$name]);
            if ($userExist->fetch()) {
                $this->error[] = "Username already exists.";
            }

            // Email is UNIQUE in the database, so check it too, otherwise a
            // duplicate email only surfaces as a generic "Database error." later
            $emailExist = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $emailExist->execute([$email]);
            if ($emailExist->fetch()) {
                $this->error[] = "An account with this email already exists.";
            }

            // Checking name value:
            // Checking if the name is empty
            if (empty($name)) {
                $this->error[] = "Name is required.";
            }
            // Checking if the name is too long
            if (strlen($name) > 50 || strlen($email) > 50) {
                $this->error[] = "Name cannot exceed 50 characters.";
            }
            // Checking if a user decides to enter 4 randon characters
            if (preg_match('/(.)\1{3,}/i', $name)) { // e.g. "aaaa" or "----"
                $this->error[] = "Name cannot have 4 or more repeated characters.";
            }
            // The name shouldn't be more than 1 word
            if (str_word_count($name) > 1) {
                $this->error[] = "Name cannot have more than 1 words.";
            }
            // Simple check if a name contain numbers
            if (preg_match('/\d/', $name)) {
                $this->error[] = "Name cannot contain numbers.";
            }

            // Checking email value:
            // Checking if the email is empty
            if (empty($email)) {
                $this->error[] = "Email is required.";
            }
            // Checking if the email is too long
            if (strlen($email) > 50) {
                $this->error[] = "Email is too long.";
            }
            // Checking if the email doesn't follow it's format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error[] = "Invalid email format.";
            }

            // Checking the password (shared with resetPassword() below)
            $this->validatePasswordRules($password, $confirm_password);

            // Checking if the error variable is empty, then validation is successful
            if (empty($this->error)){
                return true;
            }
            else{
                return false;
            }
        }

        // Shared password-strength rules, used by both registration and
        // password reset — appends to $this->error, doesn't reset it
        private function validatePasswordRules($password, $confirm_password){
            if (empty($password)) {
                $this->error[] = "Password is required.";
            }
            if (strlen($password) < 8) {
                $this->error[] = "Password must be at least 8 characters.";
            }
            if (strlen($password) > 50) {
                $this->error[] = "Password too long (max 50 chars).";
            }
            if ($password !== $confirm_password) {
                $this->error[] =  "Passwords do not match";
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $this->error[] = "Password must contain at least one uppercase letter.";
            }
            if (!preg_match('/[a-z]/', $password)) {
                $this->error[] = "Password must contain at least one lowercase letter.";
            }
            if (!preg_match('/\d/', $password)) {
                $this->error[] = "Password must contain at least one number.";
            }
            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $this->error[] = "Password must contain at least one symbol (!@#$ etc.).";
            }
        }

        // Writing a function for the registration
        // Returns new user id on success, or false on validation/DB failure.
        public function register($name, $email, $password, $confirm_password){
            // Resetting the error variable for each registration
            $this->error = [];
            if (!$this->validateUser($name, $email, $password, $confirm_password)) {
                return false;
            }
            $this->ensureEmailConfirmationSchema();
            try{
                // Hashing the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare(
                    "INSERT INTO users (name, email, password, email_confirmed) VALUES (?, ?, ?, 0)"
                );
                $stmt->execute([$name, $email, $hashed_password]);
                return (int) $this->db->lastInsertId();
            }
            catch(PDOException $e){
                $this->error[] = "Database error.";
                return false;
            }
        }

        public function isEmailConfirmed(array $user): bool {
            return (int) ($user['email_confirmed'] ?? 0) === 1;
        }

        public function ensureEmailConfirmationSchema(): void {
            static $done = false;
            if ($done) {
                return;
            }
            try {
                $this->db->exec(
                    'ALTER TABLE users ADD COLUMN email_confirmed TINYINT(1) NOT NULL DEFAULT 0'
                );
            } catch (PDOException $e) {
                // Column already exists
            }
            try {
                $this->db->exec(
                    'CREATE TABLE IF NOT EXISTS email_confirmations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        token_hash VARCHAR(64) NOT NULL,
                        expires_at TIMESTAMP NOT NULL,
                        used TINYINT(1) NOT NULL DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )'
                );
            } catch (PDOException $e) {
                // Table may already exist
            }
            // Google accounts already verified their email with Google
            try {
                $this->db->exec(
                    "UPDATE users SET email_confirmed = 1 WHERE google_id IS NOT NULL AND google_id != '' AND email_confirmed = 0"
                );
            } catch (PDOException $e) {
                // ignore
            }
            $done = true;
        }

        public function markEmailConfirmed(int $userId): void {
            $this->ensureEmailConfirmationSchema();
            try {
                $stmt = $this->db->prepare('UPDATE users SET email_confirmed = 1 WHERE id = ?');
                $stmt->execute([$userId]);
            } catch (PDOException $e) {
                // ignore
            }
        }

        public function createEmailConfirmation(int $userId): ?string {
            $this->ensureEmailConfirmationSchema();
            try {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                $expiresAt = date('Y-m-d H:i:s', time() + 86400); // 24h

                $stmt = $this->db->prepare(
                    'INSERT INTO email_confirmations (user_id, token_hash, expires_at) VALUES (?, ?, ?)'
                );
                $stmt->execute([$userId, $tokenHash, $expiresAt]);
                return $token;
            } catch (PDOException $e) {
                return null;
            }
        }

        public function findValidEmailConfirmation(string $token): ?array {
            $this->ensureEmailConfirmationSchema();
            $tokenHash = hash('sha256', $token);
            try {
                $stmt = $this->db->prepare(
                    'SELECT ec.id, ec.user_id, u.email
                     FROM email_confirmations ec
                     JOIN users u ON u.id = ec.user_id
                     WHERE ec.token_hash = ? AND ec.used = 0 AND ec.expires_at > NOW()
                     LIMIT 1'
                );
                $stmt->execute([$tokenHash]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (PDOException $e) {
                return null;
            }
        }

        public function confirmEmailWithToken(string $token): bool {
            $row = $this->findValidEmailConfirmation($token);
            if (!$row) {
                $this->error[] = 'Invalid or expired confirmation link.';
                return false;
            }
            try {
                $this->db->beginTransaction();
                $this->markEmailConfirmed((int) $row['user_id']);
                $markUsed = $this->db->prepare('UPDATE email_confirmations SET used = 1 WHERE id = ?');
                $markUsed->execute([(int) $row['id']]);
                $this->db->commit();
                return true;
            } catch (PDOException $e) {
                $this->db->rollBack();
                $this->error[] = 'Database error.';
                return false;
            }
        }

        // Looks a user up by either their username or their email, so
        // login() can accept both without the caller caring which was typed
        public function findByIdentifier($identifier){
            try{
                $stmt = $this->db->prepare("SELECT * FROM users WHERE name = ? OR email = ?");
                $stmt->execute([$identifier, $identifier]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            catch(PDOException $e){
                return false;
            }
        }

        // If password is correct but email is not confirmed yet, login()
        // returns false and stores the user here for a resend.
        public function getPendingUnconfirmedUser() {
            return $this->pendingUnconfirmedUser;
        }

        public function login($identifier, $password){
            // Resetting error variable for each login
            $this->error = [];
            $this->pendingUnconfirmedUser = null;
            if (empty($identifier) || empty($password)) {
                $this->error[] = "Username/Email or Password is required.";
                return false;
            }
            $this->ensureEmailConfirmationSchema();
            $user = $this->findByIdentifier($identifier);
            // Same error either way (unknown identifier vs. wrong password) so
            // a login attempt can't be used to find out which accounts exist
            if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
                if (!$this->isEmailConfirmed($user)) {
                    $this->pendingUnconfirmedUser = $user;
                    $this->error[] = 'EMAIL_NOT_CONFIRMED';
                    return false;
                }
                return $user;
            }
            $this->error[] = "Username/Email or Password is not correct.";
            return false;
        }

        public function findByEmail($email){
            try{
                $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            catch(PDOException $e){
                return false;
            }
        }

        // Adds google_id once so Google OAuth works on existing Railway DBs
        // without a manual migration step.
        public function ensureGoogleColumn(): void
        {
            static $done = false;
            if ($done) {
                return;
            }
            try {
                $this->db->exec(
                    'ALTER TABLE users ADD COLUMN google_id VARCHAR(64) NULL UNIQUE'
                );
            } catch (PDOException $e) {
                // Column already exists — ignore duplicate-column errors
            }
            $done = true;
        }

        public function findByGoogleId(string $googleId)
        {
            $this->ensureGoogleColumn();
            try {
                $stmt = $this->db->prepare('SELECT * FROM users WHERE google_id = ? LIMIT 1');
                $stmt->execute([$googleId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
            } catch (PDOException $e) {
                return false;
            }
        }

        /**
         * Login or register via Google profile (sub + email).
         * Links an existing email account when found; otherwise creates a user.
         */
        public function findOrCreateFromGoogle(string $googleId, string $email, string $displayName = '')
        {
            $this->error = [];
            $this->ensureGoogleColumn();
            $this->ensureEmailConfirmationSchema();

            $existing = $this->findByGoogleId($googleId);
            if ($existing) {
                $this->markEmailConfirmed((int) $existing['id']);
                $existing['email_confirmed'] = 1;
                return $existing;
            }

            $byEmail = $this->findByEmail($email);
            if ($byEmail) {
                try {
                    $stmt = $this->db->prepare(
                        'UPDATE users SET google_id = ?, email_confirmed = 1 WHERE id = ? AND (google_id IS NULL OR google_id = ?)'
                    );
                    $stmt->execute([$googleId, $byEmail['id'], $googleId]);
                    $byEmail['google_id'] = $googleId;
                    $byEmail['email_confirmed'] = 1;
                    return $byEmail;
                } catch (PDOException $e) {
                    $this->error[] = 'Could not link Google account.';
                    return false;
                }
            }

            $name = $this->uniqueUsernameFromGoogle($email, $displayName);
            $password = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);

            try {
                $stmt = $this->db->prepare(
                    'INSERT INTO users (name, email, password, google_id, email_confirmed) VALUES (?, ?, ?, ?, 1)'
                );
                $stmt->execute([$name, $email, $password, $googleId]);
                return $this->findByGoogleId($googleId);
            } catch (PDOException $e) {
                $this->error[] = 'Database error.';
                return false;
            }
        }

        private function uniqueUsernameFromGoogle(string $email, string $displayName): string
        {
            $base = '';
            if ($displayName !== '') {
                $base = preg_replace('/[^A-Za-z]/', '', explode(' ', trim($displayName))[0] ?? '');
            }
            if ($base === '') {
                $local = explode('@', $email)[0] ?? 'user';
                $base = preg_replace('/[^A-Za-z]/', '', $local) ?: 'user';
            }
            $base = substr($base, 0, 40);
            if ($base === '') {
                $base = 'user';
            }

            $candidate = $base;
            $n = 0;
            while (true) {
                $stmt = $this->db->prepare('SELECT id FROM users WHERE name = ? LIMIT 1');
                $stmt->execute([$candidate]);
                if (!$stmt->fetch()) {
                    return $candidate;
                }
                $n++;
                $candidate = substr($base, 0, 40) . $n;
            }
        }

        public function findById(int $userId) {
            try {
                $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
                $stmt->execute([$userId]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
            } catch (PDOException $e) {
                return false;
            }
        }

        // Starts a "forgot password" request: stores only a hash of the token
        // (so a leaked database row can't be replayed) and returns the raw
        // token to email to the user
        public function createPasswordReset(int $userId): ?string {
            try{
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // valid for 1 hour

                $stmt = $this->db->prepare(
                    "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)"
                );
                $stmt->execute([$userId, $tokenHash, $expiresAt]);
                return $token;
            }
            catch(PDOException $e){
                return null;
            }
        }

        // Looks up a reset request by its raw token — only returns it if it
        // hasn't been used yet and hasn't expired
        public function findValidReset(string $token): ?array {
            $tokenHash = hash('sha256', $token);
            try{
                $stmt = $this->db->prepare(
                    "SELECT pr.id, pr.user_id, u.email
                     FROM password_resets pr
                     JOIN users u ON u.id = pr.user_id
                     WHERE pr.token_hash = ? AND pr.used = 0 AND pr.expires_at > NOW()
                     LIMIT 1"
                );
                $stmt->execute([$tokenHash]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            catch(PDOException $e){
                return null;
            }
        }

        // Sets a new password for a validated reset request and marks the
        // token used so it can't be replayed
        public function resetPassword(int $resetId, int $userId, string $password, string $confirmPassword): bool {
            $this->error = [];
            $this->validatePasswordRules($password, $confirmPassword);
            if (!empty($this->error)) {
                return false;
            }

            try{
                $this->db->beginTransaction();

                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $userId]);

                $markUsed = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
                $markUsed->execute([$resetId]);

                $this->db->commit();
                return true;
            }
            catch(PDOException $e){
                $this->db->rollBack();
                $this->error[] = "Database error.";
                return false;
            }
        }
    }
?>