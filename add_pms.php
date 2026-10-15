<?php
session_start();
if (!isset($_SESSION['username'])) header("Location: login.php");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add PMS Entry</title>
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
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Add New PMS Entry</h4>
        </div>
        <div class="card-body">
            <form action="save_pms.php" method="POST">
                <div class="mb-3">
                    <label for="client_name" class="form-label">Client Name</label>
                    <input type="text" name="client_name" id="client_name" class="form-control" placeholder="Enter client name" required>
                </div>
                <div class="mb-3">
                    <label for="pms_no" class="form-label">PMS No</label>
                    <input type="text" name="pms_no" id="pms_no" class="form-control" placeholder="Enter PMS number" required>
                </div>
                <div class="mb-3">
                    <label for="principal_investment" class="form-label">Principal Investment (₹)</label>
                    <input type="number" name="principal_investment" id="principal_investment" step="0.01" class="form-control" placeholder="e.g. 500000" required>
                </div>
                <div class="mb-3">
                    <label for="monthly_payout" class="form-label">Monthly Payout (₹)</label>
                    <input type="number" name="monthly_payout" id="monthly_payout" step="0.01" class="form-control" placeholder="e.g. 2000" required>
                </div>
                <div class="mb-3">
                    <label for="current_value" class="form-label">Current Value (₹)</label>
                    <input type="number" name="current_value" id="current_value" step="0.01" class="form-control" placeholder="e.g. 600000" required>
                </div>
                <button type="submit" class="btn btn-success">Save Entry</button>
                <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
