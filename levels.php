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

$page = isset($_GET['page'])
    ? (int)$_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}

$levels_per_page = 5;
$offset = ($page - 1) * $levels_per_page;

$count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM levels
    WHERE category_id = $category_id
");

$total_levels = (int)$count_result->fetch_assoc()['total'];
$total_pages = ceil($total_levels / $levels_per_page);

$completed_result = $conn->query("
    SELECT COUNT(*) AS completed_count
    FROM progress
    INNER JOIN levels ON progress.level_id = levels.id
    WHERE progress.user_id = $user_id
    AND levels.category_id = $category_id
    AND progress.completed = 1
");

$completed_levels = (int)$completed_result->fetch_assoc()['completed_count'];

$progress_percent = $total_levels > 0
    ? ($completed_levels / $total_levels) * 100
    : 0;

$result = $conn->query("
    SELECT *
    FROM levels
    WHERE category_id = $category_id
    ORDER BY level_order ASC
    LIMIT $levels_per_page OFFSET $offset
");

?>

<div class="levels-page">

<h1 class="levels-title">Уровни</h1>
<div class="levels-progress">
    <p>Прогресс <?= $completed_levels ?> / <?= $total_levels ?></p>

    <div class="levels-progress-bar">
        <div class="levels-progress-fill" style="width: <?= $progress_percent ?>%"></div>
    </div>
</div>

<div class="levels-map">

<?php while ($level = $result->fetch_assoc()) {

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

<?php if ($total_pages > 1): ?>

<div class="level-pagination">

    <?php if ($page > 1): ?>
        <a class="arrow-btn" href="levels.php?category=<?= $category_id ?>&page=<?= $page - 1 ?>">
            ←
        </a>
    <?php else: ?>
        <span class="arrow-btn disabled">←</span>
    <?php endif; ?>

    <form method="GET" class="page-select-form">
        <input type="hidden" name="category" value="<?= $category_id ?>">

        <select name="page" onchange="this.form.submit()" class="page-select">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <option value="<?= $i ?>" <?= $i == $page ? 'selected' : '' ?>>
                    <?= $i ?> / <?= $total_pages ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>

    <?php if ($page < $total_pages): ?>
        <a class="arrow-btn" href="levels.php?category=<?= $category_id ?>&page=<?= $page + 1 ?>">
            →
        </a>
    <?php else: ?>
        <span class="arrow-btn disabled">→</span>
    <?php endif; ?>

</div>

<?php endif; ?>

</div>

<?php include "includes/footer.php"; ?>