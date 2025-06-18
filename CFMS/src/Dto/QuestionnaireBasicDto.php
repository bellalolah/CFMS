<?php
namespace Cfms\Dto;

use Cfms\Models\Questionnaire;

class QuestionnaireBasicDto
{
    public int $id;
    public string $title;
    public string $status;
    public ?array $course_offering; // Changed from int to ?array

    public int $feedback_count;
    public int $feedback_round; // Default round
    public ?int $created_by_user_id;
    public string $created_at;

    // The constructor now accepts the detailed course offering object
    public function __construct(Questionnaire $questionnaire, ?object $courseOfferingDetails = null)
    {
        $this->id = $questionnaire->id;
        $this->title = $questionnaire->title;
        $this->status = $questionnaire->status;
        $this->created_by_user_id = $questionnaire->created_by_user_id;
        $this->created_at = $questionnaire->created_at;
        $this->feedback_round = $questionnaire->feedback_round;
        $this->feedback_count = $questionnaire->feedback_count;


        if ($courseOfferingDetails) {
            $this->course_offering = [
                'id' => $courseOfferingDetails->id,
                'course_code' => $courseOfferingDetails->course_code,
                'course_title' => $courseOfferingDetails->course_title,
                'feedback_round'=>$questionnaire->feedback_round,
                'feedback_count' => $questionnaire->feedback_count,
            ];
        } else {
            $this->course_offering = null;
        }
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}