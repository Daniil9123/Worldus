<?php

declare(strict_types=1);

namespace Worldus;

class CategoryService
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function getCategoryById(int $categoryId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $categoryId);
        $stmt->execute();

        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function getCategoriesPage(int $page, int $perPage = 8): array
    {
        if ($page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * $perPage;

        $countResult = $this->db->query(
            "SELECT COUNT(*) AS total FROM categories"
        );

        $total = 0;
        if ($countResult) {
            $row = $countResult->fetch_assoc();
            $total = (int)($row['total'] ?? 0);
        }

        $totalPages = (int)ceil($total / $perPage);

        $result = $this->db->query(
            "SELECT categories.*, COUNT(levels.id) AS levels_count FROM categories LEFT JOIN levels ON levels.category_id = categories.id GROUP BY categories.id ORDER BY categories.id ASC LIMIT $perPage OFFSET $offset"
        );

        $categories = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        return [
            'categories' => $categories,
            'page' => $page,
            'total' => $total,
            'total_pages' => $totalPages,
        ];
    }

    public function addCategory(string $name, string $imagePath, int $levelsCount): int
    {
        $stmt = $this->db->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param('ss', $name, $imagePath);
        $stmt->execute();

        $categoryId = $this->db->getInsertId();
        $previousLevelId = null;

        for ($i = 1; $i <= $levelsCount; $i++) {
            $title = 'Уровень ' . $i;

            if ($previousLevelId === null) {
                $stmt = $this->db->prepare(
                    "INSERT INTO levels (category_id, title, level_order, required_level) VALUES (?, ?, ?, NULL)"
                );
                $stmt->bind_param('isi', $categoryId, $title, $i);
            } else {
                $stmt = $this->db->prepare(
                    "INSERT INTO levels (category_id, title, level_order, required_level) VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param('isii', $categoryId, $title, $i, $previousLevelId);
            }

            $stmt->execute();
            $previousLevelId = $this->db->getInsertId();
        }

        return $categoryId;
    }

    public function deleteCategoryCascade(int $categoryId): bool
    {
        $this->db->query(
            "DELETE answers FROM answers INNER JOIN questions ON answers.question_id = questions.id INNER JOIN levels ON questions.level_id = levels.id WHERE levels.category_id = $categoryId"
        );

        $this->db->query(
            "DELETE questions FROM questions INNER JOIN levels ON questions.level_id = levels.id WHERE levels.category_id = $categoryId"
        );

        $this->db->query(
            "DELETE progress FROM progress INNER JOIN levels ON progress.level_id = levels.id WHERE levels.category_id = $categoryId"
        );

        $this->db->query("DELETE FROM levels WHERE category_id = $categoryId");
        $this->db->query("DELETE FROM categories WHERE id = $categoryId");

        return true;
    }
}
