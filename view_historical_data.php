<?php
session_start();

// Redirect user to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Initialize variables
$historicalData = array();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get selected starting and ending dates from the form
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Fetch historical data within the selected date range and for the current user
    $userId = $_SESSION['user_id'];
    $sql = "SELECT h.*, r.re_name FROM historical h
            INNER JOIN resources r ON h.re_id = r.re_id
            WHERE h.date BETWEEN :startDate AND :endDate AND r.userId = :userId";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Historical Data</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">
    <h2>View Historical Data</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="start_date">Select Start Date:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <div class="form-group col-md-5">
                <label for="end_date">Select End Date:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>
            <div class="form-group col-md-2">
                <button type="submit" class="btn btn-primary" style="margin-top: 30px;">Submit</button>
            </div>
        </div>
    </form>

    <?php if (!empty($historicalData)) : ?>
    <h3>Historical Data</h3>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th rowspan="2">Resource Name</th>
                <th>Quantity</th>
                <th>Operation</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $prevResource = null; $rowspan = 0; ?>
            <?php foreach ($historicalData as $key => $row) : ?>
            <?php if ($row['re_name'] !== $prevResource) : ?>
                <?php if ($prevResource !== null) : ?>
                    <?php for ($i = 0; $i < $rowspan; $i++) : ?>
                        <tr>
                        
                    <?php endfor; ?>
                <?php endif; ?>
                <?php $prevResource = $row['re_name']; $rowspan = 1; ?>
                <tr>
                    <td><?php echo $key + 1; ?></td>
                    <td rowspan="<?php echo $rowspan; ?>"><?php echo $row['re_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['operation']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php else : ?>
                <tr>
                    <td><?php echo $key + 1; ?></td>
                    <td><?php echo $row['re_name']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['operation']; ?></td>
                    <td><?php echo $row['date']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                </tr>
            <?php $rowspan++; ?>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($prevResource !== null) : ?>
                <?php for ($i = 0; $i < $rowspan - 1; $i++) : ?>
                    
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
