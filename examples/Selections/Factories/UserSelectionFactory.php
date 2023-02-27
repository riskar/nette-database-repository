<?php

namespace Examples\Selections\Factories;

use Efabrica\NetteDatabaseRepository\Selections\Factories\SelectionFactoryInterface;
use Examples\Selections\UserSelection;

interface UserSelectionFactory extends SelectionFactoryInterface
{
    public function create(string $tableName): UserSelection;
}
