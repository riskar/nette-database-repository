<?php

namespace Efabrica\NetteRepository\Event;

use Efabrica\NetteRepository\Model\Entity;
use Efabrica\NetteRepository\Repository\Query;
use Efabrica\NetteRepository\Repository\Repository;
use Efabrica\NetteRepository\Repository\RepositoryBehaviors;
use Efabrica\NetteRepository\Subscriber\EventSubscriber;
use Efabrica\NetteRepository\Traits\RepositoryBehavior;
use LogicException;

/**
 * @template E of Entity
 * @template R *EntityEventResponse
 */
abstract class RepositoryEvent
{
    /**
     * @var EventSubscriber[]
     */
    protected array $subscribers = [];

    /**
     * @var Repository<E, Query<E>>
     */
    private Repository $repository;

    /**
     * @param Repository<E, Query<E>> $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->subscribers = $repository->getEventSubscribers()->toArray();
    }

    public function hasEnded(): bool
    {
        return $this->subscribers === [];
    }

    /**
     * @return class-string<E>
     */
    public function getEntityClass(): string
    {
        return $this->repository->getEntityClass();
    }

    public function getRepository(): Repository
    {
        return $this->repository;
    }

    public function getBehaviors(): RepositoryBehaviors
    {
        return $this->repository->getBehaviors();
    }

    /**
     * @param class-string<RepositoryBehavior> $class
     * @return bool
     */
    public function hasBehavior(string $class): bool
    {
        return $this->getBehaviors()->has($class);
    }

    /**
     * @template T of RepositoryBehavior
     * @param class-string<T> $class
     * @return T
     */
    public function getBehavior(string $class): RepositoryBehavior
    {
        $behavior = $this->getBehaviors()->get($class);
        if (!$behavior instanceof RepositoryBehavior) {
            throw new LogicException('getBehavior() was called incorrectly');
        }
        return $behavior;
    }

    /**
     * Stop the execution of the event chain.
     * @return mixed
     */
    abstract public function stopPropagation();
}
