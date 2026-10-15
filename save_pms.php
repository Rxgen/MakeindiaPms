<?php
session_start();
if (!isset($_SESSION['username'])) header("Location: login.php");
include 'database.php';

$client = $_POST['client_name'];
$pms_no = $_POST['pms_no'];
$principal = $_POST['principal_investment'];
$payout = $_POST['monthly_payout'];
$current = $_POST['current_value'];

$stmt = $mysqli->prepare("INSERT INTO pms_entries (client_name, pms_no, principal_investment, monthly_payout, current_value) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssddd", $client, $pms_no, $principal, $payout, $current);
$stmt->execute();

header("Location: dashboard.php");
