<?php
namespace Cfms\Services;

use Cfms\Models\Criterion;
use Cfms\Repositories\CriterionRepository;

class CriterionService
{
    public function __construct(private CriterionRepository $criterionRepo) {}

    /**
     * Gets all criteria from the database.
     * In a real app, you might add DTOs here, but for a simple list, this is fine.
     */
    public function getAll(): array
    {
        return $this->criterionRepo->findAllCriterion();
    }

    // In Cfms\Services\CriterionService.php

    public function create(array $data): ?Criterion
    {
        //   Validate the input data
        if (empty($data['name'])) {
            throw new \InvalidArgumentException("Criterion name is required.");
        }

        //  Call the repository to create the record
        $criterion = $this->criterionRepo->createCriterion($data);

        //  If successful, fetch and return the newly created Criterion object
        if ($criterion) {
            // You'll need to add findCriterionById to your repo if it's not there
            return $criterion;
        }

        return null;
    }

    public function update(int $id, array $data): ?Criterion
    {
        if(!$id){
            return null;
        }
        $criterion = $this->criterionRepo->findCriterionById($id);
        if ($criterion) {
            $updatedCriterion = $this->criterionRepo->updateCriterion($id,$data);
            if ($updatedCriterion) {
                return $criterion;
            }
        }
        return null;
    }
}