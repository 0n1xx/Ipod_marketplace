<?php
class CrudProducts{

    // Created two variables that I'm going to use within this class
    private $connection;
    public $error = [];

    private $table_name = "products_description"; // The table on which we can perform all crud operations

    // Constructor: receives the database connection object
    public function __construct($db) {
        $this->connection = $db;
    }

    // Uploading an image to Cloudinary using their REST API (no Composer needed)
    // Returns the secure URL of the uploaded image, or false on failure
    private function uploadToCloudinary($fileTmpPath): string|false {
        $cloudName  = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '';
        $apiKey     = $_ENV['CLOUDINARY_API_KEY']    ?? '';
        $apiSecret  = $_ENV['CLOUDINARY_API_SECRET'] ?? '';

        $timestamp  = time();
        $folder     = 'ipod_marketplace';

        // Cloudinary requires a signature: sha256 of sorted params + api_secret
        $paramsToSign = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
        $signature    = hash('sha256', $paramsToSign);

        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

        // Building the multipart POST request with cURL
        $postFields = [
            'file'      => new CURLFile($fileTmpPath),
            'api_key'   => $apiKey,
            'timestamp' => $timestamp,
            'folder'    => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_POST,           true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        // Cloudinary returns secure_url on success
        if (isset($data['secure_url'])) {
            return $data['secure_url'];
        }

        $this->error[] = "Image upload failed: " . ($data['error']['message'] ?? 'Unknown error');
        return false;
    }

    // Creating a private function which will be called inside the public function
    // This function allows us to check that if the name, description or a file path already exists
    private function recordExists(string $columnName, $value): bool {
        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$columnName} = :value";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /* First of all, the data has to be validated. There are so many ways how data
     * can be verified. Each column has it own proper (in my opinion) validation
     */

    public function validateData($data) {
        $this->error = [];
        /* First of all, I'm going to check a product name
         * This project is the ability to learn new functions and just generally a good opportunity
         * The reason why I prefer to use just if statements instead of if/elseif statements because
         * the user sees one error each time.
         */

        // First of all, validating the name based on multiple factors
        if (empty($data['name'])) {
            $this->error[] = "Name is required.";
        }
        if (!preg_match("/^[a-zA-Z-' ]*$/", $data['name'])) {
            $this->error[] = "Only letters and white space allowed for Name.";
        }
        // strlen is a pretty basic function that checks a number of characters
        if (strlen($data['name']) > 50){
            $this->error[] = "Name cannot be more than 50 characters.";
        }
        if (strlen($data['name']) < 5){
            $this->error[] = "Name cannot be less than 5 characters.";
        }
        // str_word_count checks the number of words inside the function
        if (str_word_count($data['name']) < 1){
            $this->error[] = "Name cannot be less than 1 word";
        }
        if($this->recordExists('name', $data['name'])){
            $this->error[] = "Name is already in use.";
        }

        // Now, checking description
        if (empty($data['description'])) {
            $this->error[] = "Description is required.";
        }
        if (!preg_match("/^[\p{L}\p{N}\s\.,\-'\(\)!?\/]+$/u", $data['description'])) {
            $this->error[] = "Description may only contain letters, numbers, spaces, hyphens, apostrophes, slashes, and basic punctuation.";
        }
        // I set the description limits for 5 and 200 because I'm thinking in terms of the user experience
        // I'm not going to read a description with more than 200 words :)
        if (strlen($data['description']) < 5){
            $this->error[] = "Description cannot be less than 5 characters.";
        }
        if (strlen($data['description']) > 2000){
            $this->error[] = "Description cannot be more than 2000 characters.";
        }
        if($this->recordExists('description', $data['description'])){
            $this->error[] = "Description is already in use.";
        }
        // To be honest I asked chat gpt what are the most frequent spam words and decided to check if any of them
        // are in the list
        $spamWords = ["act now", "limited time", "urgent", "immediate", "expires", "deadline", "hurry",
            "last chance", "time-sensitive", "click here", "don't miss", "buy now", "order now"];
        foreach ($spamWords as $words) {
            if (stripos($data['description'], $words) !== false) {
                $this->error[] = "Description includes spam word: $words";
            }
        }

        // Now checking the price
        if (empty($data['price'])) {
            $this->error[] = "Price is required.";
        }
        // Checking the price based on our products that we sell
        if ($data['price'] < 0) {
            $this->error[] = "Price cannot be less than 0.";
        }
        if ($data['price'] > 5000) {
            $this->error[] = "Price cannot be more than 5000.";
        }
        if (preg_match("/[a-zA-Z]/", $data['price'])) {
            $this->error[] = "Price cannot contain letters.";
        }

        // Now checking the image
        $hasImageFile = false;

        // Checking if it is a new image during updating or if it is a new image during registration
        if (
            (isset($data['imagePath']) && !empty($data['imagePath']['name'])) ||
            (isset($data['newImage']) && !empty($data['newImage']['name']))
        ) {
            $hasImageFile = true;

            // Checking which file should be tested
            $file = $data['imagePath'] ?? $data['newImage'];

            $fileName    = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize    = $file['size'];
            $fileError   = $file['error'];

            // Regular checks for images
            if ($fileError !== 0) {
                $this->error[] = "There was an error uploading your file.";
            }
            $allowed = ["jpg", "jpeg", "png", "gif"];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $this->error[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }

            if ($fileSize > 2 * 1024 * 1024) {
                $this->error[] = "File size must be less than or equal to 2 MB";
            }

            $imageInfo = getimagesize($fileTmpName);
            if ($imageInfo === false) {
                $this->error[] = "Invalid image file.";
            } elseif ($imageInfo[0] > 2480 || $imageInfo[1] > 3508) {
                $this->error[] = "Image dimensions exceed allowed limit (2480×3508).";
            }
        }

        $isCreating = !isset($data['newImage']);

        if ($isCreating && !$hasImageFile) {
            $this->error[] = "Please select an image";
        }

        // Checking if the error variable is empty, then validation is successful
        if (empty($this->error)){
            return true;
        }
        else{
            return false;
        }
    }

    // Now I need to create 4 functions of crud
    // Starting from insert
    public function create($data){
        if (!$this->validateData($data)) {
            return false;
        }
        try {
            // Uploading the image to Cloudinary instead of saving it locally
            // This is necessary because Railway doesn't persist local files between deploys
            $imageUrl = $this->uploadToCloudinary($data['imagePath']['tmp_name']);
            if (!$imageUrl) {
                return false;
            }

            $sql = "INSERT INTO products_description (name, description, price, imagePath) 
                VALUES (:name, :description, :price, :imagePath)";
            $stmt = $this->connection->prepare($sql);

            // Preparing data before we execute our statement
            $name        = htmlspecialchars(strip_tags($data['name']));
            $description = htmlspecialchars(strip_tags($data['description']));
            $price       = (float)$data['price'];

            // Binding the parameters to our query
            $stmt->bindParam(':name',        $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price',       $price);
            $stmt->bindParam(':imagePath',   $imageUrl);

            return $stmt->execute();

        } catch(PDOException $e) {
            $this->error[] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    // Creating a read function
    public function read(){
        try{
            // Store our SQL select statement
            $sql = "SELECT * FROM products_description ORDER BY product_id DESC";
            // execute the query
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            // fetch all the results in an associative array
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            $this->error[] = "Database read error";
            return false;
        }
    }

    // A function that is responsible for getting a single value by its product id
    public function getProductById($id) {
        try {
            $sql = "SELECT * FROM {$this->table_name} WHERE product_id = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error[] = "Error fetching product: " . $e->getMessage();
            return false;
        }
    }

    // Creating an update function
    public function update($id, $data){
        if (!$this->validateData($data)) {
            return false;
        }

        $name        = htmlspecialchars($data["name"]);
        $description = htmlspecialchars($data["description"]);
        $price       = htmlspecialchars($data["price"]);

        $oldImage = $data["oldImage"];
        $newImage = $data["newImage"];

        // If there is a new image — upload it to Cloudinary
        if (!empty($newImage['name'])) {
            $imageUrl = $this->uploadToCloudinary($newImage['tmp_name']);
            if (!$imageUrl) {
                return false;
            }
            // No need to delete old image locally — it lives on Cloudinary
            $finalImage = $imageUrl;
            // If no image is provided, recording an old image into the variable
        } else {
            $finalImage = $oldImage;
        }

        // Trying to update the row
        try {
            $query = "UPDATE " . $this->table_name . "
                 SET name=:name, description=:description, price=:price, imagePath=:imagePath
                 WHERE product_id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':name',        $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':price',       $price);
            $stmt->bindParam(':imagePath',   $finalImage);
            $stmt->bindParam(':id',          $id);
            return $stmt->execute();
        }
        catch(PDOException $e){
            $this->error[] = "Database update error";
            return false;
        }
    }

    // A function that deletes a record by its product id
    public function delete($id){
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE product_id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
        catch(PDOException $e){
            $this->error[] = "Database delete error";
            return false;
        }
    }
}
?>