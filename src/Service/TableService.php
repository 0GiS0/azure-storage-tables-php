<?php

namespace App\Service;

use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Table\Models\EdmType;
use MicrosoftAzure\Storage\Table\Models\Entity;
use MicrosoftAzure\Storage\Table\TableRestProxy;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class TableService
{
    private $logger;
    private $tableClient;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->tableClient = TableRestProxy::createTableService($_SERVER['AZURE_STORAGE_CONNECTION_STRING']);
    }

    private function createTableIfNotExists($tableName)
    {
        try {
            $this->tableClient->getTable($tableName);

        } catch (ServiceException $exception) {
            if ($exception->getCode() == 404) {
                try {
                    $this->tableClient->createTable($tableName);
                } catch (ServiceException $exception) {
                    $this->logger->error('failed to get the entities: ' . $exception->getCode() . ':' . $exception->getMessage());
                    throw $exception;
                }
            }

        }
    }

    public function getEntities($table = 'people')
    {
        $filter = "PartitionKey eq 'BreakingBad'";
        try {
            $this->createTableIfNotExists($table);

            $result = $this->tableClient->queryEntities($table, $filter);
            return $result->getEntities();

        } catch (ServiceException $exception) {
            $this->logger->error('failed to get the entities: ' . $exception->getCode() . ':' . $exception->getMessage());
            throw $exception;
        }
    }

    public function createEntity($firstName, $lastName, $table = 'people')
    {
        try {

            $this->createTableIfNotExists($table);
            $uuid1 = Uuid::uuid1();

            $entity = new Entity();
            $entity->setPartitionKey("BreakingBad");
            $entity->setRowKey($uuid1->toString());
            $entity->addProperty("firstName", EdmType::STRING, $firstName);
            $entity->addProperty("lastName", EdmType::STRING, $lastName);

            $this->tableClient->insertEntity($table, $entity);

        } catch (ServiceException $exception) {
            $this->logger->error('failed to get the entities: ' . $exception->getCode() . ':' . $exception->getMessage());
            throw $exception;
        }
    }
}
