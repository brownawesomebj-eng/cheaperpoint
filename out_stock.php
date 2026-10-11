<?php
include 'config.php';
include 'header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $drug_id = intval($_POST['drug_id']);
    $quantity = intval($_POST['quantity']);
    $note = trim($_POST['note']);

    if ($drug_id <= 0 || $quantity <= 0) {
        $message = "<div class='alert alert-error'>Please select a drug and enter valid quantity.</div>";
    } else {
        $drug = $conn->query("SELECT name, quantity FROM drugs WHERE id=$drug_id")->fetch_assoc();

        if (!$drug) {
            $message = "<div class='alert alert-error'>Drug not found.</div>";
        } elseif ($drug['quantity'] < $quantity) {
            $message = "<div class='alert alert-error'>✗ Not enough stock! Only <b>{$drug['quantity']} units</b> available.</div>";
        } else {
            $stmt = $conn->prepare("UPDATE drugs SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $drug_id);
            $stmt->execute();
            $stmt->close();

            $stmt2 = $conn->prepare("INSERT INTO history (drug_id, action, quantity, note) VALUES (?, 'OUT', ?, ?)");
            $stmt2->bind_param("iis", $drug_id, $quantity, $note);
            $stmt2->execute();
            $stmt2->close();

            $remaining = $drug['quantity'] - $quantity;
            $message = "<div class='alert alert-success'>✓ Removed <b>$quantity units</b> of '".htmlspecialchars($drug['name'])."'. Remaining: <b>$remaining units</b>.</div>";
        }
    }
}

$drugs = $conn->query("SELECT id, name, quantity FROM drugs WHERE quantity > 0 ORDER BY name");
?>

<div class="card">
    <h2>📤 Stock Out (Drugs Going Out)</h2>
    <?= $message ?>

    <?php if ($drugs->num_rows == 0): ?>
        <p>No drugs available in stock. Please add stock first.</p>
    <?php else: ?>
    <form method="POST">
        <label>Select Drug *</label>
        <select name="drug_id" required>
            <option value="">-- Choose a drug --</option>
            <?php while($d = $drugs->fetch_assoc()): ?>
                <option value="<?= $d['id'] ?>">
                    <?= htmlspecialchars($d['name']) ?> (available: <?= $d['quantity'] ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <label>Quantity Going Out *</label>
        <input type="number" name="quantity" min="1" required placeholder="e.g., 5">

        <label>Note (optional)</label>
        <input type="text" name="note" placeholder="e.g., Sold to John / Ward A">

        <button type="submit" class="btn btn-out">➖ Remove Stock</button>
    </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
