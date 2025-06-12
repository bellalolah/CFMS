<?php

namespace Cfms\Repositories;

use Cfms\Models\Session;

class SessionRepository extends BaseRepository
{
    protected $table = 'sessions';

    public function getAllSessions(): array
    {
        $records = $this->findAll($this->table);
        $list = [];

        foreach ($records as $record) {
            $session = new Session();
            $list[] = $session->toModel((object)$record);
        }

        return $list;
    }

    public function getSessionById(int $id): ?Session
    {
        $data = $this->findById($this->table, $id);
        if ($data) {
            $session = new Session();
            return $session->toModel((object)$data);
        }

        return null;
    }

    public function getOpenSession(): ?Session
    {
        $records = $this->findByColumn($this->table, 'status', 'open');
        if (!empty($records)) {
            $session = new Session();
            return $session->toModel((object)$records[0]);
        }
        return null;
    }

    public function createSession(Session $sessionModel): ?Session
    {
        // Check if there's already an open session
        if ($this->getOpenSession()) {
            throw new \Exception("Cannot create new session while another session is open.");
        }

        $data = [
            'name' => $sessionModel->name,
            'status' => 'open',
            'start_date' => date('Y-m-d'),
            'end_date' => null
        ];

        $sessionModel->id = $this->insert($this->table, $data);

        if ($sessionModel->id) {
            return $sessionModel->getModel((object)$data);
        }

        return null;
    }

    public function closeSession(int $id): bool
    {
        return $this->update($this->table, [
            'status' => 'closed',
            'end_date' => date('Y-m-d')
        ], $id);
    }
}
