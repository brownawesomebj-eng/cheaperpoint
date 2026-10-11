<?php
include 'config.php';
include 'header.php';

$total_drugs = $conn->query("SELECT COUNT(*) AS c FROM drugs")->fetch_assoc()['c'];
$total_units = $conn->query("SELECT COALESCE(SUM(quantity),0) AS c FROM drugs")->fetch_assoc()['c'];
$out_of_stock = $conn->query("SELECT COUNT(*) AS c FROM drugs WHERE quantity = 0")->fetch_assoc()['c'];
$low_stock = $conn->query("SELECT COUNT(*) AS c FROM drugs WHERE quantity > 0 AND quantity <= 10")->fetch_assoc()['c'];
?>

<div class="grid">
    <div class="stat-box">
        <h3><?= $total_drugs ?></h3>
        <p>Total Drugs</p>
    </div>
    <div class="stat-box">
        <h3><?= $total_units ?></h3>
        <p>Total Units in Stock</p>
    </div>
    <div class="stat-box">
        <h3><?= $low_stock ?></h3>
        <p>Running Low</p>
    </div>
    <div class="stat-box">
        <h3><?= $out_of_stock ?></h3>
        <p>Out of Stock</p>
    </div>
</div>

<div class="card">
    <h2>📋 Recent Activity</h2>
    <?php
    $recent = $conn->query("SELECT h.*, d.name 
                            FROM history h 
                            JOIN drugs d ON h.drug_id = d.id 
                            ORDER BY h.date_recorded DESC LIMIT 10");
    if ($recent->num_rows > 0): ?>
        <table>
            <tr>
                <th>Drug</th><th>Action</th><th>Quantity</th><th>Note</th><th>Date</th>
            </tr>
            <?php while($r = $recent->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td>
                    <span class="badge <?= $r['action']=='IN'?'badge-in':'badge-out' ?>">
                        <?= $r['action']=='IN' ? 'Stock In' : 'Stock Out' ?>
                    </span>
                </td>
                <td><?= $r['action']=='IN' ? '+' : '-' ?><?= $r['quantity'] ?></td>
                <td><?= htmlspecialchars($r['note'] ?: '—') ?></td>
                <td><?= date('M d, Y H:i', strtotime($r['date_recorded'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No activity yet. Start by adding stock.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
