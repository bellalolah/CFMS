<?php

namespace Cfms\Dto;

use Cfms\Models\Questionnaire;

class QuestionnaireWithGroupedCriteriaDto
{
    public int $id;
    public string $title;
    public string $status;
    public ?int $created_by_user_id;

    // --- NEW ---
    public  string $overall_performance; // Will hold the 0-100 score

    public ?array $course_offering = null;

    /** @var CriterionGroupDto[] */
    public array $criteria_groups = [];

    /**
     * @param Questionnaire $questionnaire
     * @param CriterionGroupDto[] $criterionGroupDtos
     * @param string $overallPerformance   <-- NEW: Pass in the calculated score
     * @param array|null $courseOfferingDetails
     */
    public function __construct(
        Questionnaire $questionnaire,
        array $criterionGroupDtos,
        string $overallPerformance, // <-- NEW
        ?array $courseOfferingDetails = null
    ) {
        $this->id = $questionnaire->id;
        $this->title = $questionnaire->title;
        $this->status = $questionnaire->status;
        $this->created_by_user_id = $questionnaire->created_by_user_id;
        $this->course_offering = $courseOfferingDetails;
        $this->criteria_groups = $criterionGroupDtos;

        // --- NEW ---
        // Assign the new property, rounding for a clean output
        $this->overall_performance = $overallPerformance;
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'overall_performance' => $this->overall_performance, // <-- NEW
            'course_offering' => $this->course_offering,
            'created_by_user_id' => $this->created_by_user_id,
            'criteria_groups' => array_map(fn(CriterionGroupDto $cg) => $cg->toArray(), $this->criteria_groups)
        ];
    }
}