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
class ResourcesCommandController extends \TYPO3\Flow\Cli\CommandController {

    /**
     * @Flow\Inject
     * @var Resource
     */
    protected $resourceCleanupService;

    /**
     * Fetches the public header and footer menues from passcreator.de
     * @return void
     */
    public function cleanupCommand(){
        $this->createLogger();

        $result = $this->resourceCleanupService->cleanOrphanedResources(50000, $this->logger);
        //echo 'freed space: '.$result['FREED SPACE:'];
        $this->response->appendContent('deleted files: '.count($result['DELETED FILE:']) . "\n");
    }

    protected function createLogger() {
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