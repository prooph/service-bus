<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 18:55
 */

namespace Prooph\ServiceBusTest\Message\PhpResque;

use Prooph\ServiceBus\Service\ServiceBusConfiguration;
use Prooph\ServiceBus\Service\ServiceBusManager;
use Prooph\ServiceBus\Service\StaticServiceBusRegistry;
use Prooph\ServiceBusTest\Mock\RemoveFileCommand;
use Prooph\ServiceBusTest\TestCase;
use Zend\EventManager\EventInterface;
use Zend\EventManager\StaticEventManager;

/**
 * Class PhpResqueMessageDispatcherTest
 *
 * @package Prooph\ServiceBusTest\Message\PhpResque
 * @author Alexander Miertsch <contact@prooph.de>
 */
class PhpResqueMessageDispatcherTest extends TestCase
{
    protected $testFile;

    protected $orgDirPermissions;

    protected function setUp()
    {
        $this->testFile = __DIR__ . '/delete-me.txt';

        $this->orgDirPermissions = fileperms(__DIR__);

        chmod(__DIR__, 0770);

        file_put_contents($this->testFile, 'I am just a testfile. You can delete me.');
    }

    protected function tearDown()
    {
        StaticServiceBusRegistry::reset();

        @unlink($this->testFile);

        chmod(__DIR__, $this->orgDirPermissions);
    }

    /**
     * @test
     */
    public function it_sends_remove_file_command_to_file_remover_via_php_resque()
    {
        $this->assertTrue(file_exists($this->testFile));

        $config = new ServiceBusConfiguration(array(
            'command_bus' => array(
                'php-resque-test-bus' => array(
                    'message_dispatcher' => 'php_resque_message_dispatcher'
                )
            )
        ));

        $serviceBusManager = new ServiceBusManager($config);

        $jobId = null;

        StaticEventManager::getInstance()->attach(
            'message_dispatcher',
            'dispatch.pre',
            function (EventInterface $e) {
                $e->getTarget()->activateJobTracking();
            }
        );

        StaticEventManager::getInstance()->attach(
            'message_dispatcher',
            'dispatch.post',
            function (EventInterface $e) use (&$jobId) {
                $jobId = $e->getParam('jobId');
            }
        );

        $removeFile = RemoveFileCommand::fromPayload($this->testFile);

        $serviceBusManager->getCommandBus('php-resque-test-bus')->send($removeFile);

        $this->assertNotNull($jobId);

        $status = new \Resque_Job_Status($jobId);

        $this->assertEquals(\Resque_Job_Status::STATUS_WAITING, $status->get());

        $worker = new \Resque_Worker(array('php-resque-test-bus'));

        $worker->work(0);

        $this->assertEquals(\Resque_Job_Status::STATUS_COMPLETE, $status->get());

        $this->assertFalse(file_exists($this->testFile));
    }
}
 