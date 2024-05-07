<?php
session_start();

// Redirect user to dashboard if already logged in
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Define variables and initialize with empty values
$username = $name = $phone = $pin = "";
$username_err = $name_err = $phone_err = $pin_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Prepare a select statement
    $sql = "SELECT userId FROM Users WHERE phone = :phone";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":phone", $param_phone, PDO::PARAM_STR);

        // Set parameters
        $param_phone = trim($_POST["phone"]);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            if($stmt->rowCount() == 1){
                $username_err = "This phone number is already regitered.";
                ?><script type="text/javascript">
                    window.alert('<?php echo $username_err; ?>')
                    </script><?php
            } else{
                $phone = trim($_POST["phone"]);
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    // Close statement
    unset($stmt);


    // Validate name
    if(empty(trim($_POST['name']))){
        $name_err = "Please enter your name.";     
    } else{
        $name = trim($_POST['name']);
    }

    // Validate phone number
    if(empty(trim($_POST['phone']))){
        $phone_err = "Please enter your phone number.";     
    } else{
        $phone = trim($_POST['phone']);
    }

    // Validate PIN
    if(empty(trim($_POST['pin']))){
        $pin_err = "Please enter your PIN.";     
    } else{
        $pin = trim($_POST['pin']);
    }

    // Check input errors before inserting in database
    if(empty($username_err) && empty($name_err) && empty($phone_err) && empty($pin_err)){

        // Prepare an insert statement
        $sql = "INSERT INTO Users (name, phone, pin, date, status) VALUES (:name, :phone, :pin, NOW(), 'active')";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":name", $param_name, PDO::PARAM_STR);
            $stmt->bindParam(":phone", $param_phone, PDO::PARAM_STR);
            $stmt->bindParam(":pin", $param_pin, PDO::PARAM_STR);

            // Set parameters
            $password= md5($pin);
            $param_name = $name;
            $param_phone = $phone;
            $param_pin = $password ;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to login page
                ?><script type="text/javascript">
                    window.alert('Successful to signup')
                </script><?php
                header("location: login.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        unset($stmt);
    }

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-3">
            
            <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                <span class="help-block"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($phone_err)) ? 'has-error' : ''; ?>">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>" pattern="[0-9]{10}" title="Please enter a 10-digit phone number">
                <span class="help-block"><?php echo $phone_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($pin_err)) ? 'has-error' : ''; ?>">
                <label>PIN</label>
                <input type="password" name="pin" class="form-control" value="<?php echo $pin; ?>">
                <span class="help-block"><?php echo $pin_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>    
    <!-- Bootstrap JS (optional, if you need JavaScript features) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
