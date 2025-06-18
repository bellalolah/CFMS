<?php
namespace Cfms\Dto;

use Cfms\Models\Feedback;

class FeedbackDto
{
    public int $id;
    public ?int $answer_value;
    public ?string $answer_text;
    public string $submitted_at;

    public function __construct(Feedback $feedback)
    {
        $this->id = $feedback->id;
        $this->answer_value = $feedback->answer_value;
        $this->answer_text = $feedback->answer_text;
        $this->submitted_at = $feedback->created_at;
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}