<?php $current = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>💊 Pharmacy System</h1>
    <p>Simple Stock Management</p>
</header>
<nav>
    <a href="index.php" class="<?= $current=='index.php'?'active':'' ?>">Dashboard</a>
    <a href="add_stock.php" class="<?= $current=='add_stock.php'?'active':'' ?>">Add Stock (In)</a>
    <a href="out_stock.php" class="<?= $current=='out_stock.php'?'active':'' ?>">Stock Out</a>
    <a href="view_stock.php" class="<?= $current=='view_stock.php'?'active':'' ?>">View Stock</a>
</nav>
<div class="container">
