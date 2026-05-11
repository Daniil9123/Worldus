<?php

include "config/db.php";
include "includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

/*
LEVEL
*/

/*
LEVEL AND CATEGORY
*/

$level_id = isset($_GET['level'])
    ? (int)$_GET['level']
    : 0;

$category_id = isset($_GET['category'])
    ? (int)$_GET['category']
    : 1;

/*
ПРОВЕРКА:
уровень принадлежит категории
*/

$level_check = $conn->query("
    SELECT *
    FROM levels
    WHERE id = $level_id
    AND category_id = $category_id
    LIMIT 1
");

if ($level_check->num_rows == 0) {

    echo "
    <div class='question-page'>

        <div class='finish-box'>

            <h1>Уровень не найден</h1>

            <a href='categories.php'>
                <button class='next-btn'>
                    Назад
                </button>
            </a>

        </div>

    </div>
    ";

    include "includes/footer.php";
    exit();
}

/*
QUESTION NUMBER
*/

$q = isset($_GET['q'])
    ? (int)$_GET['q']
    : 0;

/*
ВСЕ ВОПРОСЫ УРОВНЯ
*/

$questions = $conn->query("
    SELECT *
    FROM questions
    WHERE level_id = $level_id
    ORDER BY id ASC
");

$questions_array = [];

while ($row = $questions->fetch_assoc()) {
    $questions_array[] = $row;
}

/*
КОЛИЧЕСТВО
*/

$total_questions = count($questions_array);

/*
ЕСЛИ ВОПРОСОВ НЕТ
*/

if ($total_questions == 0) {
    echo "<div class='question-page'><div class='finish-box'><h1>Вопросов нет</h1></div></div>";
    include "includes/footer.php";
    exit();
}

/*
ЕСЛИ УРОВЕНЬ ЗАКОНЧЕН
*/

if ($q >= $total_questions) {

    $conn->query("
        INSERT INTO progress (user_id, level_id, completed, score)
        VALUES ($user_id, $level_id, 1, 0)
        ON DUPLICATE KEY UPDATE
        completed = 1
    ");

?>

<div class="question-page">

    <div class="finish-box">

        <h1>Уровень пройден!</h1>

        <a href="levels.php?category=<?= $category_id ?>">
            <button class="next-btn">
                Назад к уровням
            </button>
        </a>

    </div>

</div>

<?php

    include "includes/footer.php";
    exit();

}

/*
ТЕКУЩИЙ ВОПРОС
*/

$current_question = $questions_array[$q];

/*
ОТВЕТЫ
*/

$answers = $conn->query("
    SELECT *
    FROM answers
    WHERE question_id = {$current_question['id']}
");

?>

<div class="question-page">

    <div class="question-box">

        <!-- ПРОГРЕСС -->

        <div class="progress-text">
            Вопрос <?= $q + 1 ?> / <?= $total_questions ?>
        </div>

        <div class="progress-bar">
            <div class="progress-fill"
                 style="width: <?= (($q + 1) / $total_questions) * 100 ?>%">
            </div>
        </div>

        <!-- QUESTION -->

        <h1 class="question-title">
            <?= $current_question['question_text'] ?>
        </h1>

        <!-- IMAGE -->

        <?php if (!empty($current_question['image'])) { ?>

            <img
                src="<?= $current_question['image'] ?>"
                class="question-image"
                alt="question image"
            >

        <?php } ?>

        <!-- ANSWERS -->

        <?php if ($current_question['type'] == 'image') { ?>

            <div class="image-answers">

                <?php while ($answer = $answers->fetch_assoc()) { ?>

                    <button
                        class="image-answer answer-btn"
                        data-correct="<?= $answer['is_correct'] ?>"
                        type="button"
                    >

                        <img src="<?= $answer['image'] ?>" alt="answer image">

                    </button>

                <?php } ?>

            </div>

        <?php } else { ?>

            <div class="answers">

                <?php while ($answer = $answers->fetch_assoc()) { ?>

                    <button
                        class="answer-btn"
                        data-correct="<?= $answer['is_correct'] ?>"
                        type="button"
                    >

                        <?= $answer['answer_text'] ?>

                    </button>

                <?php } ?>

            </div>

        <?php } ?>

        <!-- NEXT -->

        <a href="question.php?level=<?= $level_id ?>&category=<?= $category_id ?>&q=<?= $q + 1 ?>">

            <button
                class="next-btn"
                id="nextButton"
                style="display:none;"
            >
                Далее
            </button>

        </a>

    </div>

</div>

<script>
const buttons = document.querySelectorAll(".answer-btn");
const nextButton = document.getElementById("nextButton");

buttons.forEach(button => {
    button.addEventListener("click", () => {

        buttons.forEach(btn => {
            btn.disabled = true;
        });

        if (button.dataset.correct == "1") {
            button.classList.add("correct");
        } else {
            button.classList.add("wrong");

            buttons.forEach(btn => {
                if (btn.dataset.correct == "1") {
                    btn.classList.add("correct");
                }
            });
        }

        nextButton.style.display = "inline-block";
    });
});
</script>

<?php include "includes/footer.php"; ?>