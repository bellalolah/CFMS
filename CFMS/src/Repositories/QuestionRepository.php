<?php
namespace Cfms\Repositories;

use Cfms\Models\Question;

class QuestionRepository extends BaseRepository
{
    protected $table = 'questions';

    public function getAllQuestionsByQuestionnaire(int $questionnaireId): array
    {
        $results = $this->findByColumn($this->table, 'questionnaire_id', $questionnaireId);
        $questions = [];

        foreach ($results as $row) {
            $questions[] = (new Question())->toModel((object) $row);
        }

        return $questions;
    }

    public function getQuestionById(int $id): ?Question
    {
        $result = $this->findById($this->table, $id);
        return $result ? (new Question())->toModel((object) $result) : null;
    }

    public function createQuestion(Question $question): ?Question
    {
        $data = $question->getModel();
        $question->id = $this->insert($this->table, $data);

        return $question->id ? $question : null;
    }

    public function updateQuestion(int $id, array $data): bool
    {
        return $this->update($this->table, $id, $data);
    }

    public function deleteQuestion(int $id): bool
    {
        return $this->deleteById($this->table, $id);
    }
}
