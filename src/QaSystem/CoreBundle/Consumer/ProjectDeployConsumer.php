<?php

namespace QaSystem\CoreBundle\Consumer;

use Monolog\Logger;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Process\Process;
use QaSystem\CoreBundle\Command\DeployCommand;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;

class ProjectDeployConsumer implements ConsumerInterface
{

    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @param $rootDir
     * @param Logger $logger
     */
    function __construct($rootDir, Logger $logger)
    {
        $this->logger = $logger;
        $this->rootDir = $rootDir;
    }

    /**
     * @param AMQPMessage $msg
     * @return bool
     */
    public function execute(AMQPMessage $msg)
    {
        $logger = $this->logger;
        $rootDir = $this->rootDir;
        $data = unserialize($msg->body);
        $commandName = DeployCommand::NAME;

        $deploymentId = $data['deploymentId'];

        $process = new Process("php $rootDir/console $commandName $deploymentId");
        $process->run(function ($type, $buffer) use ($logger) {
            if (Process::ERR === $type) {
                $logger->err($buffer);
            } else {
                $logger->info($buffer);
            }
        });

        return true;
    }
}
