<?php

require_once __DIR__ . '/vendor/autoload.php';
include "config/db.php";
include "includes/header.php";

use Worldus\AuthService;
use Worldus\MysqliDatabase;
use Worldus\QuestionService;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$db = new MysqliDatabase($conn);
$questionService = new QuestionService($db);

$is_admin = AuthService::getUserRole($db, $user_id) === 'admin';

$level_id = isset($_GET['level']) ? (int)$_GET['level'] : 0;
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 1;

if (isset($_GET['failed'])) {
    ?>

    <div class="question-page">
        <div class="finish-box">
            <h1>Ответ неправильный</h1>

            <p>Уровень не засчитан. Попробуйте пройти его ещё раз.</p>

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

if (!$questionService->validateLevelCategory($level_id, $category_id)) {
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

$q = isset($_GET['q']) ? (int)$_GET['q'] : 0;
$questions_array = $questionService->getQuestionsByLevel($level_id);
$total_questions = count($questions_array);

if ($total_questions == 0) {
    echo "<div class='question-page'><div class='finish-box'><h1>Вопросов нет</h1></div></div>";
    include "includes/footer.php";
    exit();
}

if ($q >= $total_questions) {
    $questionService->completeLevel($user_id, $level_id);
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

$current_question = $questions_array[$q];
$answers = $questionService->getAnswersByQuestion($current_question['id']);

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
            <?= htmlspecialchars($current_question['question_text']) ?>
        </h1>

        <?php if ($is_admin): ?>
            <a href="edit_question.php?id=<?= $current_question['id'] ?>" class="admin-edit-question-btn">
                Редактировать вопрос
            </a>
        <?php endif; ?>

        <!-- IMAGE -->

        <?php if (!empty($current_question['image'])) { ?>

            <img
                src="<?= $current_question['image'] ?>"
                class="question-image"
                alt="question image"
            >

        <?php } ?>

        <!-- ANSWERS -->

        <?php if ($current_question['type'] == 'input') { ?>

            <div class="input-answer-box">

                <?php
                $correct_answer = $answers[0] ?? null;
                if (!$correct_answer) {
                    echo "<p>Ошибка: правильный ответ не найден.</p>";
                    include "includes/footer.php";
                    exit();
                }
                ?>

                <input
                    type="text"
                    id="textAnswer"
                    class="text-answer"
                    placeholder="Введите ответ"
                    data-correct="<?= htmlspecialchars(mb_strtolower(trim($correct_answer['answer_text']))) ?>"
                >

                <button
                    type="button"
                    class="next-btn"
                    id="checkInputBtn"
                >
                    Проверить
                </button>

                <p id="inputResult" class="input-result"></p>

            </div>

        <?php } elseif ($current_question['type'] == 'image') { ?>

            <div class="image-answers">

                <?php foreach ($answers as $answer) { ?>

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

                <?php foreach ($answers as $answer) { ?>

                    <button
                        class="answer-btn"
                        data-correct="<?= $answer['is_correct'] ?>"
                        type="button"
                    >

                        <?= htmlspecialchars($answer['answer_text']) ?>

                    </button>

                <?php } ?>

            </div>

        <?php } ?>

        <!-- NEXT -->

        <a id="nextLink" href="question.php?level=<?= $level_id ?>&category=<?= $category_id ?>&q=<?= $q + 1 ?>">

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
const nextLink = document.getElementById("nextLink");

buttons.forEach(button => {
    button.addEventListener("click", () => {

        buttons.forEach(btn => {
            btn.disabled = true;
        });

        if (button.dataset.correct == "1") {
            button.classList.add("correct");
            nextLink.href = "question.php?level=<?= $level_id ?>&category=<?= $category_id ?>&q=<?= $q + 1 ?>";
        } else {
            button.classList.add("wrong");

            buttons.forEach(btn => {
                if (btn.dataset.correct == "1") {
                    btn.classList.add("correct");
                }
            });

            nextButton.textContent = "Вернуться к уровням";
            nextLink.href = "question.php?level=<?= $level_id ?>&category=<?= $category_id ?>&failed=1";
        }

        nextButton.style.display = "inline-block";
    });
});

const checkInputBtn = document.getElementById("checkInputBtn");
const textAnswer = document.getElementById("textAnswer");
const inputResult = document.getElementById("inputResult");

if (checkInputBtn) {
    checkInputBtn.addEventListener("click", () => {
        const userAnswer = textAnswer.value.trim().toLowerCase();
        const correctAnswer = textAnswer.dataset.correct.trim().toLowerCase();

        if (userAnswer === correctAnswer) {
            inputResult.textContent = "Правильно!";
            inputResult.className = "input-result correct-text";
            nextLink.href = "question.php?level=<?= $level_id ?>&category=<?= $category_id ?>&q=<?= $q + 1 ?>";
        } else {
            inputResult.textContent = "Неправильно! Правильный ответ: " + correctAnswer;
            inputResult.className = "input-result wrong-text";

            nextButton.textContent = "Вернуться к уровням";
            nextLink.href = "question.php?level=<?= $level_id ?>&category=<?= $category_id ?>&failed=1";
        }

        textAnswer.disabled = true;
        checkInputBtn.style.display = "none";
        nextButton.style.display = "inline-block";
    });
}
</script>

<?php include "includes/footer.php"; ?>