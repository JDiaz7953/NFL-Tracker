<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'includes/database-connection.php';

$player = $_GET['player'] ?? '';
$type = $_GET['type'] ?? '';
$stat = $_GET['stat'] ?? '';
$opponentTeamId = (int)($_GET['opponent'] ?? 0);

if (!$player || !$type || !$stat || !$opponentTeamId) {
    die("Missing or invalid parameters.");
}

$rankingCol = 'total_defense_ranking';

$statTable = match($type) {
    'passing' => 'passing_stats',
    'rushing' => 'rushing_stats',
    'receiving' => 'receiving_stats',
    default => null
};

if (!$statTable) {
    die("Invalid stat type.");
}

$rank = pdo($pdo, "SELECT $rankingCol FROM defense_rankings WHERE team_id = ? LIMIT 1", [$opponentTeamId])->fetchColumn();
if (!$rank) {
    die("Opponent ranking not found.");
}

$rankLow = max(1, $rank - 3);
$rankHigh = min(32, $rank + 3);

$playerInfo = pdo($pdo, "SELECT player_id, image_url, team_id FROM player_info WHERE full_name = ?", [$player])->fetch();
if (!$playerInfo) {
    die("Player not found.");
}

$playerId = $playerInfo['player_id'];
$imageUrl = $playerInfo['image_url'];
$playerTeamId = $playerInfo['team_id'];

$sql = "
    SELECT 
        g.week,
        g.season_year,
        CASE 
            WHEN g.home_team_id = pi.team_id THEN g.away_team_id
            ELSE g.home_team_id
        END AS opponent_team_id,
        t.display_name AS opponent_name,
        s.$stat
    FROM $statTable s
    JOIN game_info g ON s.game_id = g.game_id
    JOIN player_info pi ON s.player_id = pi.player_id
    JOIN defense_rankings d ON g.away_team_id = d.team_id OR g.home_team_id = d.team_id
    JOIN teams t ON t.team_id = 
        CASE 
            WHEN g.home_team_id = pi.team_id THEN g.away_team_id
            ELSE g.home_team_id
        END
    WHERE s.player_id = ?
    AND d.$rankingCol BETWEEN ? AND ?
    ORDER BY g.season_year DESC, g.week DESC
";

$games = pdo($pdo, $sql, [$playerId, $rankLow, $rankHigh])->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($player) ?> Gamelog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="p-4">
    <div class="container">
        <div class="player-header">
            <?php if ($imageUrl): ?>
                <div class="player-image-wrapper">
                    <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($player) ?>" class="player-thumb">
                </div>
            <?php endif; ?>
            <div class="player-name"><?= htmlspecialchars($player) ?></div>
        </div>

        <h4 class="mb-3 text-center">Gamelog vs. Similarly Ranked Teams (by Total Defense)</h4>

        <?php if ($games): ?>
            <table class="table table-dark table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Season</th>
                        <th>Week</th>
                        <th>Opponent</th>
                        <th><?= ucfirst(str_replace('_', ' ', $stat)) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?= htmlspecialchars($game['season_year']) ?></td>
                            <td><?= htmlspecialchars($game['week']) ?></td>
                            <td><?= htmlspecialchars($game['opponent_name']) ?></td>
                            <td><?= htmlspecialchars($game[$stat]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                No games found against similarly ranked defenses.
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">← Back to Parlay Predictor</a>
        </div>
    </div>
</body>
</html>
