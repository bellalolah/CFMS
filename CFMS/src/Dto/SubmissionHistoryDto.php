<?php
namespace Cfms\Dto;

class SubmissionHistoryDto
{
    public int $submission_id;
    public string $submitted_at;
    public array $questionnaire;

    public function __construct(object $data)
    {
        $this->submission_id = (int)$data->submission_id;
        $this->submitted_at = $data->submitted_at;

        // Build the nested questionnaire object
        $this->questionnaire = [
            'id' => (int)$data->questionnaire_id,
            'title' => $data->questionnaire_title,
            'course' => $data->course_code ? [
                'course_code' => $data->course_code,
                'course_title' => $data->course_title
            ] : null, // Handle general questionnaires
            'lecturer' => $data->lecturer_name ? [
                'full_name' => $data->lecturer_name
            ] : null,
        ];
    }
    public function toArray(): array { return (array)$this; }
}