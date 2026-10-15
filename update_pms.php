<?php
session_start();
if (!isset($_SESSION['username'])) header("Location: login.php");
include 'database.php';

$id = $_POST['id'];
$client = $_POST['client_name'];
$pms_no = $_POST['pms_no'];
$principal = $_POST['principal_investment'];
$payout = $_POST['monthly_payout'];
$current = $_POST['current_value'];

$stmt = $mysqli->prepare("UPDATE pms_entries SET client_name=?, pms_no=?, principal_investment=?, monthly_payout=?, current_value=? WHERE id=?");
$stmt->bind_param("ssdddi", $client, $pms_no, $principal, $payout, $current, $id);
$stmt->execute();

header("Location: dashboard.php");
