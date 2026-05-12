<?php
include "config/db.php";
include "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$user_result = $conn->query("
    SELECT role
    FROM users
    WHERE id = $user_id
    LIMIT 1
");

$user = $user_result->fetch_assoc();

if (!$user || $user['role'] !== 'admin') {
    echo "<h1 class='categories-title'>Доступ запрещён</h1>";
    include "includes/footer.php";
    exit();
}

$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$category_result = $conn->query("
    SELECT *
    FROM categories
    WHERE id = $category_id
    LIMIT 1
");

if ($category_result->num_rows == 0) {
    echo "<h1 class='categories-title'>Категория не найдена</h1>";
    include "includes/footer.php";
    exit();
}

$category = $category_result->fetch_assoc();

$count_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM levels
    WHERE category_id = $category_id
");

$current_levels_count = (int)$count_result->fetch_assoc()['total'];

if (isset($_POST['save_category'])) {
    $name = trim($_POST['name']);
    $new_levels_count = (int)$_POST['levels_count'];

    $image_path = $category['image'];

    if (!empty($_FILES['image']['name'])) {
        $file_name = time() . "_" . basename($_FILES['image']['name']);
        $target = "assets/images/" . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $image_path = $target;
        }
    }

    $stmt = $conn->prepare("
        UPDATE categories
        SET name = ?, image = ?
        WHERE id = ?
    ");

    $stmt->bind_param("ssi", $name, $image_path, $category_id);
    $stmt->execute();

    if ($new_levels_count > $current_levels_count) {
        $previous_level_id = null;

        $last_level_result = $conn->query("
            SELECT id
            FROM levels
            WHERE category_id = $category_id
            ORDER BY level_order DESC
            LIMIT 1
        ");

        if ($last_level_result->num_rows > 0) {
            $previous_level_id = (int)$last_level_result->fetch_assoc()['id'];
        }

        for ($i = $current_levels_count + 1; $i <= $new_levels_count; $i++) {
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
    }

    if ($new_levels_count < $current_levels_count) {
        $levels_to_delete = $conn->query("
            SELECT id
            FROM levels
            WHERE category_id = $category_id
            AND level_order > $new_levels_count
        ");

        while ($lvl = $levels_to_delete->fetch_assoc()) {
            $delete_level_id = (int)$lvl['id'];

            $conn->query("
                DELETE FROM answers
                WHERE question_id IN (
                    SELECT id FROM questions WHERE level_id = $delete_level_id
                )
            ");

            $conn->query("
                DELETE FROM questions
                WHERE level_id = $delete_level_id
            ");

            $conn->query("
                DELETE FROM progress
                WHERE level_id = $delete_level_id
            ");

            $conn->query("
                DELETE FROM levels
                WHERE id = $delete_level_id
            ");
        }
    }

    header("Location: categories.php");
    exit();
}
?>

<div class="admin-page">
    <div class="admin-add-box">

        <h2>Редактировать категорию</h2>

        <form method="POST" enctype="multipart/form-data">

            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars($category['name']) ?>"
                placeholder="Название категории"
                required
            >

            <input
                type="number"
                name="levels_count"
                value="<?= $current_levels_count ?>"
                min="1"
                placeholder="Количество уровней"
                required
            >

            <p>Текущая картинка:</p>

            <img
                src="<?= htmlspecialchars($category['image']) ?>"
                style="width:220px;max-height:160px;object-fit:contain;margin:15px 0;"
                alt=""
            >

            <input type="file" name="image" accept="image/*">

            <button type="submit" name="save_category" class="admin-add-btn">
                Сохранить
            </button>

        </form>

        <a href="categories.php" class="btn blue">Назад</a>

    </div>
</div>

<?php include "includes/footer.php"; ?>