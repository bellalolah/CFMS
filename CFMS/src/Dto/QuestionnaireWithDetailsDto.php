<?php
namespace Cfms\Dto;

use Cfms\Models\Questionnaire;

class QuestionnaireWithDetailsDto
{
    public int $id;
    public string $title;
    public string $status;
    public ?int $created_by_user_id;

    // This will now hold the full, detailed course offering object, or null
    public ?array $course_offering = null;

    /** @var QuestionDto[] */
    public array $questions = [];

    // The constructor will now accept the detailed course offering object


    /**
     * @param Questionnaire $questionnaire
     * @param QuestionDto[] $questionDtos
     * @param array|null $courseOfferingDetails  <-- THE FIX IS HERE
     */
    public function __construct(Questionnaire $questionnaire, array $questionDtos, ?array $courseOfferingDetails = null)
    {
        $this->id = $questionnaire->id;
        $this->title = $questionnaire->title;
        $this->status = $questionnaire->status;
        $this->created_by_user_id = $questionnaire->created_by_user_id;
        $this->questions = $questionDtos;

        // This now correctly accepts the array from the service
        $this->course_offering = $courseOfferingDetails;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'course_offering' => $this->course_offering, // This is now the rich object
            'created_by_user_id' => $this->created_by_user_id,
            'questions' => array_map(fn(QuestionDto $q) => $q->toArray(), $this->questions)
        ];
    }
}