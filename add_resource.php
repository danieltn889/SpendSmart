<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Define variables and initialize with empty values
$resourceName = $resourceStatus = "";
$resourceName_err = $resourceStatus_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate resource name
    if (empty(trim($_POST["resource_name"]))) {
        $resourceName_err = "Please enter a resource name.";
    } else {
        $resourceName = trim($_POST["resource_name"]);
    }

    // Validate resource status
    if (empty(trim($_POST["resource_status"]))) {
        $resourceStatus_err = "Please enter a resource status.";
    } else {
        $resourceStatus = trim($_POST["resource_status"]);
    }

    // Check if the resource already exists in resources table
    $sql_check_resource = "SELECT re_id FROM resources WHERE userId = :userId AND re_name = :re_name";
    $stmt_check_resource = $pdo->prepare($sql_check_resource);
    $stmt_check_resource->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt_check_resource->bindParam(':re_name', $resourceName, PDO::PARAM_STR);
    $stmt_check_resource->execute();
    $row_check_resource = $stmt_check_resource->fetch(PDO::FETCH_ASSOC);

    // Check if the resource already exists in resourcesavailable table
    $sql_check_resourcesavailable = "SELECT re_id FROM resourcesavailable WHERE re_id = :re_id";
    $stmt_check_resourcesavailable = $pdo->prepare($sql_check_resourcesavailable);
    $stmt_check_resourcesavailable->bindParam(':re_id', $row_check_resource['re_id'], PDO::PARAM_INT);
    $stmt_check_resourcesavailable->execute();
    $row_check_resourcesavailable = $stmt_check_resourcesavailable->fetch(PDO::FETCH_ASSOC);

    if ($row_check_resourcesavailable) {
        $resourceName_err = "Resource already exists.";
    } else {
        // Insert resource into resources table
        $sql_insert_resource = "INSERT INTO resources (userId, re_name, re_status) VALUES (:userId, :re_name, :re_status)";
        $stmt_insert_resource = $pdo->prepare($sql_insert_resource);
        $stmt_insert_resource->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt_insert_resource->bindParam(':re_name', $resourceName, PDO::PARAM_STR);
        $stmt_insert_resource->bindParam(':re_status', $resourceStatus, PDO::PARAM_STR);

        if ($stmt_insert_resource->execute()) {
            // Get the re_id of the newly inserted resource
            $re_id = $pdo->lastInsertId();

            // Insert resource into resourcesavailable table
            $sql_insert_resourcesavailable = "INSERT INTO resourcesavailable (re_id, qty, date) VALUES (:re_id, 0, NOW())";
            $stmt_insert_resourcesavailable = $pdo->prepare($sql_insert_resourcesavailable);
            $stmt_insert_resourcesavailable->bindParam(':re_id', $re_id, PDO::PARAM_INT);
            $stmt_insert_resourcesavailable->execute();

            // Redirect to list_resources.php page
            header("location: list_resources.php");
            exit;
        } else {
            echo "Something went wrong. Please try again later.";
        }
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
    <title>Add Resource</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Add Resource</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Resource Name</label>
            <input type="text" name="resource_name" class="form-control <?php echo (!empty($resourceName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $resourceName; ?>">
            <span class="invalid-feedback"><?php echo $resourceName_err; ?></span>
        </div>
        <div class="form-group">
            <input type="hidden" name="resource_status" class="form-control <?php echo (!empty($resourceStatus_err)) ? 'is-invalid' : ''; ?>" value="<?php echo 'Active'; ?>">
            <span class="invalid-feedback"><?php echo $resourceStatus_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Submit">
            <a href="list_resources.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
