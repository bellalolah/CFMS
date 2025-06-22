<?php

namespace Cfms\Dto;

use Cfms\Models\Criterion;

class CriterionGroupDto
{
    public int $id;
    public string $name;
    public float $performance; // The calculated 0-5 score

    /** @var QuestionDto[] */
    public array $questions = [];

    /**
     * @param Criterion $criterion
     * @param float $performance
     * @param QuestionDto[] $questionDtos
     */
    public function __construct(Criterion $criterion, float $performance, array $questionDtos)
    {
        $this->id = $criterion->id;
        $this->name = $criterion->name;
        $this->performance = round($performance, 2); // Round to 2 decimal places
        $this->questions = $questionDtos;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'performance' => $this->performance,
            'questions' => array_map(fn(QuestionDto $q) => $q->toArray(), $this->questions)
        ];
    }
}