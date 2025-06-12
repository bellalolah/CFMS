<?php

namespace Cfms\Services;

use Cfms\Repositories\SessionRepository;

class SessionService
{
    private $sessionRepository;

    public function __construct(SessionRepository $sessionRepo)
    {
        $this->sessionRepository = $sessionRepo;
    }

    public function createSession(array $data): ?\Cfms\Models\Session
    {
        // Close any currently open session
        $this->sessionRepository->closeCurrentSession();

        // Create new session
        return $this->sessionRepository->createSession($data);
    }

    public function endSession(int $sessionId): bool
    {
        return $this->sessionRepository->closeSession($sessionId);
    }

    public function getAllSessions(): array
    {
        return $this->sessionRepository->getAllSessions();
    }

    public function getCurrentSession(): ?\Cfms\Models\Session
    {
        return $this->sessionRepository->getCurrentSession();
    }
}
