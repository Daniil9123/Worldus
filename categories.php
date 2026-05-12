<?php
include "config/db.php";
include "includes/header.php";

$is_admin = false;

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];

    $user_result = $conn->query("
        SELECT role 
        FROM users 
        WHERE id = $user_id 
        LIMIT 1
    ");

    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $is_admin = ($user['role'] === 'admin');
    }
}

/*
DELETE CATEGORY
*/

if ($is_admin && isset($_POST['delete_category'])) {
    $id = (int)$_POST['id'];

    // Удаляем ответы вопросов этой категории
    $conn->query("
        DELETE answers
        FROM answers
        INNER JOIN questions ON answers.question_id = questions.id
        INNER JOIN levels ON questions.level_id = levels.id
        WHERE levels.category_id = $id
    ");

    // Удаляем вопросы этой категории
    $conn->query("
        DELETE questions
        FROM questions
        INNER JOIN levels ON questions.level_id = levels.id
        WHERE levels.category_id = $id
    ");

    // Удаляем прогресс по уровням этой категории
    $conn->query("
        DELETE progress
        FROM progress
        INNER JOIN levels ON progress.level_id = levels.id
        WHERE levels.category_id = $id
    ");

    // Удаляем уровни категории
    $conn->query("
        DELETE FROM levels
        WHERE category_id = $id
    ");

    // Удаляем категорию
    $conn->query("
        DELETE FROM categories
        WHERE id = $id
    ");

    header("Location: categories.php");
    exit();
}

/*
ADD CATEGORY
*/

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

    $stmt = $conn->prepare("
        INSERT INTO categories (name, image)
        VALUES (?, ?)
    ");

    $stmt->bind_param("ss", $name, $image_path);
    $stmt->execute();

    $category_id = $conn->insert_id;

    $previous_level_id = null;

    for ($i = 1; $i <= $levels_count; $i++) {
        $title = "Уровень " . $i;

        if ($previous_level_id === null) {
            $stmt = $conn->prepare("
                INSERT INTO levels (category_id, title, level_order, required_level)
                VALUES (?, ?, ?, NULL)
            ");

            $stmt->bind_param("isi", $category_id, $title, $i);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO levels (category_id, title, level_order, required_level)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param("isii", $category_id, $title, $i, $previous_level_id);
        }

        $stmt->execute();
        $previous_level_id = $conn->insert_id;
    }

    header("Location: levels.php?category=" . $category_id);
    exit();
}

/*
PAGINATION
*/

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$categories_per_page = 8;
$offset = ($page - 1) * $categories_per_page;

$count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM categories
");

$total_categories = (int)$count_result->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $categories_per_page);

$result = $conn->query("
    SELECT 
        categories.*,
        COUNT(levels.id) AS levels_count
    FROM categories
    LEFT JOIN levels ON levels.category_id = categories.id
    GROUP BY categories.id
    ORDER BY categories.id ASC
    LIMIT $categories_per_page OFFSET $offset
");
?>

<div class="container">

    <h1 class="categories-title">Категории</h1>

    <p class="categories-description">
        Выберите категорию и начните обучение. Каждая категория содержит уровни с вопросами.
    </p>

    <div class="grid">

        <?php while ($row = $result->fetch_assoc()): ?>

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

        <?php endwhile; ?>

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