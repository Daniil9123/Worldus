<?php
include "config/db.php";

$level_id = $_GET['level_id'];

$q = $conn->query("SELECT * FROM questions WHERE level_id=$level_id LIMIT 1");
$question = $q->fetch_assoc();

$answers = $conn->query("SELECT * FROM answers WHERE question_id=".$question['id']);

echo "<h2>".$question['question_text']."</h2>";

while ($a = $answers->fetch_assoc()) {
    echo "<button>".$a['answer_text']."</button>";
}
?>