<?php

namespace Dell\Cfms\KPI\KPIDto;


class LecturerKPIDto
{
    public int $lecturer_id;
    public string $lecturer_name;
    public int $total_ratings_received;
    public string $normalized_avg_score;

    public function __construct(array $data)
    {
        $this->lecturer_id = (int) $data['lecturer_id'];
        $this->lecturer_name = $data['lecturer_name'];
        $this->total_ratings_received = (int) $data['total_ratings_received'];
        $this->normalized_avg_score =  $data['normalized_avg_score'];
    }
}
