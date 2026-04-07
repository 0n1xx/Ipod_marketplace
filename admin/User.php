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
            $userExist = $this->db->prepare("SELECT id FROM admin_users WHERE name = ?");
            $userExist->execute([$name]);
            if ($userExist->fetch()) {
                $this->error[] = "Username already exists.";
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

            // Checking the password:
            // Checking if the passwords are the same
            if (empty($password)) {
                $this->error[] = "Password is required.";
            }
            // Checking if the password is too small. In my opinion, it should be at least 8 characters
            if (strlen($password) < 8) {
                $this->error[] = "Password must be at least 8 characters.";
            }
            // Checking if the password is too big. In my opinion, it should be at most 50 characters
            if (strlen($password) > 50) {
                $this->error[] = "Password too long (max 50 chars).";
            }
            // Checking if a password and a confirm password match
            if ($password !== $confirm_password) {
                $this->error[] =  "Passwords do not match";
            }
            // Verifying that a password has at least one upper case letter
            if (!preg_match('/[A-Z]/', $password)) {
                $this->error[] = "Password must contain at least one uppercase letter.";
            }
            // Verifying that a password has at least one lower case letter
            if (!preg_match('/[a-z]/', $password)) {
                $this->error[] = "Password must contain at least one lowercase letter.";
            }
            // Password also should include one number
            if (!preg_match('/\d/', $password)) {
                $this->error[] = "Password must contain at least one number.";
            }
            // Finally, password should include one special character
            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                $this->error[] = "Password must contain at least one symbol (!@#$ etc.).";
            }

            // Checking if the error variable is empty, then validation is successful
            if (empty($this->error)){
                return true;
            }
            else{
                return false;
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
                $stmt = $this->db->prepare("INSERT INTO admin_users (name, email, password) VALUES (?, ?, ?)");
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
                $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE name = ?");
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
            $userExist = $this->db->prepare("SELECT id FROM admin_users WHERE name = ?");
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
    }
?>