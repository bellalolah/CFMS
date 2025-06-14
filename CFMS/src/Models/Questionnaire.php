<?php

namespace Cfms\Models;

use Cfms\Interface\Models;

class Questionnaire implements Models
{
    public ?int $id = null;
    public string $title;
    public int $course_offering_id;
    public string $status = 'draft'; // draft | active | closed
    public int $feedback_round = 1;
    
    public static function toModel(array $data): Questionnaire
    {
        $q = new Questionnaire();
        $q->id = (int) $data['id'];
        $q->title = $data['title'];
        $q->course_offering_id = (int) $data['course_offering_id'];
        $q->status = $data['status'];
        $q->feedback_round = (int) $data['feedback_round'];
        

        return $q;
    }

    public function getModel(): array
    {
        return [
            'title' => $this->title,
            'course_offering_id' => $this->course_offering_id,
            'status' => $this->status,
            'feedback_round' => $this->feedback_round,
        ];
    }
}
