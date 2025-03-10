<?php

namespace Efabrica\NetteRepository\Bridge;

use Efabrica\NetteRepository\CodeGen\EntityStructureFactory;
use Efabrica\NetteRepository\CodeGen\RepositoryCodeGenerationCommand;
use Efabrica\NetteRepository\Repository\RepositoryDependencies;
use Efabrica\NetteRepository\Repository\RepositoryManager;
use Efabrica\NetteRepository\Repository\Scope\ScopeContainer;
use Efabrica\NetteRepository\Subscriber\RepositoryEventSubscriber;
use Efabrica\NetteRepository\Traits\Account\AccountEventSubscriber;
use Efabrica\NetteRepository\Traits\AES\AESEventSubscriber;
use Efabrica\NetteRepository\Traits\Cast\CastEventSubscriber;
use Efabrica\NetteRepository\Traits\Date\DateEventSubscriber;
use Efabrica\NetteRepository\Traits\DefaultOrder\DefaultOrderEventSubscriber;
use Efabrica\NetteRepository\Traits\DefaultValue\DefaultValueEventSubscriber;
use Efabrica\NetteRepository\Traits\Filter\FilterEventSubscriber;
use Efabrica\NetteRepository\Traits\KeepDefault\KeepDefaultEventSubscriber;
use Efabrica\NetteRepository\Traits\LastManStanding\LastManStandingEventSubscriber;
use Efabrica\NetteRepository\Traits\Owner\OwnerEventSubscriber;
use Efabrica\NetteRepository\Traits\SoftDelete\SoftDeleteEventSubscriber;
use Efabrica\NetteRepository\Traits\Sorting\SortingEventSubscriber;
use Efabrica\NetteRepository\Traits\TreeTraverse\TreeTraverseEventSubscriber;
use Efabrica\NetteRepository\Traits\Version\VersionEventSubscriber;
use Efabrica\NetteRepository\Traits\Version\VersionRepository;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Symfony\Component\Console\Application;

class EfabricaNetteRepositoryExtension extends CompilerExtension
{
    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'ignoreTables' => Expect::arrayOf('bool', 'string')->default([
                'migrations' => true,
                'migration_log' => true,
                'phinxlog' => true,
                'phoenix_log' => true,

                // TODO efabrica-specific, move:
                'versions' => true,
                'dashboard_stats' => true,
            ]),
            'inheritance' => Expect::arrayOf(
                Expect::structure([
                    'extends' => Expect::string(),
                    'implements' => Expect::arrayOf('string'),
                ]),
                'string'
            )->default([]),
        ]);
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('codeGenCommand'))->setFactory(RepositoryCodeGenerationCommand::class,
            [$builder->parameters['appDir'], $this->config]);
        $builder->addDefinition($this->prefix('entityStructureFactory'))->setFactory(EntityStructureFactory::class);
        $builder->addDefinition($this->prefix('repoDeps'))->setFactory(RepositoryDependencies::class);
        $builder->addDefinition($this->prefix('repoManager'))->setFactory(RepositoryManager::class);
        $builder->addDefinition($this->prefix('scopeContainer'))->setFactory(ScopeContainer::class);

        $builder->addDefinition('symfonyConsoleApp')
            ->setFactory(Application::class)
            ->addSetup('add', [$builder->getDefinition($this->prefix('codeGenCommand'))])
        ;

        // built-in event subscribers
        $builder->addDefinition($this->prefix('castEventSubscriber'))->setFactory(CastEventSubscriber::class);
        $builder->addDefinition($this->prefix('dateEventSubscriber'))->setFactory(DateEventSubscriber::class);
        $builder->addDefinition($this->prefix('defaultValueEventSubscriber'))->setFactory(DefaultValueEventSubscriber::class);
        $builder->addDefinition($this->prefix('defaultOrderEventSubscriber'))->setFactory(DefaultOrderEventSubscriber::class);
        $builder->addDefinition($this->prefix('filterEventSubscriber'))->setFactory(FilterEventSubscriber::class);
        $builder->addDefinition($this->prefix('keepDefaultEventSubscriber'))->setFactory(KeepDefaultEventSubscriber::class);
        $builder->addDefinition($this->prefix('lastManStandingEventSubscriber'))->setFactory(LastManStandingEventSubscriber::class);
        $builder->addDefinition($this->prefix('softDeleteEventSubscriber'))->setFactory(SoftDeleteEventSubscriber::class);
        $builder->addDefinition($this->prefix('repoEventSubscriber'))->setFactory(RepositoryEventSubscriber::class);
        $builder->addDefinition($this->prefix('sortingEventSubscriber'))->setFactory(SortingEventSubscriber::class);

        // TODO efabrica-specific event subscribers
        $builder->addDefinition($this->prefix('userOwnedEventSubscriber'))->setFactory(AccountEventSubscriber::class);
        $builder->addDefinition($this->prefix('aesEventSubscriber'))->setFactory(AESEventSubscriber::class);
        $builder->addDefinition($this->prefix('ownerEventSubscriber'))->setFactory(OwnerEventSubscriber::class);
        $builder->addDefinition($this->prefix('treeTraverseEventSubscriber'))->setFactory(TreeTraverseEventSubscriber::class);
        $builder->addDefinition($this->prefix('versionEventSubscriber'))->setFactory(VersionEventSubscriber::class);
        $builder->addDefinition('versionRepository')->setFactory(VersionRepository::class);
    }
}
