<?php
namespace Cfms\Dto;

class PendingQuestionnaireDto
{
    public int $questionnaire_id;
    public string $title;
    public string $status;
    public int $feedback_round;
    public array $course_offering;
    public array $course;
    public array $lecturer;

    /**
     * The constructor takes a single raw data object from our complex SQL query
     * and maps its properties to the DTO's structure.
     *
     * @param object $data A stdClass object from PDO::FETCH_OBJ.
     */
    public function __construct(object $data)
    {
        // Map the main questionnaire details
        $this->questionnaire_id = (int)$data->questionnaire_id;
        $this->title = $data->title;
        $this->status = $data->status;
        $this->feedback_round = (int)$data->feedback_round;

        // Create the nested course_offering object
        $this->course_offering = [
            'id' => (int)$data->course_offering_id
        ];

        // Create the nested course object
        $this->course = [
            'id' => (int)$data->course_id,
            'course_code' => $data->course_code,
            'course_title' => $data->course_title
        ];

        // Create the nested lecturer object
        $this->lecturer = [
            'id' => (int)$data->lecturer_id,
            'full_name' => $data->lecturer_name
        ];
    }

    /**
     * Converts the DTO into a plain array for the final JSON response.
     */
    public function toArray(): array
    {
        // Since all properties are public and already formatted, we can just cast the object.
        return (array)$this;
    }
}