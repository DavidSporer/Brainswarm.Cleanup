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
     * @param int $maximumNumberOfResourcesToCleanup
     * @param LoggerInterface $logger
     * @return int
     * @throws DBALException
     */
    public function cleanOrphanedResources($maximumNumberOfResourcesToCleanup, LoggerInterface $logger)
    {
        $directoryName = FLOW_PATH_DATA . 'Persistent/Resources/';
        $dh = opendir($directoryName);
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->entityManager;
        $connection = $em->getConnection();
        $sum = 0;
        $deletedFiles = array();
        $deletedResources = array();
        $usedResources = array();
        while (false !== ($filename = readdir($dh))) {
            if (is_file($directoryName . $filename)) {
                $query = $connection->prepare('SELECT persistence_object_identifier FROM typo3_flow_resource_resource WHERE resourcepointer = ?;');
                $query->execute(array($filename));
                $result = $query->fetchAll();
                if (!$result) {
                    $query = $connection->prepare('DELETE FROM typo3_flow_resource_resourcepointer WHERE hash = ?');
                    $query->execute(array($filename));
                    $sum += filesize($directoryName . $filename);
                    foreach (glob(FLOW_PATH_WEB . '_Resources/Persistent/' . $filename . '*.*') as $published) {
                        unlink($published);
                    }
                    unlink($directoryName . $filename);
                    $deletedFiles[] = $filename;
                    echo FLOW_PATH_WEB . '_Resources/Persistent/' . $filename . '*.*' . "\n";
                    $logger->log('deleted file ' . FLOW_PATH_WEB . '_Resources/Persistent/' . $filename . '*.*' . "\n");
                } else {
                    try {
                        foreach ($result as $resultSet) {
                            $resourceIdentifier = $resultSet['persistence_object_identifier'];
                            $query = $connection->prepare('DELETE FROM typo3_flow_resource_resource WHERE resourcepointer = ?');
                            $query->execute(array($resourceIdentifier));
                            $deletedResources[] = $resourceIdentifier;
                        }
                        $connection->executeQuery('DELETE FROM typo3_flow_resource_resourcepointer WHERE hash = ?');
                        $sum += filesize($directoryName . $filename);
                        foreach (glob(FLOW_PATH_WEB . '_Resources/Persistent/' . $filename . '*') as $published) {
                            unlink($published);
                        }
                        unlink($directoryName);
                    } catch (DBALException $e) {
                        $usedResources[] = $filename;
                    }
                }
            }

            if (count($deletedFiles) > $maximumNumberOfResourcesToCleanup) {
                break;
            }
        }

        $logger->log("\n\n\nNo of deleted files: " . count($deletedFiles) . "\nNo of deleted resources: " . count($deletedResources));

        return count($deletedFiles);
    }
}