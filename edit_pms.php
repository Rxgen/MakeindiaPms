<?php
session_start();
if (!isset($_SESSION['username'])) header("Location: login.php");
include 'database.php';

$id = $_GET['id'];
$result = $mysqli->query("SELECT * FROM pms_entries WHERE id = $id");
$data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit PMS Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 40px auto;
        }
    </style>
</head>
<body>

<div class="container form-container">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Edit PMS Entry</h4>
        </div>
        <div class="card-body">
            <form action="update_pms.php" method="POST">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="mb-3">
                    <label for="client_name" class="form-label">Client Name</label>
                    <input type="text" class="form-control" name="client_name" id="client_name" value="<?= $data['client_name'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="pms_no" class="form-label">PMS No</label>
                    <input type="text" class="form-control" name="pms_no" id="pms_no" value="<?= $data['pms_no'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="principal_investment" class="form-label">Principal Investment (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="principal_investment" id="principal_investment" value="<?= $data['principal_investment'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="monthly_payout" class="form-label">Monthly Payout (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="monthly_payout" id="monthly_payout" value="<?= $data['monthly_payout'] ?>" required>
                </div>

                <div class="mb-3">
                    <label for="current_value" class="form-label">Current Value (₹)</label>
                    <input type="number" step="0.01" class="form-control" name="current_value" id="current_value" value="<?= $data['current_value'] ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Entry</button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
