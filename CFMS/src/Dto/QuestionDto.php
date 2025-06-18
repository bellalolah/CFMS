<?php
namespace Cfms\Dto;

use Cfms\Models\Criterion;
use Cfms\Models\Question;

class QuestionDto
{
    public int $id;
    public string $question_text;
    public string $question_type;
    public int $order;

    // This will hold the Criterion details
    public array $criterion;

    public function __construct(Question $question, Criterion $criterion)
    {
        $this->id = $question->id;
        $this->question_text = $question->question_text;
        $this->question_type = $question->question_type;
        $this->order = $question->order;

        // We'll store the criterion info as a simple array
        $this->criterion = [
            'id' => $criterion->id,
            'name' => $criterion->name
        ];
    }

    /**
     * Converts the DTO into a plain array for the final JSON response.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'order' => $this->order,
            'criterion' => $this->criterion,
        ];
    }
}