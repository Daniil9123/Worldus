<?php
include "config/db.php";
include "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$category_id = isset($_GET['category'])
    ? (int)$_GET['category']
    : 1;
?>

<div class="levels-page">
    <h1 class="levels-title">Уровни</h1>

    <div class="levels-map">

<?php
$result = $conn->query("
    SELECT *
    FROM levels
    WHERE category_id = $category_id
    ORDER BY level_order ASC
");

while ($level = $result->fetch_assoc()) {

    $level_id = (int)$level['id'];

    $locked = false;

    if (!empty($level['required_level'])) {
        $required_level = (int)$level['required_level'];

        $check = $conn->query("
            SELECT id
            FROM progress
            WHERE user_id = $user_id
            AND level_id = $required_level
            AND completed = 1
            LIMIT 1
        ");

        if ($check->num_rows == 0) {
            $locked = true;
        }
    }

    $done = $conn->query("
        SELECT id
        FROM progress
        WHERE user_id = $user_id
        AND level_id = $level_id
        AND completed = 1
        LIMIT 1
    ");

    $completed = $done->num_rows > 0;
?>

        <div class="level-item">
            <?php if ($locked): ?>

                <div class="level-circle locked">🔒</div>

            <?php else: ?>

                <a href="question.php?level=<?= $level_id ?>&category=<?= $category_id ?>">
                    <div class="level-circle <?= $completed ? 'completed' : '' ?>">
                        <?= $level['level_order'] ?>
                    </div>
                </a>

            <?php endif; ?>
        </div>

<?php } ?>

    </div>
</div>

<?php include "includes/footer.php"; ?>