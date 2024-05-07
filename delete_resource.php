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

// Prepare a delete statement
$sql = "DELETE FROM resources WHERE re_id = :re_id AND userId = :userId";

if ($stmt = $pdo->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bindParam(":re_id", $_GET['id'], PDO::PARAM_INT);
    $stmt->bindParam(":userId", $_SESSION['user_id'], PDO::PARAM_INT);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Redirect to list_resources.php page
        header("Location: list_resources.php");
        exit;
    } else {
        echo "Something went wrong. Please try again later.";
    }
}

// Close connection
unset($pdo);
?>
