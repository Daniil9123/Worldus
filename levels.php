<?php

require_once __DIR__ . '/vendor/autoload.php';
include "config/db.php";
include "includes/header.php";

use Worldus\AuthService;
use Worldus\LevelService;
use Worldus\MysqliDatabase;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 1;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = new MysqliDatabase($conn);
$levelService = new LevelService($db);
$is_admin = AuthService::getUserRole($db, $user_id) === 'admin';

$pageData = $levelService->getLevelsPage($category_id, $page, $user_id);
$levels = $pageData['levels'];
$total_levels = $pageData['total'];
$total_pages = $pageData['total_pages'];
$completed_levels = $pageData['completed'];
$progress_percent = $pageData['progress_percent'];
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

<?php foreach ($levels as $level): ?>

    <?php
    $level_id = (int)$level['id'];
    $locked = !empty($level['locked']) && $level['locked'];
    $completed = !empty($level['completed']) && $level['completed'];
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

    <?php if ($is_admin): ?>
        <div class="admin-level-actions">
            <a href="edit_levels.php?id=<?= $level_id ?>" class="admin-small-btn edit">
                Редактировать
            </a>
        </div>
    <?php endif; ?>

</div>

<?php endforeach; ?>

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