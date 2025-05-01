<?php
require 'includes/database-connection.php';

$teams = pdo($pdo, "SELECT team_id, display_name FROM teams WHERE is_active = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $team_id = (int)$_POST['team_id'];

    if ($name && $team_id) {
        // Manually compute next player_id
        $nextId = pdo($pdo, "SELECT MAX(player_id) FROM players")->fetchColumn();
        $nextId = $nextId ? $nextId + 1 : 1;

        // Insert with manually assigned ID
        pdo($pdo, "INSERT INTO players (player_id, full_name, team_id) VALUES (?, ?, ?)", [
            $nextId,
            $name,
            $team_id
        ]);

        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Add New Player</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Add New Player</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="full_name" class="form-label">Player Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="team_id" class="form-label">Team</label>
                <select name="team_id" id="team_id" class="form-select" required>
                    <option value="">Select a Team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['display_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Player</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>
