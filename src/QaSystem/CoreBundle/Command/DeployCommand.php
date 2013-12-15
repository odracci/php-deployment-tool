<?php

namespace QaSystem\CoreBundle\Command;

use Monolog\Logger;
use QaSystem\CoreBundle\Entity\Deployment;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DeployCommand extends ContainerAwareCommand
{
    const NAME = 'deployment:tools:deploy';

    protected function configure()
    {
        $this
            ->setName(static::NAME)
            ->setDescription('Trigger a deployment')
            ->addArgument('deploymentId', InputArgument::REQUIRED);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $deploymentId = $input->getArgument('deploymentId');

        $deployment = $this->getEntityManager()
            ->getRepository('QaSystemCoreBundle:Deployment')
            ->findOneById($deploymentId);

        if (is_null($deployment)) {
            throw new \RuntimeException("Deployment $deploymentId not found");
            return null;
        }

        if ($deployment->getStatus() !== Deployment::STATUS_PENDING) {
            throw new \RuntimeException("Deployment $deploymentId aborted, status is not pending");
            return null;
        }

        $this->getContainer()->get('deployment_tool')->deploy($deployment);
    }
}
