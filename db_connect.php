<?php
include 'conn.php';
$database = new Database();
$pdo=$database->getPDO();
class SpendSmart {
    private $pdo;

    // Database connection initialization
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getPDO();
    }

    // Getter method for PDO object
    public function getPDO() {
        return $this->pdo;
    }

    // Method to add a user to the database
    public function addUser($data) {
        // Initialize error variables
        $name_err = $phone_err = $pin_err = '';

        // Validate name
        if (empty(trim($data['name']))) {
            $name_err = "Please enter your name.";
        } else {
            $name = trim($data['name']);
        }

        // Validate phone number
        if (empty(trim($data['phone']))) {
            $phone_err = "Please enter your phone number.";
        } else {
            $phone = trim($data['phone']);
        }

        // Validate PIN
        if (empty(trim($data['pin']))) {
            $pin_err = "Please enter your PIN.";
        } else {
            $pin = trim($data['pin']);
        }

        // Check for input errors before inserting into database
        if (empty($name_err) && empty($phone_err) && empty($pin_err)) {
            // Prepare a select statement
            $sql = "SELECT userId FROM Users WHERE phone = :phone";

            if ($stmt = $this->pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":phone", $param_phone, PDO::PARAM_STR);

                // Set parameters
                $param_phone = $phone;

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        return ['message' => 'This phone number is already registered.', 'status' => 'fail'];
                    }
                } else {
                    return ['message' => 'Oops! Something went wrong. Please try again later.', 'status' => 'fail'];
                }
            }

            // Prepare an insert statement
            $sql = "INSERT INTO Users (name, phone, pin, date, status) VALUES (:name, :phone, :pin, NOW(), 'active')";

            if ($stmt = $this->pdo->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(":name", $param_name, PDO::PARAM_STR);
                $stmt->bindParam(":phone", $param_phone, PDO::PARAM_STR);
                $stmt->bindParam(":pin", $param_pin, PDO::PARAM_STR);

                // Set parameters
                $param_name = $name;
                $param_phone = $phone;
                $param_pin = md5($pin); // Hashing the PIN before storing

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    return ['message' => 'Successful signup.', 'status' => 'success'];
                } else {
                    return ['message' => 'Oops! Something went wrong. Please try again later.', 'status' => 'fail'];
                }
            }
        } else {
            // Return error messages
            return [
                'message' => [
                    'name' => $name_err,
                    'phone' => $phone_err,
                    'pin' => $pin_err
                ],
                'status' => 'fail'
            ];
        }
    }

    public function addResource($data){
        // Validate resource name
        if (empty(trim($data["re_name"]))) {
            $resourceName_err = "Please enter a resource name.";
            $message=[
                'message','Please enter a resource name',
                'status','fail'
            ];
            return $message;

        } else {
            $resourceName = trim($data["re_name"]);
        }

        // Validate resource status
        if (empty(trim($data["re_status"]))) {
            $resourceStatus_err = "Please enter a resource status.";
            $message=[
                'message','Please enter a resource status',
                'status','fail'
            ];
            return $message;
        } else {
            $resourceStatus = trim($data["re_status"]);
        }
        $quantity=$data['re_qty'];

        // Check if the resource already exists in resources table
        $sql_check_resource = "SELECT re_id FROM resources WHERE userId = :userId AND re_name = :re_name";
        $stmt_check_resource = $this->pdo->prepare($sql_check_resource);
        $stmt_check_resource->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt_check_resource->bindParam(':re_name', $resourceName, PDO::PARAM_STR);
        $stmt_check_resource->execute();
        $row_check_resource = $stmt_check_resource->fetch(PDO::FETCH_ASSOC);
        if($row_check_resource){
            $resourceName_err = "Resource already exists.";
            $message=[
                'message'=>$resourceName_err,
                'status'=>'fail'
            ];
            return $message;
        }else{
            // Check if the resource already exists in resourcesavailable table
            $sql_check_resourcesavailable = "SELECT re_id FROM resourcesavailable WHERE re_id = :re_id";
            $stmt_check_resourcesavailable = $this->pdo->prepare($sql_check_resourcesavailable);
            $stmt_check_resourcesavailable->bindParam(':re_id', $row_check_resource['re_id'], PDO::PARAM_INT);
            $stmt_check_resourcesavailable->execute();
            $row_check_resourcesavailable = $stmt_check_resourcesavailable->fetch(PDO::FETCH_ASSOC);

            if ($row_check_resourcesavailable) {
                $resourceName_err = "Resource Availbale already exists.";
                $message=[
                    'message'=>$resourceName_err,
                    'status'=>'fail'
                ];
                return $message;
            } else {
            
                // Insert resource into resources table
                $sql_insert_resource = "INSERT INTO resources (userId, re_name, re_status) VALUES (:userId, :re_name, :re_status)";
                $stmt_insert_resource = $this->pdo->prepare($sql_insert_resource);
                $stmt_insert_resource->bindParam(':userId', $data['userId'], PDO::PARAM_INT);
                $stmt_insert_resource->bindParam(':re_name', $resourceName, PDO::PARAM_STR);
                $stmt_insert_resource->bindParam(':re_status', $resourceStatus, PDO::PARAM_STR);

                if ($stmt_insert_resource->execute()) {
                    // Get the re_id of the newly inserted resource
                    $re_id = $this->pdo->lastInsertId();

                    // Insert resource into resourcesavailable table
                    $sql_insert_resourcesavailable = "INSERT INTO resourcesavailable (re_id, qty, date) VALUES (:re_id, {$data['re_qty']}, NOW())";
                    $stmt_insert_resourcesavailable = $this->pdo->prepare($sql_insert_resourcesavailable);
                    $stmt_insert_resourcesavailable->bindParam(':re_id', $re_id, PDO::PARAM_INT);
                    $stmt_insert_resourcesavailable->execute();                                    
                    if($stmt_insert_resourcesavailable){
                        // Insert a new record into the historical table with status "Addition"
                        $insertSql = "INSERT INTO historical (re_id, quantity, operation, date, status) 
                        VALUES (:re_id, :quantity, 'Addition', CURDATE(), 'completed')";
                        $insertStmt = $this->pdo->prepare($insertSql);
                        $insertStmt->bindParam(':re_id', $data["re_id"], PDO::PARAM_INT);
                        $insertStmt->bindParam(':quantity', $data['re_qty'], PDO::PARAM_INT);
                        $insertStmt->execute(); 
                        if($insertStmt){
                            $message = "Successfully added " . $quantity . "kg to resource called " . $data['re_name'];
                            $status = 'success';
                        } else {
                            $message = "Failed to add resource";
                            $status = 'fail';
                        }
                    
                        return [
                            'message' => $message,
                            'status' => $status
                        ];
                       
                    }

                    
                } else {
                    echo "Something went wrong. Please try again later.";
                    $message=[
                        'message'=>'Something went wrong. Please try again later',
                        'status'=>'fail'
                    ];
                    return $message;
                }
                
            }
        }
        

        
    }

    public function checkUserByPhone($phone,$pin){
        // Query database to verify user credentials
        $sql = "SELECT * FROM Users WHERE phone = :phone";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        if($stmt->rowcount()>0){
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if($user && $pin==$user['pin']) {
                $_SESSION['user_id'] = $user['userId'];
               return $user['userId'];
            } else {
                $error = "Invalid username or password";
                return false;
            }  
        }
        
    }

    public function getUserByPhone($phone){
        // Query database to verify user credentials
        $sql = "SELECT * FROM Users WHERE phone = :phone";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        if($stmt->rowcount()>0){
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return  $user;
        }
        
    }

    public function getResources($userId, $resourceAvId) {
        if (empty($resourceAvId)) {
            $sql = "SELECT ra.*, r.*
                    FROM resourcesavailable AS ra
                    INNER JOIN resources AS r ON ra.re_id = r.re_id 
                    WHERE r.userId = :userId
                    GROUP BY ra.re_av_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        } else {
            $sql = "SELECT ra.*, r.*
                    FROM resourcesavailable AS ra
                    INNER JOIN resources AS r ON ra.re_id = r.re_id 
                    WHERE r.userId = :userId AND ra.re_av_id = :re_av_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':re_av_id', $resourceAvId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resources;
    }
    
    

    // Method to check if a user exists in the users table by phone number
    public function checkUserExistsByPhone($phone) {
        // Prepare a select statement
        $sql = "SELECT COUNT(*) as count FROM users WHERE phone = :phone";

        if ($stmt = $this->pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":phone", $phone, PDO::PARAM_STR);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Fetch result
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Return true if user exists, false otherwise
                return $result['count'] > 0;
            } else {
                // Handle execution error
                return false;
            }
        } else {
            // Handle prepare error
            return false;
        }
    }

    public function spendin($data){
        // Validate quantity input
        if (empty(trim($data["quantity"])) || !is_numeric($data["quantity"]) || (int)$data["quantity"] <= 0) {
            $quantity_err = "Please enter a valid quantity.";
            return $quantity_err;
        } else {
            $quantity = (int)$data["quantity"];
            $resourceAvId=$data["re_av_id"];
            $re_id=$data["re_id"];
            $userId=$data['userId'];
            $reAvailable=$this->getResources($userId,$resourceAvId);
            foreach ($reAvailable as $key => $value) {
                $re_name=$value['re_name'];
            }

            
            // Check if the requested quantity is available
            if ($quantity > $data['qty']) {
                $message='Fail to remove '.$quantity.' Requested quantity exceeds available quantity ';
                $data=[
                    'message'=>$message,
                    'status'=>'fail'
                ];
                return $data;
            } else {
                // Deduct the spent quantity from available quantity
                $newQuantity = $data['qty'] - $quantity;
                $sqlUpdate = "UPDATE resourcesavailable SET qty = :newQuantity WHERE re_av_id = :re_av_id";
                $stmtUpdate = $this->pdo->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':newQuantity', $newQuantity, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':re_av_id', $data["re_av_id"], PDO::PARAM_INT);
                $stmtUpdate->execute();

                // Insert spending action into historical table
                if($stmtUpdate){
                    $operation = 'spending';
                    $date = date('Y-m-d');
                    $status = 'completed';
                    $sqlInsert = "INSERT INTO historical (re_id, quantity, operation, date, status) 
                                VALUES (:re_id, :quantity, :operation, :date, :status)";
                    $stmtInsert = $this->pdo->prepare($sqlInsert);
                    $stmtInsert->bindParam(':re_id', $re_id, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                    $stmtInsert->bindParam(':operation', $operation);
                    $stmtInsert->bindParam(':date', $date);
                    $stmtInsert->bindParam(':status', $status);
                    $stmtInsert->execute();
                    if($stmtInsert){
                        $message='Successfull to remove '.$quantity.' from  '.$re_name;
                        $data=[
                            'message'=>$message,
                            'status'=>'success'
                        ];
                        return $data;
                    } else {
                        $message='Fail to remove '.$quantity.' from  '.$re_name;
                        $data=[
                            'message'=>$message,
                            'status'=>'fail'
                        ];
                        return $data;
                    }

                }else{
                    $message='Fail to remove '.$quantity.' from  '.$re_name;
                    $data=[
                        'message'=>$message,
                        'status'=>'fail'
                    ];
                    return $data;
                }
            }
        }
    }

    public function savein($data){
        // Validate quantity input
        $quantity=$data['re_qty'];
        $resourceAvId=$data['re_av_id'];
        $re_Id=$data['re_id'];
        $userId=$data['userId'];
        $reAvailable=$this->getResources($userId,$resourceAvId);
        foreach ($reAvailable as $key => $value) {
            $re_name=$value['re_name'];
        }
        if (!is_numeric($quantity) || $quantity <= 0) {
            $error = "Quantity must be a positive number.";
            $message=[
                'message'=>$error,
                'status'=>'fail'
            ];
            return $message;
        } else {
            $quantity=$data['re_qty'];
            // Update the resourcesavailable table to add the entered quantity
            $updateSql = "UPDATE resourcesavailable SET qty = :quantity WHERE re_av_id = :re_av_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->bindParam(':quantity', $data['qty'], PDO::PARAM_INT);
            $updateStmt->bindParam(':re_av_id', $resourceAvId, PDO::PARAM_INT);
            $updateStmt->execute();
            if($updateStmt){
                // Insert a new record into the historical table with status "Addition"
                $insertSql = "INSERT INTO historical (re_id, quantity, operation, date, status) 
                VALUES (:re_id, :quantity, 'Addition', CURDATE(), 'completed')";
                $insertStmt = $this->pdo->prepare($insertSql);
                $insertStmt->bindParam(':re_id',  $re_Id, PDO::PARAM_INT);
                $insertStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $insertStmt->execute(); 
                if($insertStmt){
                    $message = "Successfull to Add ".$quantity.'kg to recouce called '.$data['re_name'].' new on is '.$data['qty']." kg";
                    $message=[
                        'message'=>$message,
                        'status'=>'success'
                    ];
                    return $message;
                }
            }else{
                $error = "Not update recesource availbale.";
                $message=[
                    'message'=>$error,
                    'status'=>'fail'
                ];
                return $message;
            }
        }
    }

    

    // Method to update resources table
    public function updateResource($data) {
        // Check if the new resource name already exists in the database for the specified userId
        $existingResource = $this->getResourceByNameAndUserId($data['re_name'], $data['userId']);

        // If the new resource name already exists for the specified userId and it's not the current resource being updated
        if ($existingResource && $existingResource['re_id'] !== $data['re_id']) {
            return ['status' => 'fail', 'message' => 'Resource name already exists for the specified user.'];
        }

        // Prepare the SQL statement
        $sql = "UPDATE resources 
                SET re_name = :re_name, 
                    userId = :userId, 
                    re_status = :re_status 
                WHERE re_id = :re_id";

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':re_name', $data['new_name'], PDO::PARAM_STR);
        $stmt->bindParam(':userId', $data['userId'], PDO::PARAM_INT);
        $stmt->bindParam(':re_status', $data['re_status'], PDO::PARAM_STR);
        $stmt->bindParam(':re_id', $data['re_id'], PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Resource updated successfully.'];
        } else {
            return ['status' => 'fail', 'message' => 'Error updating resource.'];
        }
    }

    // Method to retrieve resource by name and userId
    private function getResourceByNameAndUserId($re_name, $userId) {
        $sql = "SELECT * FROM resources WHERE re_name = :re_name AND userId = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':re_name', $re_name, PDO::PARAM_STR);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Method to update user data
    public function updateUser($data) {
        // Prepare the SQL statement
        $sql = "UPDATE users 
                SET name = :newName,
                    phone = :phone,
                    pin = :pin,
                    date = :date,
                    status = :status
                WHERE userId = :userId";

        // Prepare the statement
        $stmt = $this->pdo->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':newName', $data['newName'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':pin', $data['newPin'], PDO::PARAM_STR);
        $stmt->bindParam(':date', $data['date'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
        $stmt->bindParam(':userId', $data['userId'], PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'User updated successfully.'];
        } else {
            return ['status' => 'fail', 'message' => 'Error updating user.'];
        }
    }
}
?>
