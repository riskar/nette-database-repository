<?php

namespace Efabrica\NetteRepository\Traits\AES;

use Efabrica\NetteRepository\Event\InsertEventResponse;
use Efabrica\NetteRepository\Event\InsertRepositoryEvent;
use Efabrica\NetteRepository\Event\RepositoryEvent;
use Efabrica\NetteRepository\Event\SelectQueryEvent;
use Efabrica\NetteRepository\Event\SelectQueryResponse;
use Efabrica\NetteRepository\Event\UpdateQueryEvent;
use Efabrica\NetteRepository\Repository\Repository;
use Efabrica\NetteRepository\Subscriber\EventSubscriber;
use Nette\Database\Row;
use Nette\Database\Table\ActiveRow;

final class AESEventSubscriber extends EventSubscriber
{
    public function supportsEvent(RepositoryEvent $event): bool
    {
        return $event->hasBehavior(AESBehavior::class);
    }

    public function onSelect(SelectQueryEvent $event): SelectQueryResponse
    {
        /** @var AESBehavior $behavior */
        $behavior = $event->getBehavior(AESBehavior::class);
        $repository = $event->getRepository();
        $selectParts = [$repository->getTableName() . '.*'];
        foreach ($behavior->encryptedFields() as $field) {
            $selectParts[] = $this->convertEncryptedField($repository, $behavior, $field) . ' AS ' . $field;
        }
        $event->getQuery()->select($selectParts);
        return $event->handle();
    }

    public function onInsert(InsertRepositoryEvent $event): InsertEventResponse
    {
        /** @var AESBehavior $behavior */
        $behavior = $event->getBehaviors()->get(AESBehavior::class);
        $repository = $event->getRepository();
        foreach ($event->getEntities() as $entity) {
            foreach ($behavior->encryptedFields() as $field) {
                $value = $entity[$field];
                if (is_string($value)) {
                    $entity[$field] = $this->encryptValue($repository, $behavior, $value);
                }
            }
        }
        return $event->handle();
    }

    public function onUpdate(UpdateQueryEvent $event, array &$data): int
    {
        /** @var AESBehavior $behavior */
        $behavior = $event->getBehaviors()->get(AESBehavior::class);
        $repository = $event->getRepository();
        foreach ($behavior->encryptedFields() as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->encryptValue($repository, $behavior, $data[$field]);
            }
        }
        return $event->handle($data);
    }

    protected function convertEncryptedField(Repository $repository, AESBehavior $behavior, string $field): string
    {
        if ($this->ivFunction($repository) !== '') {
            return 'CONVERT(AES_DECRYPT(UNHEX(SUBSTRING(' . $repository->getTableName() . '.' . $field . ', 33)), ' . $behavior->keyFunction() . ', UNHEX(SUBSTRING(' . $repository->getTableName() . '.' . $field . ', 1, 32))) USING utf8)';
        }
        return 'CONVERT(AES_DECRYPT(UNHEX(' . $repository->getTableName() . '.' . $field . '), ' . $behavior->keyFunction() . $this->ivFunction($repository) . ') USING utf8)';
    }

    protected function encryptValue(Repository $repository, AESBehavior $behavior, string $value): string
    {
        $ivFunction = $this->ivFunction($repository);
        if ($ivFunction !== '') {
            /** @var literal-string $ivFunction */
            $initVector = $repository->getExplorer()->fetch($ivFunction);
            assert($initVector instanceof Row);
            $random = $initVector->random;
            assert(is_string($random));
            $randomBytes = addslashes($random);

            /** @var literal-string $queryString */
            $queryString = 'SELECT HEX(CONCAT("' . $randomBytes . '", AES_ENCRYPT("' . addslashes($value) . '", ' . $behavior->keyFunction() . ', "' . $randomBytes . '"))) AS encrypted';
            /** @var  ActiveRow $row */
            $row = $repository->getExplorer()->fetch($queryString);
            $encrypted = $row['encrypted'];
            assert(is_string($encrypted));
            return $encrypted;
        }

        /** @var literal-string $queryString */
        $queryString = 'SELECT HEX(AES_ENCRYPT("' . addslashes($value) . '", ' . $behavior->keyFunction() . $this->ivFunction($repository) . ')) AS encrypted';
        /** @var ActiveRow $row */
        $row = $repository->getExplorer()->fetch($queryString);
        $encrypted = $row['encrypted'];
        assert(is_string($encrypted));
        return $encrypted;
    }

    private function ivFunction(Repository $repository): string
    {
        $blockEncryptionMode = $repository->getExplorer()->fetch('SHOW variables WHERE Variable_name = "block_encryption_mode"');
        $blockEncryptionModeValue = $blockEncryptionMode instanceof Row ? $blockEncryptionMode->Value : 'aes-128-ecb';

        if (str_contains($blockEncryptionModeValue, '-cbc')) {
            return 'SELECT RANDOM_BYTES(16) AS random';
        }
        return '';
    }
}
