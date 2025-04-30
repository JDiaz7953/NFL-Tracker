<?php
session_start();
require 'includes/database-connection.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch teams
$teams = pdo($pdo, "SELECT team_id, display_name FROM teams WHERE is_active = 1")->fetchAll();
$teamsMap = array_column($teams, 'display_name', 'team_id');

// Fetch player names and their team IDs
$allPlayersRaw = pdo($pdo, "SELECT full_name, team_id FROM player_info ORDER BY full_name")->fetchAll();
$playerMap = [];
foreach ($allPlayersRaw as $p) {
    $playerMap[$p['full_name']] = $p['team_id'];
}

$parlay_results = [];
$parlay_probability = null;

if (isset($_SESSION['parlay_results'])) {
    $parlay_results = $_SESSION['parlay_results'];
    $parlay_probability = $_SESSION['parlay_probability'];
    unset($_SESSION['parlay_results'], $_SESSION['parlay_probability']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['legs'])) {
    $legs = $_POST['legs'];

    foreach ($legs as $leg) {
        $player = trim($leg['player_name']);
        $value = (int)$leg['stat_value'];
        $type = $leg['stat_type'];
        $statCol = $leg['stat_name'];
        $overUnder = $leg['over_under'];
        $opponentTeamId = (int)$leg['opponent_team_id'];

        $rankingCol = ($type === 'passing') ? 'pass_defense_ranking' : 'run_defense_ranking';
        $statTable = match($type) {
            'passing' => 'passing_stats',
            'rushing' => 'rushing_stats',
            'receiving' => 'receiving_stats',
        };

        $rank = pdo($pdo, "SELECT $rankingCol FROM defense_rankings WHERE team_id = ? LIMIT 1", [$opponentTeamId])->fetchColumn();
        if (!$rank) {
            $parlay_results[] = ['player' => $player, 'prob' => 0, 'note' => 'Opponent rank not found'];
            continue;
        }

        $rankLow = max(1, $rank - 3);
        $rankHigh = min(32, $rank + 3);

        $playerId = pdo($pdo, "SELECT player_id FROM player_info WHERE full_name = ?", [$player])->fetchColumn();
        if (!$playerId) {
            $parlay_results[] = ['player' => $player, 'prob' => 0, 'note' => 'Player not found'];
            continue;
        }

        $sql = "
            SELECT s.$statCol
            FROM $statTable s
            JOIN game_info g ON s.game_id = g.game_id
            JOIN defense_rankings d ON g.away_team_id = d.team_id OR g.home_team_id = d.team_id
            WHERE s.player_id = ?
            AND d.$rankingCol BETWEEN ? AND ?
        ";
        $games = pdo($pdo, $sql, [$playerId, $rankLow, $rankHigh])->fetchAll();
        $total = count($games);
        $hits = 0;

        foreach ($games as $g) {
            $stat = (int)$g[$statCol];
            if (($overUnder === 'over' && $stat > $value) || ($overUnder === 'under' && $stat < $value)) {
                $hits++;
            }
        }

        $prob = $total > 0 ? $hits / $total : 0;
        $image = pdo($pdo, "SELECT image_url FROM player_info WHERE full_name = ?", [$player])->fetchColumn();

        $parlay_results[] = [
            'player' => $player,
            'stat_label' => str_replace('_', ' ', $statCol),
            'value' => $value,
            'over_under' => $overUnder,
            'opponent_name' => $teamsMap[$opponentTeamId] ?? 'Unknown',
            'prob' => $prob,
            'image' => $image
        ];
    }

    $parlay_probability = array_reduce($parlay_results, fn($carry, $item) => $carry * $item['prob'], 1);

    $_SESSION['parlay_results'] = $parlay_results;
    $_SESSION['parlay_probability'] = $parlay_probability;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>NFL Parlay Predictor</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1 class="mb-4">NFL Parlay Predictor</h1>

        <form method="POST" id="parlayForm">
            <button type="submit" class="btn btn-success mb-3">Calculate Parlay Likelihood</button>
            <div id="legsContainer"></div>
            <button type="button" class="btn btn-outline-primary" id="addLegBtn">+ Add Leg</button>
        </form>

        <?php if (!empty($parlay_results)): ?>
            <div class="row mt-4">
                <?php foreach ($parlay_results as $result): ?>
                    <div class="col-md-3">
                        <div class="card">
                            <?php if (!empty($result['image'])): ?>
                                <img src="<?= htmlspecialchars($result['image']) ?>" alt="<?= $result['player'] ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/64?text=?" alt="No Image">
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($result['player']) ?></strong>
                            <div><?= ucfirst($result['stat_label']) ?> <?= ucfirst($result['over_under']) ?> <?= $result['value'] ?></div>
                            <div>vs. <?= htmlspecialchars($result['opponent_name']) ?></div>
                            <div>Likelihood: <?= round($result['prob'] * 100) ?>%</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <h5 class="mt-3 text-light">Total Parlay Likelihood: <strong><?= round($parlay_probability * 100, 2) ?>%</strong></h5>
        <?php endif; ?>
    </div>

    <script>
        let legIndex = 0;
        const teams = <?= json_encode($teams) ?>;
        const playerTeams = <?= json_encode($playerMap) ?>;

        const statOptions = {
            passing: [
                { value: 'passing_yards', label: 'Passing Yards' },
                { value: 'passing_touchdowns', label: 'Passing Touchdowns' }
            ],
            rushing: [
                { value: 'rush_yards', label: 'Rushing Yards' },
                { value: 'rushing_touchdowns', label: 'Rushing Touchdowns' }
            ],
            receiving: [
                { value: 'receiving_yards', label: 'Receiving Yards' },
                { value: 'receiving_touchdowns', label: 'Receiving Touchdowns' }
            ]
        };

        function buildOpponentOptions(playerName) {
            const playerTeam = playerTeams[playerName];
            return teams
                .filter(t => t.team_id !== playerTeam)
                .map(t => `<option value="${t.team_id}">${t.display_name}</option>`)
                .join('');
        }

        function createLegRow(index) {
            const row = document.createElement('div');
            row.className = 'row align-items-end leg-row';
            row.dataset.index = index;

            row.innerHTML = `
                <div class="col">
                    <input type="text" class="form-control player-name" name="legs[${index}][player_name]" placeholder="Player Name" required list="playerList${index}" data-index="${index}">
                    <datalist id="playerList${index}">
                        ${Object.keys(playerTeams).map(name => `<option value="${name}">`).join('')}
                    </datalist>
                </div>
                <div class="col"><input type="number" class="form-control" name="legs[${index}][stat_value]" placeholder="Stat Value" required></div>
                <div class="col">
                    <select class="form-select stat-type" name="legs[${index}][stat_type]" data-index="${index}" required>
                        <option value="">Stat Type</option>
                        <option value="passing">Passing</option>
                        <option value="rushing">Rushing</option>
                        <option value="receiving">Receiving</option>
                    </select>
                </div>
                <div class="col">
                    <select class="form-select stat-name" name="legs[${index}][stat_name]" required>
                        <option value="">Choose Stat</option>
                    </select>
                </div>
                <div class="col">
                    <select class="form-select opponent-team" name="legs[${index}][opponent_team_id]" required>
                        <option value="">Team</option>
                    </select>
                </div>
                <div class="col">
                    <select class="form-select" name="legs[${index}][over_under]" required>
                        <option value="over">Over</option>
                        <option value="under">Under</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger remove-leg" title="Remove">🗑</button>
                </div>
            `;
            return row;
        }

        function addLeg() {
            const container = document.getElementById('legsContainer');
            const newRow = createLegRow(legIndex);
            container.appendChild(newRow);
            legIndex++;
        }

        function updateStatDropdown(selectEl) {
            const index = selectEl.dataset.index;
            const statSelect = document.querySelector(`select[name="legs[${index}][stat_name]"]`);
            const type = selectEl.value;

            if (statOptions[type]) {
                statSelect.innerHTML = statOptions[type].map(opt =>
                    `<option value="${opt.value}">${opt.label}</option>`
                ).join('');
            } else {
                statSelect.innerHTML = '<option value="">Choose Stat</option>';
            }
        }

        document.getElementById('addLegBtn').addEventListener('click', addLeg);

        document.getElementById('legsContainer').addEventListener('change', function (e) {
            if (e.target.classList.contains('stat-type')) {
                updateStatDropdown(e.target);
            }
        });

        document.getElementById('legsContainer').addEventListener('input', function (e) {
            if (e.target.classList.contains('player-name')) {
                const index = e.target.dataset.index;
                const teamSelect = document.querySelector(`select[name="legs[${index}][opponent_team_id]"]`);
                teamSelect.innerHTML = '<option value="">Team</option>' + buildOpponentOptions(e.target.value);
            }
        });

        document.getElementById('legsContainer').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-leg')) {
                const rows = document.querySelectorAll('.leg-row');
                if (rows.length > 1) {
                    e.target.closest('.leg-row').remove();
                } else {
                    alert("You must have at least one parlay leg.");
                }
            }
        });

        // Initial leg
        addLeg();
    </script>
</body>
</html>
