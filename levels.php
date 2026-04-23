<?php
include "config/db.php";
include "includes/header.php";

$category_id = $_GET['category_id'];

$result = $conn->query("
    SELECT * FROM levels 
    WHERE category_id = $category_id 
    ORDER BY level_order ASC
");
?>

<div class="container">
    <h2>Уровни</h2>

    <div class="levels">

<?php
while ($row = $result->fetch_assoc()) {

    $locked = $row['is_locked'] ? "locked" : "";

    echo "
    <a href='game.php?level_id={$row['id']}' class='level $locked'>
        {$row['level_order']}
    </a>
    ";
}
?>

    </div>
</div>

<?php include "includes/footer.php"; ?>