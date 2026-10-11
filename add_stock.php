<?php
include 'config.php';
include 'header.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode']; // 'new' or 'existing'

    if ($mode === 'new') {
        // Add brand new drug
        $name = trim($_POST['name']);
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        if (empty($name) || $quantity <= 0) {
            $message = "<div class='alert alert-error'>Please enter a valid drug name and quantity.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO drugs (name, quantity, price) VALUES (?, ?, ?)");
            $stmt->bind_param("sid", $name, $quantity, $price);
            if ($stmt->execute()) {
                $id = $stmt->insert_id;
                $conn->query("INSERT INTO history (drug_id, action, quantity, note) 
                              VALUES ($id, 'IN', $quantity, 'New drug added')");
                $message = "<div class='alert alert-success'>✓ New drug '<b>".htmlspecialchars($name)."</b>' added with $quantity units.</div>";
            } else {
                $message = "<div class='alert alert-error'>Error: ".$stmt->error."</div>";
            }
            $stmt->close();
        }
    } else {
        // Add stock to existing drug
        $drug_id = intval($_POST['drug_id']);
        $quantity = intval($_POST['quantity']);
        $note = trim($_POST['note']);

        if ($drug_id <= 0 || $quantity <= 0) {
            $message = "<div class='alert alert-error'>Please select a drug and enter valid quantity.</div>";
        } else {
            $stmt = $conn->prepare("UPDATE drugs SET quantity = quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $quantity, $drug_id);
            if ($stmt->execute()) {
                $stmt2 = $conn->prepare("INSERT INTO history (drug_id, action, quantity, note) VALUES (?, 'IN', ?, ?)");
                $stmt2->bind_param("iis", $drug_id, $quantity, $note);
                $stmt2->execute();
                $stmt2->close();

                $name = $conn->query("SELECT name FROM drugs WHERE id=$drug_id")->fetch_assoc()['name'];
                $message = "<div class='alert alert-success'>✓ Added <b>$quantity units</b> of '".htmlspecialchars($name)."'.</div>";
            } else {
                $message = "<div class='alert alert-error'>Error: ".$stmt->error."</div>";
            }
            $stmt->close();
        }
    }
}

$drugs = $conn->query("SELECT id, name, quantity FROM drugs ORDER BY name");
?>

<div class="card">
    <h2>📥 Add Stock (Incoming Drugs)</h2>
    <?= $message ?>

    <div style="margin-bottom:20px;">
        <button type="button" class="btn" onclick="showTab('existing')" id="tab-btn-existing">Add to Existing Drug</button>
        <button type="button" class="btn btn-out" onclick="showTab('new')" id="tab-btn-new">Add New Drug</button>
    </div>

    <!-- Add to existing drug -->
    <div id="tab-existing">
        <form method="POST">
            <input type="hidden" name="mode" value="existing">

            <label>Select Drug *</label>
            <select name="drug_id" required>
                <option value="">-- Choose a drug --</option>
                <?php while($d = $drugs->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['name']) ?> (currently: <?= $d['quantity'] ?> units)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Quantity to Add *</label>
            <input type="number" name="quantity" min="1" required placeholder="e.g., 50">

            <label>Note (optional)</label>
            <input type="text" name="note" placeholder="e.g., Received from supplier ABC">

            <button type="submit" class="btn">➕ Add Stock</button>
        </form>
    </div>

    <!-- Add new drug -->
    <div id="tab-new" style="display:none;">
        <form method="POST">
            <input type="hidden" name="mode" value="new">

            <label>Drug Name *</label>
            <input type="text" name="name" required placeholder="e.g., Paracetamol">

            <label>Initial Quantity *</label>
            <input type="number" name="quantity" min="1" required placeholder="e.g., 100">

            <label>Price per Unit ($)</label>
            <input type="number" name="price" step="0.01" min="0" value="0">

            <button type="submit" class="btn">➕ Add New Drug</button>
        </form>
    </div>
</div>

<script>
function showTab(tab) {
    document.getElementById('tab-existing').style.display = tab === 'existing' ? 'block' : 'none';
    document.getElementById('tab-new').style.display = tab === 'new' ? 'block' : 'none';
}
</script>

<?php include 'footer.php'; ?>
