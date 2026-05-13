<?php

require_once __DIR__ . '/vendor/autoload.php';
include "config/db.php";
include "includes/header.php";

use Worldus\AuthService;
use Worldus\CategoryService;
use Worldus\MysqliDatabase;

$db = new MysqliDatabase($conn);
$categoryService = new CategoryService($db);

$is_admin = false;
$user_id = null;

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $is_admin = AuthService::getUserRole($db, $user_id) === 'admin';
}

if ($is_admin && isset($_POST['delete_category'])) {
    $id = (int)$_POST['id'];
    $categoryService->deleteCategoryCascade($id);
    header("Location: categories.php");
    exit();
}

if ($is_admin && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $levels_count = (int)$_POST['levels_count'];
    $image_path = "assets/images/EstoniaHG.png";

    if (!empty($_FILES['image']['name'])) {
        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target = "assets/images/" . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        }
    }

    $category_id = $categoryService->addCategory($name, $image_path, $levels_count);
    header("Location: levels.php?category=" . $category_id);
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageData = $categoryService->getCategoriesPage($page);
$categories = $pageData['categories'];
$total_pages = $pageData['total_pages'];
?>

<div class="container">

    <h1 class="categories-title">Категории</h1>

    <p class="categories-description">
        Выберите категорию и начните обучение. Каждая категория содержит уровни с вопросами.
    </p>

    <div class="grid">

        <?php foreach ($categories as $row): ?>

            <?php
            $image = !empty($row['image'])
                ? $row['image']
                : "assets/images/EstoniaHG.png";
            ?>

            <div class="category-wrapper">

                <a href="levels.php?category=<?= $row['id'] ?>" class="card category">
                    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= $row['levels_count'] ?> уровней</p>
                </a>

                <?php if ($is_admin): ?>

                    <div class="admin-category-actions">

                        <a href="edit_categories.php?id=<?= $row['id'] ?>" class="admin-small-btn edit">
                            Редактировать
                        </a>

                        <button 
                            type="button"
                            class="admin-small-btn delete"
                            onclick="openDeleteModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>')"
                        >
                            Удалить
                        </button>

                    </div>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

    <?php if ($is_admin): ?>

        <form method="POST" enctype="multipart/form-data" class="admin-add-category">
            <input type="text" name="name" placeholder="Название категории" required>

            <input type="number" name="levels_count" placeholder="Количество уровней" min="1" required>

            <input type="file" name="image" accept="image/*">

            <button type="submit" name="add_category">
                Добавить
            </button>
        </form>

    <?php endif; ?>

    <?php if ($total_pages > 1): ?>

        <div class="category-pagination">

            <?php if ($page > 1): ?>
                <a class="arrow-btn" href="categories.php?page=<?= $page - 1 ?>">←</a>
            <?php else: ?>
                <span class="arrow-btn disabled">←</span>
            <?php endif; ?>

            <form method="GET" class="page-select-form">
                <select name="page" onchange="this.form.submit()" class="page-select">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $page ? 'selected' : '' ?>>
                            <?= $i ?> / <?= $total_pages ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </form>

            <?php if ($page < $total_pages): ?>
                <a class="arrow-btn" href="categories.php?page=<?= $page + 1 ?>">→</a>
            <?php else: ?>
                <span class="arrow-btn disabled">→</span>
            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>

<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-box">
        <h2>Вы уверены, что хотите удалить:</h2>
        <p id="deleteCategoryName"></p>

        <div class="delete-modal-buttons">
            <button type="button" onclick="closeDeleteModal()" class="keep-btn">
                Оставить
            </button>

            <form method="POST">
                <input type="hidden" name="id" id="deleteCategoryId">

                <button type="submit" name="delete_category" class="confirm-delete-btn">
                    Удалить
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(id, name) {
    document.getElementById("deleteCategoryId").value = id;
    document.getElementById("deleteCategoryName").innerText = name;
    document.getElementById("deleteModal").style.display = "flex";
}

function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
}
</script>

<?php include "includes/footer.php"; ?>