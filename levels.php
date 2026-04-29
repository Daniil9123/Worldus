<?php

include "config/db.php";
include "includes/header.php";

$category_id = isset($_GET['category'])
? (int)$_GET['category']
: 1;

?>

<div class="levels-page">

<h1 class="levels-title">
<?= "Уровни" ?>
</h1>

<div class="levels-map">

<?php

$result = $conn->query("
SELECT *
FROM levels
WHERE category_id = $category_id
ORDER BY level_order ASC
");

while($level = $result->fetch_assoc()) {

$locked = false;

/*
ПРОВЕРКА:
открыт ли уровень
*/

if ($level['required_level'] != NULL) {

$check = $conn->query("
SELECT progress.*
FROM progress

INNER JOIN levels
ON progress.level_id = levels.id

WHERE progress.level_id = {$level['required_level']}
AND levels.category_id = $category_id
AND progress.completed = 1
");

if ($check->num_rows == 0) {
$locked = true;
}

}

/*
ПРОЙДЕН ЛИ
*/

$completed = false;

$done = $conn->query("
SELECT progress.*
FROM progress

INNER JOIN levels
ON progress.level_id = levels.id

WHERE progress.level_id = {$level['id']}
AND levels.category_id = $category_id
AND progress.completed = 1
");

if ($done->num_rows > 0) {
$completed = true;
}

?>

<div class="level-item">

<?php if ($locked): ?>

    <div class="level-circle locked">
        🔒
    </div>

<?php else: ?>

    <a href="question.php?level=<?= $level['id'] ?>">
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