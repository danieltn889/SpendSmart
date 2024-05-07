<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if the ID parameter is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list_resources.php");
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

    // Check input errors before updating the database
    if (empty($resourceName_err) && empty($resourceStatus_err)) {
        // Prepare an update statement
        $sql = "UPDATE resources SET re_name = :re_name, re_status = :re_status WHERE re_id = :re_id AND userId = :userId";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":re_name", $resourceName, PDO::PARAM_STR);
            $stmt->bindParam(":re_status", $resourceStatus, PDO::PARAM_STR);
            $stmt->bindParam(":re_id", $_GET['id'], PDO::PARAM_INT);
            $stmt->bindParam(":userId", $_SESSION['user_id'], PDO::PARAM_INT);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to list_resources.php page
                header("location: list_resources.php");
                exit;
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }

        // Close statement
        unset($stmt);
    }

    // Close connection
    unset($pdo);
} else {
    // Retrieve the resource information based on the ID parameter
    $sql = "SELECT re_name, re_status FROM resources WHERE re_id = :re_id AND userId = :userId";
    if ($stmt = $pdo->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":re_id", $_GET['id'], PDO::PARAM_INT);
        $stmt->bindParam(":userId", $_SESSION['user_id'], PDO::PARAM_INT);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Fetch the result
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if the result is not empty
            if ($row) {
                // Retrieve values from result set
                $resourceName = $row['re_name'];
                $resourceStatus = $row['re_status'];
            } else {
                // Redirect to list_resources.php page
                header("location: list_resources.php");
                exit;
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    }

    // Close statement
    unset($stmt);

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resource</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>Edit Resource</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $_GET['id']; ?>" method="post">
        <div class="form-group">
            <label>Resource Name</label>
            <input type="text" name="resource_name" class="form-control <?php echo (!empty($resourceName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $resourceName; ?>">
            <span class="invalid-feedback"><?php echo $resourceName_err; ?></span>
        </div>
        <div class="form-group">
            <label>Resource Status</label>
            <select class="form-control <?php echo (!empty($resourceStatus_err)) ? 'is-invalid' : ''; ?>" name="resource_status">
                <option selected value="<?php echo $resourceStatus ?>"><?php echo $resourceStatus ?></option>
                <?php if ($resourceStatus !== "Active") : ?>
                    <option value="Active">Active</option>
                <?php endif; ?>
                <?php if ($resourceStatus !== "Disactive") : ?>
                    <option value="Disactive">Disactive</option>
                <?php endif; ?>
            </select>

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
