<?php

namespace Cfms\Models;

class Session
{
    public int $id;
    public string $name;
    public string $start_date;
    public ?string $end_date = null;
    public string $status; // 'open' or 'closed'
    public bool $is_active = false;

    /**
     * Constructor to auto-assign today'Utils date as start_date if not given.
     */
    public function __construct()
    {
        // Default to today if not explicitly set
        $this->start_date = date('Y-m-d');
    }

    /**
     * Map DB data to this model
     */
    public function toModel(object $data): self
    {
        $this->id = (int) $data->id;
        $this->name = $data->name;
        $this->start_date = $data->start_date;
        $this->end_date = $data->end_date ?? null;
        $this->status = $data->status;
        $this->is_active = property_exists($data, 'is_active') ? (bool)$data->is_active : false;

        return $this;
    }

    /**
     * Prepare data for insert/update
     */
    public function getModel(): array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->start_date ?? date('Y-m-d'),
            'end_date' => $this->end_date,
            'status' => $this->status,
            'is_active' => $this->is_active,
        ];
    }
}
