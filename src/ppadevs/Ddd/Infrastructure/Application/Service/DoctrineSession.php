<?php
namespace ppadevs\Ddd\Infrastructure\Application\Service;

use ppadevs\Ddd\Application\Service\TransactionalSession;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineSession implements TransactionalSession
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * 
     * {@inheritDoc}
     * @see \Ddd\Application\Service\TransactionalSession::executeAtomically()
     */
    public function executeAtomically(callable $operation)
    {
        if (method_exists($this->entityManager, 'wrapInTransaction')) {
            return $this->entityManager->wrapInTransaction($operation);
        }

        return $this->entityManager->transactional($operation);
    }
}

