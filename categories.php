<?php
include "config/db.php";
include "includes/header.php";

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
                : "assets/images/categories/default.png";
            ?>

            <a href="levels.php?category=<?= $row['id'] ?>" class="card category">
                <img src="<?= $image ?>" alt="<?= $row['name'] ?>">

                <h3><?= $row['name'] ?></h3>

                <p>
                    <?= $row['levels_count'] ?> уровней
                </p>
            </a>

        <?php endwhile; ?>

    </div>

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

<?php include "includes/footer.php"; ?>