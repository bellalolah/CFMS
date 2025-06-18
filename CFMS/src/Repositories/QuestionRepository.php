<?php
namespace Cfms\Repositories;


use Cfms\Models\Question;

class QuestionRepository extends BaseRepository
{
    protected string $table = 'questions';

    /**
     * Finds all questions associated with a specific questionnaire ID.
     */
    public function findByQuestionnaireId(int $questionnaireId): array
    {
        $rows = $this->findByColumn($this->table, 'questionnaire_id', $questionnaireId);

        // Sort the questions by their 'order' property
        usort($rows, fn($a, $b) => $a->order <=> $b->order);

        return array_map(fn($row) => (new Question())->toModel($row), $rows);
    }
}