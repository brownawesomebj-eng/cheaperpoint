<?php
include 'config.php';
include 'header.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM drugs";
if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " WHERE name LIKE '%$s%'";
}
$sql .= " ORDER BY quantity DESC, name ASC";
$result = $conn->query($sql);
?>

<div class="card">
    <h2>📦 Current Stock</h2>

    <form method="GET" style="display:flex; gap:10px; margin-bottom:15px;">
        <input type="text" name="search" placeholder="Search drug..." value="<?= htmlspecialchars($search) ?>" style="margin:0;">
        <button type="submit" class="btn" style="margin:0;">Search</button>
        <?php if($search): ?>
            <a href="view_stock.php" class="btn btn-out" style="margin:0; text-decoration:none; display:inline-block;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Drug Name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Status</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): 
            if ($row['quantity'] == 0) {
                $status = "<span class='badge badge-low'>Out of Stock</span>";
            } elseif ($row['quantity'] <= 10) {
                $status = "<span class='badge badge-out'>Running Low</span>";
            } else {
                $status = "<span class='badge badge-ok'>In Stock</span>";
            }
        ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><b><?= $row['quantity'] ?></b></td>
            <td>$<?= number_format($row['price'], 2) ?></td>
            <td><?= $status ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No drugs found. <a href="add_stock.php" style="color:#0b6e3f;">Add your first drug →</a></p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
