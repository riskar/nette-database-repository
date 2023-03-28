<?php

namespace Efabrica\NetteDatabaseRepository\Behavior;

use DateTime;
use Efabrica\NetteDatabaseRepository\Models\ActiveRow;
use Efabrica\NetteDatabaseRepository\Repositores\Repository;
use Nette\Database\Table\Selection;

class SoftDeleteBehavior extends Behavior
{
    private string $deletedAt;

    private Repository $repository;

    private bool $isDefaultWhere = true;

    public function __construct(Repository $repository, string $deletedAt = 'deleted_at')
    {
        $this->deletedAt = $deletedAt;
        $this->repository = $repository;
    }

    public function setIsDefaultWhere(bool $isDefaultWhere): void
    {
        $this->isDefaultWhere = $isDefaultWhere;
    }

    public function beforeDelete(ActiveRow $row): ?bool
    {
        foreach ($this->repository->getBehaviors() as $behavior) {
            if ($behavior instanceof BehaviorWithSoftDelete) {
                $behavior->beforeSoftDelete($row);
            }
        }
        $this->repository->raw()->update($row, [$this->deletedAt => new DateTime()]);
        foreach ($this->repository->getBehaviors() as $behavior) {
            if ($behavior instanceof BehaviorWithSoftDelete) {
                $behavior->afterSoftDelete($row);
            }
        }
        return true;
    }

    public function beforeSelect(Selection $selection): void
    {
        if ($this->isDefaultWhere) {
            $selection->where($this->deletedAt . ' < ?', new DateTime());
        }
    }
}
