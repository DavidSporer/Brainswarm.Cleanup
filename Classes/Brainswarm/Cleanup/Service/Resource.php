<?php
/**
 * Created by PhpStorm.
 * User: davidsporer
 * Date: 06.04.16
 * Time: 08:43
 */
namespace Brainswarm\Cleanup\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Log\LoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class Resource
{
    /**
     * @var \TYPO3\Flow\Resource\ResourceManager
     * @Flow\Inject
     */
    protected $resourceManager;
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     * @Flow\Inject
     */
    protected $entityManager;
    /**
     * @Flow\Inject
     * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $usedResources;

    /**
     * @var array
     */
    protected $deletedResources;

    /**
     * @var array
     */
    protected $deletedFiles;

    public function cleanUnusedResources($numberOfResourcesToCleanup, $offset, LoggerInterface $logger)
    {
        $em = $this->entityManager;
        $connection = $em->getConnection();
        $sql = 'SELECT resourcepointer, persistence_object_identifier FROM typo3_flow_resource_resource LIMIT ' . $numberOfResourcesToCleanup . ' OFFSET ' . $offset . ';';
        echo $sql . "\n";
        $query = $connection->prepare($sql);
        $query->execute();
        $result = $query->fetchAll();

        foreach ($result as $resultSet) {
            $resourcepointer = $resultSet['resourcepointer'];
            $this->cleanUpResourceByResourcepointer($resourcepointer, $logger);
        }
    }

    /**
     * @param int $maximumNumberOfResourcesToCleanup
     * @param LoggerInterface $logger
     * @return int
     * @throws DBALException
     */
    public function cleanOrphanedResources(LoggerInterface $logger)
    {
        $directoryName = FLOW_PATH_DATA . 'Persistent/Resources/';
        $dh = opendir($directoryName);

        echo 'start reading folder ' . $directoryName . "\n";
        while (false !== ($filename = readdir($dh))) {
            if (is_file($directoryName . $filename)) {
                $this->cleanUpResourceByResourcepointer($filename, $logger);
            }
        }

        $logger->log("\n\n\nNo of deleted files: " . count($this->deletedFiles) . "\nNo of deleted resources: " . count($this->deletedResources));

        return count($this->deletedFiles);
    }

    /**
     * @param $resourcepointer
     * @param LoggerInterface $logger
     */
    protected function cleanUpResourceByResourcepointer($resourcepointer, LoggerInterface $logger)
    {
        $directoryName = FLOW_PATH_DATA . 'Persistent/Resources/';
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->entityManager;
        $connection = $em->getConnection();

        $query = $connection->prepare('SELECT persistence_object_identifier FROM typo3_flow_resource_resource WHERE resourcepointer = ?;');
        $query->execute(array($resourcepointer));
        $result = $query->fetchAll();
        if (!$result) {
            $query = $connection->prepare('DELETE FROM typo3_flow_resource_resourcepointer WHERE hash = ?');
            $query->execute(array($resourcepointer));

            foreach (glob(FLOW_PATH_WEB . '_Resources/Persistent/' . $resourcepointer . '*.*') as $published) {
                unlink($published);
            }
            unlink($directoryName . $resourcepointer);
            $deletedFiles[] = $resourcepointer;
            echo FLOW_PATH_WEB . '_Resources/Persistent/' . $resourcepointer . '*.*' . "\n";
            $logger->log('deleted file ' . FLOW_PATH_WEB . '_Resources/Persistent/' . $resourcepointer . '*.*' . "\n");
        } else {
            try {
                foreach ($result as $resultSet) {
                    $resourceIdentifier = $resultSet['persistence_object_identifier'];
                    $query = $connection->prepare('DELETE FROM typo3_flow_resource_resource WHERE persistence_object_identifier = ?');
                    $query->execute(array($resourceIdentifier));
                    $deletedResources[] = $resourceIdentifier;
                }
                $connection->executeQuery('DELETE FROM typo3_flow_resource_resourcepointer WHERE hash = \''. $resourcepointer.'\'');

                foreach (glob(FLOW_PATH_WEB . '_Resources/Persistent/' . $resourcepointer . '*') as $published) {
                    unlink($published);
                }
                unlink($directoryName . $resourcepointer);

                echo FLOW_PATH_WEB . '_Resources/Persistent/' . $resourcepointer . '*.*' . "\n";
                $logger->log('deleted file ' . FLOW_PATH_WEB . '_Resources/Persistent/' . $resourcepointer . '*.*' . "\n");
            } catch (DBALException $e) {
                $logger->log('Exception occurred while deleting resource with hash ' . $resourcepointer . '. The error was: ' . $e->getMessage() . "\n");
                $this->usedResources[] = $resourcepointer;
            }
        }
    }

}