<?php

namespace Efabrica\NetteRepository\Traits\Sorting;

use Efabrica\NetteRepository\Traits\DefaultOrder\DefaultOrderBehavior;
use Efabrica\NetteRepository\Traits\RepositoryBehavior;

class SortingBehavior extends DefaultOrderBehavior
{
    public const DEFAULT_STEP = 100;

    private string $column;

    private int $step;

    private bool $ascending;

    public function __construct(string $column = 'sorting', int $step = self::DEFAULT_STEP, bool $ascending = true)
    {
        $this->column = $column;
        $this->step = $step;
        $this->ascending = $ascending;
        parent::__construct($column.' '.$this->getDirection());
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function isAscending(): bool
    {
        return $this->ascending;
    }

    public function getDirection(): string
    {
        return $this->isAscending() ? 'ASC' : 'DESC';
    }
}
