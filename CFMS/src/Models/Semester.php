<?php

namespace Cfms\Models;

class Semester
{
    public int $id;
    public string $name;
    public int $session_id;
    public string $status;
    public ?string $start_date;
    public ?string $end_date;

    // For converting DB data (object) into a Semester model
    public function toModel(object $data): self
    {
        $this->id = (int) $data->id;
        $this->name = $data->name;
        $this->session_id = (int) $data->session_id;
        $this->status = $data->status;
        $this->start_date = $data->start_date ?? null;
        $this->end_date = $data->end_date ?? null;

        return $this;
    }

    // For preparing model data to be inserted into the database
    public function getModel(object $data): self
    {
        $this->name = $data->name;
        $this->session_id = (int) $data->session_id;
        $this->status = $data->status ?? 'open';
        $this->start_date = $data->start_date ?? date('Y-m-d');
        $this->end_date = $data->end_date ?? null;

        return $this;
    }
}
