<?php

namespace Efabrica\NetteRepository\Repository\Scope;

use Efabrica\NetteRepository\Repository\Repository;
use Efabrica\NetteRepository\Repository\RepositoryBehaviors;

/**
 * @immutable
 */
#[\Jetbrains\PhpStorm\Immutable]
interface Scope
{
    public function apply(RepositoryBehaviors $behaviors, Repository $repository): void;
}
