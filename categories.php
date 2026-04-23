<?php
include "config/db.php";
include "includes/header.php";

$result = $conn->query("SELECT * FROM categories");
?>

<div class="container">
    <div class="grid">

<?php
while ($row = $result->fetch_assoc()) {
    echo "
    <a href='levels.php?category_id={$row['id']}' class='card category'>
        <img src='{$row['image']}' alt=''>
        <h3>{$row['name']}</h3>
        <p>{$row['description']}</p>
    </a>
    ";
}
?>

    </div>
</div>

<?php include "includes/footer.php"; ?>