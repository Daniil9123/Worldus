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

$level_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$level_result = $conn->query("
    SELECT *
    FROM levels
    WHERE id = $level_id
    LIMIT 1
");

if ($level_result->num_rows == 0) {
    echo "<h1 class='categories-title'>Уровень не найден</h1>";
    include "includes/footer.php";
    exit();
}

$level = $level_result->fetch_assoc();
$category_id = (int)$level['category_id'];

if (isset($_POST['save_level'])) {
    $title = trim($_POST['title']);
    $level_order = (int)$_POST['level_order'];

    $stmt = $conn->prepare("
        UPDATE levels
        SET title = ?, level_order = ?
        WHERE id = ?
    ");

    $stmt->bind_param("sii", $title, $level_order, $level_id);
    $stmt->execute();

    $old_questions = $conn->query("
        SELECT id
        FROM questions
        WHERE level_id = $level_id
    ");

    while ($old_q = $old_questions->fetch_assoc()) {
        $old_q_id = (int)$old_q['id'];

        $conn->query("
            DELETE FROM answers
            WHERE question_id = $old_q_id
        ");
    }

    $conn->query("
        DELETE FROM questions
        WHERE level_id = $level_id
    ");

    foreach ($_POST['question_text'] as $index => $question_text) {
        $question_text = trim($question_text);

        if ($question_text === '') {
            continue;
        }

        $type = $_POST['question_type'][$index];
        $answer_count = isset($_POST['answer_count'][$index])
            ? (int)$_POST['answer_count'][$index]
            : 4;

        if ($answer_count < 1) {
            $answer_count = 1;
        }

        if ($answer_count > 4) {
            $answer_count = 4;
        }

        $stmt = $conn->prepare("
            INSERT INTO questions (level_id, question_text, type)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iss", $level_id, $question_text, $type);
        $stmt->execute();

        $question_id = $conn->insert_id;

        if ($type === 'input') {
            $input_answer = trim($_POST['input_answer'][$index] ?? '');

            $stmt = $conn->prepare("
                INSERT INTO answers (question_id, answer_text, is_correct)
                VALUES (?, ?, 1)
            ");

            $stmt->bind_param("is", $question_id, $input_answer);
            $stmt->execute();
        }

        if ($type === 'choice') {
            for ($i = 0; $i < $answer_count; $i++) {
                $answer_text = trim($_POST['answer_text'][$index][$i] ?? '');
                $is_correct = isset($_POST['correct_answer'][$index]) && (int)$_POST['correct_answer'][$index] === $i ? 1 : 0;

                $stmt = $conn->prepare("
                    INSERT INTO answers (question_id, answer_text, is_correct)
                    VALUES (?, ?, ?)
                ");

                $stmt->bind_param("isi", $question_id, $answer_text, $is_correct);
                $stmt->execute();
            }
        }

        if ($type === 'image') {
            for ($i = 0; $i < $answer_count; $i++) {
                $is_correct = isset($_POST['correct_answer'][$index]) && (int)$_POST['correct_answer'][$index] === $i ? 1 : 0;

                $image_path = $_POST['old_answer_image'][$index][$i] ?? null;

                if (!empty($_FILES['answer_image']['name'][$index][$i])) {
                    $file_name = time() . "_" . basename($_FILES['answer_image']['name'][$index][$i]);
                    $target = "assets/images/" . $file_name;

                    if (move_uploaded_file($_FILES['answer_image']['tmp_name'][$index][$i], $target)) {
                        $image_path = $target;
                    }
                }

                $stmt = $conn->prepare("
                    INSERT INTO answers (question_id, answer_text, image, is_correct)
                    VALUES (?, NULL, ?, ?)
                ");

                $stmt->bind_param("isi", $question_id, $image_path, $is_correct);
                $stmt->execute();
            }
        }
    }

    header("Location: levels.php?category=" . $category_id);
    exit();
}

$questions_result = $conn->query("
    SELECT *
    FROM questions
    WHERE level_id = $level_id
    ORDER BY id ASC
");

$questions = [];

while ($q = $questions_result->fetch_assoc()) {
    $qid = (int)$q['id'];

    $answers_result = $conn->query("
        SELECT *
        FROM answers
        WHERE question_id = $qid
        ORDER BY id ASC
    ");

    $q['answers'] = [];

    while ($a = $answers_result->fetch_assoc()) {
        $q['answers'][] = $a;
    }

    $questions[] = $q;
}

if (count($questions) == 0) {
    $questions[] = [
        'question_text' => '',
        'type' => 'choice',
        'answers' => []
    ];
}
?>

<div class="admin-page">

    <div class="admin-add-box">

        <h2>Редактировать уровень</h2>

        <form method="POST" enctype="multipart/form-data">

            <label>Название уровня</label>
            <input type="text" name="title" value="<?= htmlspecialchars($level['title']) ?>" required>

            <label>Порядок уровня</label>
            <input type="number" name="level_order" value="<?= (int)$level['level_order'] ?>" required>

            <h2>Вопросы и ответы</h2>

            <div id="questionsBox">

                <?php foreach ($questions as $index => $question): ?>

                    <?php
                    $answer_count = count($question['answers']);
                    if ($answer_count < 1) {
                        $answer_count = 4;
                    }
                    if ($answer_count > 4) {
                        $answer_count = 4;
                    }
                    ?>

                    <div class="question-edit-block">

                        <h3>Вопрос <?= $index + 1 ?></h3>

                        <input
                            type="text"
                            name="question_text[]"
                            value="<?= htmlspecialchars($question['question_text']) ?>"
                            placeholder="Текст вопроса"
                        >

                        <select name="question_type[]" class="admin-select question-type-select" onchange="toggleQuestionType(this)">
                            <option value="choice" <?= $question['type'] == 'choice' ? 'selected' : '' ?>>choice</option>
                            <option value="image" <?= $question['type'] == 'image' ? 'selected' : '' ?>>image</option>
                            <option value="input" <?= $question['type'] == 'input' ? 'selected' : '' ?>>input</option>
                        </select>

                        <div class="input-answer-section">
                            <input
                                type="text"
                                name="input_answer[]"
                                placeholder="Правильный ответ для input"
                                value="<?= htmlspecialchars($question['answers'][0]['answer_text'] ?? '') ?>"
                            >
                        </div>

                        <div class="variants-section">

                            <label>Количество вариантов ответа</label>

                            <select name="answer_count[]" class="admin-select answer-count-select" onchange="toggleAnswerCount(this)">
                                <option value="1" <?= $answer_count == 1 ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= $answer_count == 2 ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= $answer_count == 3 ? 'selected' : '' ?>>3</option>
                                <option value="4" <?= $answer_count == 4 ? 'selected' : '' ?>>4</option>
                            </select>

                            <?php for ($i = 0; $i < 4; $i++): ?>

                                <div class="answer-edit-row" data-answer-index="<?= $i ?>">

                                    <div class="choice-answer-field">
                                        <input
                                            type="text"
                                            name="answer_text[<?= $index ?>][]"
                                            placeholder="Ответ <?= $i + 1 ?>"
                                            value="<?= htmlspecialchars($question['answers'][$i]['answer_text'] ?? '') ?>"
                                        >
                                    </div>

                                    <div class="image-answer-field">
                                        <?php if (!empty($question['answers'][$i]['image'])): ?>
                                            <img
                                                src="<?= htmlspecialchars($question['answers'][$i]['image']) ?>"
                                                style="width:90px;height:55px;object-fit:cover;margin-bottom:6px;"
                                                alt=""
                                            >
                                        <?php endif; ?>

                                        <input
                                            type="hidden"
                                            name="old_answer_image[<?= $index ?>][]"
                                            value="<?= htmlspecialchars($question['answers'][$i]['image'] ?? '') ?>"
                                        >

                                        <input type="file" name="answer_image[<?= $index ?>][]" accept="image/*">
                                    </div>

                                    <label>
                                        <input
                                            type="radio"
                                            name="correct_answer[<?= $index ?>]"
                                            value="<?= $i ?>"
                                            <?= !empty($question['answers'][$i]['is_correct']) ? 'checked' : '' ?>
                                        >
                                        правильный
                                    </label>

                                </div>

                            <?php endfor; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

            <button type="button" class="admin-edit-btn" onclick="addQuestion()">
                Добавить вопрос
            </button>

            <button type="submit" name="save_level" class="admin-add-btn">
                Сохранить уровень
            </button>

        </form>

        <a href="levels.php?category=<?= $category_id ?>" class="btn blue">Назад</a>

    </div>

</div>

<script>
let questionIndex = <?= count($questions) ?>;

function toggleQuestionType(select) {
    const block = select.closest(".question-edit-block");
    const type = select.value;

    const inputSection = block.querySelector(".input-answer-section");
    const variantsSection = block.querySelector(".variants-section");
    const choiceFields = block.querySelectorAll(".choice-answer-field");
    const imageFields = block.querySelectorAll(".image-answer-field");

    if (type === "input") {
        inputSection.style.display = "block";
        variantsSection.style.display = "none";
    }

    if (type === "choice") {
        inputSection.style.display = "none";
        variantsSection.style.display = "block";

        choiceFields.forEach(field => field.style.display = "block");
        imageFields.forEach(field => field.style.display = "none");
    }

    if (type === "image") {
        inputSection.style.display = "none";
        variantsSection.style.display = "block";

        choiceFields.forEach(field => field.style.display = "none");
        imageFields.forEach(field => field.style.display = "block");
    }

    toggleAnswerCount(block.querySelector(".answer-count-select"));
}

function toggleAnswerCount(select) {
    const block = select.closest(".question-edit-block");
    const count = parseInt(select.value);

    const rows = block.querySelectorAll(".answer-edit-row");

    rows.forEach(row => {
        const index = parseInt(row.dataset.answerIndex);

        if (index < count) {
            row.style.display = "grid";
        } else {
            row.style.display = "none";
        }
    });
}

function addQuestion() {
    const box = document.getElementById("questionsBox");

    const html = `
        <div class="question-edit-block">

            <h3>Новый вопрос</h3>

            <input type="text" name="question_text[]" placeholder="Текст вопроса">

            <select name="question_type[]" class="admin-select question-type-select" onchange="toggleQuestionType(this)">
                <option value="choice">choice</option>
                <option value="image">image</option>
                <option value="input">input</option>
            </select>

            <div class="input-answer-section">
                <input type="text" name="input_answer[]" placeholder="Правильный ответ для input">
            </div>

            <div class="variants-section">

                <label>Количество вариантов ответа</label>

                <select name="answer_count[]" class="admin-select answer-count-select" onchange="toggleAnswerCount(this)">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4" selected>4</option>
                </select>

                ${[0,1,2,3].map(i => `
                    <div class="answer-edit-row" data-answer-index="${i}">

                        <div class="choice-answer-field">
                            <input type="text" name="answer_text[${questionIndex}][]" placeholder="Ответ ${i + 1}">
                        </div>

                        <div class="image-answer-field">
                            <input type="hidden" name="old_answer_image[${questionIndex}][]" value="">
                            <input type="file" name="answer_image[${questionIndex}][]" accept="image/*">
                        </div>

                        <label>
                            <input type="radio" name="correct_answer[${questionIndex}]" value="${i}" ${i === 0 ? 'checked' : ''}>
                            правильный
                        </label>

                    </div>
                `).join('')}

            </div>

        </div>
    `;

    box.insertAdjacentHTML("beforeend", html);

    const newBlock = box.lastElementChild;
    toggleQuestionType(newBlock.querySelector(".question-type-select"));

    questionIndex++;
}

document.querySelectorAll(".question-type-select").forEach(select => {
    toggleQuestionType(select);
});
</script>

<?php include "includes/footer.php"; ?>