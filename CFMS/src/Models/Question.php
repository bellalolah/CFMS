<?php
namespace Cfms\Models;

class Question implements Models
{
    public $id;
    public $questionnaire_id;
    public $question_text;
    public $question_type;


    public function toModel(object $row): self
    {
        $this->id = $row->id ?? null;
        $this->questionnaire_id = $row->questionnaire_id ?? null;
        $this->question_text = $row->question_text ?? '';
        $this->question_type = $row->question_type ?? 'rating';


        return $this;
    }

    public function getModel(): array
    {
        return [
            'questionnaire_id' => $this->questionnaire_id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type
        ];
    }
}
