<?php
namespace Cfms\KPI\KPIDto;

class CriterionPerformanceDto
{
    public string $criterion_name;
    public string $average_score; // This will be the 0-5 scale score

    public function __construct(string $criterionName, float $averageScore)
    {
        $this->criterion_name = $criterionName;
        $this->average_score =  round($averageScore, 2);
    }

    public function toArray(): array
    {
        return [
            'criterion_name' => $this->criterion_name,
            'average_score' => $this->average_score,
        ];
    }
}