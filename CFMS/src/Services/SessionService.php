<?php
namespace Cfms\Services;

use Cfms\Repositories\SessionRepository;
use Cfms\Repositories\SemesterRepository;
use Cfms\Models\Session;
use Cfms\Models\Semester;
use Cfms\Dto\SessionDto;
use Cfms\Dto\SemesterDto;

class SessionService
{
    public function __construct(
        private SessionRepository $sessionRepo,
        private SemesterRepository $semesterRepo
    ) {}

    public function createSessionWithSemesters(array $sessionData, array $semestersData): ?array
    {
        // The service's only job is to call the correct repository method.
        return $this->sessionRepo->createSessionAndSemesters($sessionData, $semestersData);
    }

    public function updateSessionWithSemester($sessionId, array $sessionData, $semesterId, array $semesterData): array
    {
        $updatedSession = $this->sessionRepo->updateSession($sessionId, $sessionData);
        $updatedSemester = $this->semesterRepo->updateSemester($semesterId, $semesterData);
        return ['session' => $updatedSession, 'semester' => $updatedSemester];
    }

    public function deleteSessionWithSemester($sessionId, $semesterId): bool
    {
        $this->semesterRepo->deleteSemester($semesterId);
        return $this->sessionRepo->deleteSession($sessionId);
    }

    public function getCurrentSession(): ?SessionDto
    {
        $session = $this->sessionRepo->getActiveSession();
        if (!$session) return null;
        $semesters = $this->semesterRepo->findBySessionId($session->id);
        $semesterDtos = array_map(fn($s) => new SemesterDto($s), $semesters);
        return new SessionDto($session, $semesterDtos);
    }

    public function activateSession($sessionId): bool
    {
        // Set all sessions to inactive
        $this->sessionRepo->updateAll(['is_active' => false]);
        // Set the specified session to active
        return $this->sessionRepo->updateSession($sessionId, ['is_active' => true]);
    }

    public function createSemester(array $data): int
    {
        return $this->semesterRepo->createSemester($data);
    }

    public function getSessionsByDateRange(string $from, string $to): array
    {
        return $this->sessionRepo->findSessionsByDateRange($from, $to);
    }


    public function activeSessionSemester(): ?array
    {
        $session = $this->sessionRepo->getActiveSessionWithSemesters();
        if (!$session) return null;

        return $session;
    }
}
