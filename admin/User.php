<?php
    class User{
        // Connecting to a database
        private $db;
        public $error = [];
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
        public function register($name, $email, $password, $confirm_password){
            // Resetting the error variable for each registration
            $this->error = [];
            if (!$this->validateUser($name, $email, $password, $confirm_password)) {
                return false;
            }
            try{
                // Hashing the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password]);
                return "User Created";
            }
            catch(PDOException $e){
                $this->error[] = "Database error.";
                return false;
            }
        }

        public function findByUsername($username){
            try{
                $stmt = $this->db->prepare("SELECT * FROM users WHERE name = ?");
                $stmt->execute([$username]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            catch(PDOException $e){
                return false;
            }
        }

        // Function that logs in our users
        public function login($username, $password){
            // Resetting error variable for each login
            $this->error = [];
            if (empty($username) || empty($password)) {
                $this->error[] = "Username or Password is required.";
                return false;
            }
            $userExist = $this->db->prepare("SELECT id FROM users WHERE name = ?");
            $userExist->execute([$username]);
            if (!$userExist->fetch()) {
                $this->error[] = "Username or Password is not correct.";
                return false;
            }
            // find the user (Read method)
            $user = $this->findByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                // Password is correct
                return $user;
            }
            else{
                $this->error[] = "Internal issue while logging in.";
                return false;
            }
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