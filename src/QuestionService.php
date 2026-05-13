<?php

declare(strict_types=1);

namespace Worldus;

class QuestionService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function validateLevelCategory(int $levelId, int $categoryId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM levels WHERE id = ? AND category_id = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param('ii', $levelId, $categoryId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->num_rows > 0 : false;
    }

    public function getQuestionsByLevel(int $levelId): array
    {
        $result = $this->db->query("SELECT * FROM questions WHERE level_id = $levelId ORDER BY id ASC");
        $questions = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
        }

        return $questions;
    }

    public function getAnswersByQuestion(int $questionId): array
    {
        $result = $this->db->query("SELECT * FROM answers WHERE question_id = $questionId");
        $answers = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $answers[] = $row;
            }
        }

        return $answers;
    }

    public function completeLevel(int $userId, int $levelId): bool
    {
        $sql = "INSERT INTO progress (user_id, level_id, completed, score) VALUES ($userId, $levelId, 1, 0) ON DUPLICATE KEY UPDATE completed = 1";
        $result = $this->db->query($sql);

        return $result !== false;
    }

    public function getQuestionCount(int $levelId): int
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM questions WHERE level_id = $levelId");
        if ($result) {
            $row = $result->fetch_assoc();
            return (int)($row['total'] ?? 0);
        }

        return 0;
    }
}
