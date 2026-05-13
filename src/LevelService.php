<?php

declare(strict_types=1);

namespace Worldus;

class LevelService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function getLevelById(int $levelId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM levels WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $levelId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function saveLevel(int $levelId, string $title, int $levelOrder, array $questions): bool
    {
        $stmt = $this->db->prepare("UPDATE levels SET title = ?, level_order = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('sii', $title, $levelOrder, $levelId);
        $stmt->execute();

        $oldQuestions = $this->db->query("SELECT id FROM questions WHERE level_id = $levelId");
        if ($oldQuestions) {
            while ($oldQuestion = $oldQuestions->fetch_assoc()) {
                $oldQuestionId = (int)$oldQuestion['id'];
                $this->db->query("DELETE FROM answers WHERE question_id = $oldQuestionId");
            }
        }

        $this->db->query("DELETE FROM questions WHERE level_id = $levelId");

        foreach ($questions as $question) {
            $questionText = trim((string)($question['question_text'] ?? ''));
            if ($questionText === '') {
                continue;
            }

            $type = $question['type'] ?? 'choice';
            $answerCount = isset($question['answer_count']) ? (int)$question['answer_count'] : 4;
            $answerCount = max(1, min(4, $answerCount));

            $stmt = $this->db->prepare("INSERT INTO questions (level_id, question_text, type) VALUES (?, ?, ?)");
            if (!$stmt) {
                continue;
            }

            $stmt->bind_param('iss', $levelId, $questionText, $type);
            $stmt->execute();

            $questionId = $this->db->getInsertId();

            if ($type === 'input') {
                $inputAnswer = trim((string)($question['input_answer'] ?? ''));

                $stmt = $this->db->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, 1)");
                if (!$stmt) {
                    continue;
                }

                $stmt->bind_param('is', $questionId, $inputAnswer);
                $stmt->execute();
                continue;
            }

            if ($type === 'choice' || $type === 'image') {
                $correctIndex = isset($question['correct_answer']) ? (int)$question['correct_answer'] : 0;
                $answers = $question['answers'] ?? [];

                for ($i = 0; $i < $answerCount; $i++) {
                    $answerData = $answers[$i] ?? [];
                    $answerText = trim((string)($answerData['answer_text'] ?? ''));
                    $image = trim((string)($answerData['image'] ?? ''));
                    $isCorrect = $correctIndex === $i ? 1 : 0;

                    if ($type === 'choice') {
                        $stmt = $this->db->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            continue;
                        }

                        $stmt->bind_param('isi', $questionId, $answerText, $isCorrect);
                        $stmt->execute();
                        continue;
                    }

                    if ($type === 'image') {
                        $stmt = $this->db->prepare("INSERT INTO answers (question_id, answer_text, image, is_correct) VALUES (?, NULL, ?, ?)");
                        if (!$stmt) {
                            continue;
                        }

                        $stmt->bind_param('isi', $questionId, $image, $isCorrect);
                        $stmt->execute();
                    }
                }
            }
        }

        return true;
    }

    public function getLevelsPage(int $categoryId, int $page, int $userId, int $perPage = 5): array
    {
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        $countResult = $this->db->query("SELECT COUNT(*) AS total FROM levels WHERE category_id = $categoryId");
        $total = 0;
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $total = (int)($row['total'] ?? 0);
        }

        $totalPages = (int)ceil($total / $perPage);

        $result = $this->db->query("SELECT * FROM levels WHERE category_id = $categoryId ORDER BY level_order ASC LIMIT $perPage OFFSET $offset");
        $levels = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $row['locked'] = $this->isLevelLocked((int)$row['id'], $userId);
                $row['completed'] = $this->hasCompletedLevel($userId, (int)$row['id']);
                $levels[] = $row;
            }
        }

        $completedResult = $this->db->query(
            "SELECT COUNT(*) AS completed_count FROM progress INNER JOIN levels ON progress.level_id = levels.id WHERE progress.user_id = $userId AND levels.category_id = $categoryId AND progress.completed = 1"
        );

        $completed = 0;
        if ($completedResult) {
            $row = $completedResult->fetch_assoc();
            $completed = (int)($row['completed_count'] ?? 0);
        }

        $progressPercent = $total > 0 ? ($completed / $total) * 100 : 0;

        return [
            'levels' => $levels,
            'page' => $page,
            'total' => $total,
            'total_pages' => $totalPages,
            'completed' => $completed,
            'progress_percent' => $progressPercent,
        ];
    }

    public function isLevelLocked(int $levelId, int $userId): bool
    {
        $level = $this->getLevelById($levelId);
        if (!$level || empty($level['required_level'])) {
            return false;
        }

        $requiredLevelId = (int)$level['required_level'];
        $check = $this->db->query(
            "SELECT id FROM progress WHERE user_id = $userId AND level_id = $requiredLevelId AND completed = 1 LIMIT 1"
        );

        return $check ? $check->num_rows === 0 : true;
    }

    public function hasCompletedLevel(int $userId, int $levelId): bool
    {
        $done = $this->db->query(
            "SELECT id FROM progress WHERE user_id = $userId AND level_id = $levelId AND completed = 1 LIMIT 1"
        );

        return (bool)($done && $done->num_rows > 0);
    }
}
