<?php
/**
 * Created by PhpStorm.
 * User: davidsporer
 * Date: 06.04.16
 * Time: 09:01
 */
namespace Brainswarm\Cleanup\Command;

use Brainswarm\Cleanup\Service\Resource;
use TYPO3\Flow\Annotations as Flow;

/**
 * PassTemplate controller for the SporerWebservices.PassbookPasses package
 *
 * @Flow\Scope("singleton")
 */
class ResourcesCommandController extends \TYPO3\Flow\Cli\CommandController
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
     * @Flow\Inject
     * @var Resource
     */
    protected $resourceCleanupService;

    /**
     * Iterates over all resources on the filesystem and looks if they are still used. If not they're deleted.
     * @return void
     */
    public function cleanupCommand()
    {
        $this->createLogger();

        $result = $this->resourceCleanupService->cleanOrphanedResources($this->logger);
        $this->response->appendContent('deleted files: ' . $result . "\n");
    }

    /**
     * Looks in the database for unused resources and deletes them from the database and the file storage
     * @return  void
     */
    public function cleanupByDbCommand() {
        $this->createLogger();

        $em = $this->entityManager;
        $connection = $em->getConnection();
        $query = $connection->prepare('SELECT * FROM typo3_flow_resource_resource;');
        $query->execute();
        $result = $query->fetchAll();

        $numberOfResources = count($result);

		echo 'number of resources: '. $numberOfResources . " \n";
		
        // how many resources should be cleaned in one chunk
        $interval = 40000;

        $numberOfChunks = round($numberOfResources / $interval) + 1;

        $offset = 0;
        $index = 0;

        while($index < $numberOfChunks) {
            $this->resourceCleanupService->cleanUnusedResources($interval, $offset, $this->logger);

            $index++;
            $offset = $index * $interval;
        }

    }

    protected function createLogger()
    {
        $this->logger = \TYPO3\Flow\Log\LoggerFactory::create(
            'myLoggerName',
            'TYPO3\Flow\Log\Logger',
            '\TYPO3\Flow\Log\Backend\FileBackend',
            array(
                'logFileURL' => FLOW_PATH_DATA . 'Logs/FileCleanup.log',
                'createParentDirectories' => true,
                'severityThreshold' => LOG_INFO,
                'maximumLogFileSize' => 10485760,
                'logFilesToKeep' => 1,
                'logIpAddress' => true
            ));
    }
}

?>