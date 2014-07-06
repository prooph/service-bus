<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 22:37
 */
namespace {

    require_once '../../vendor/autoload.php';

    chdir(__DIR__);

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include 'classes.php';

    use Prooph\ServiceBus\Example\Resque\WriteLine;
    use Prooph\ServiceBus\Service\ServiceBusConfiguration;
    use Prooph\ServiceBus\Service\ServiceBusManager;
    use Prooph\ServiceBus\Service\StaticServiceBusRegistry;
    use Zend\EventManager\EventInterface;
    use Zend\EventManager\StaticEventManager;

    if (isset($_GET['write'])) {

        //We are on the write side, so the resque-sample-bus needs information of how to send a message
        $serviceBusManager = new ServiceBusManager(
            new ServiceBusConfiguration(array(
                'command_bus' => array(
                    'resque-sample-bus' => array(
                        //Configure the message_dispatcher to be used by the resque-sample-bus
                        'message_dispatcher' => 'php_resque_message_dispatcher'
                    )
                )
            ))
        );

        //The PhpResqueMessageDispatcher uses a Redis-Server to manage background jobs
        //We want to track the status of the job, so we use the Event-System of ServiceBusManager to capture the JobId
        //of a new created Job
        $jobId = null;

        //First we need to enable JobTracking. We make use of the StaticEventManager feature of ZF2 to attach our listener.
        //!!!JobTracking is a PhpResque feature and not supported by all MessageDispatchers.
        StaticEventManager::getInstance()->attach(
            'message_dispatcher',
            'dispatch.pre',
            function (EventInterface $e) {
                //Target of the event is the PhpResqueMessageDispatcher
                $e->getTarget()->activateJobTracking();
            }
        );

        //After the MessageDispatcher has done it's work, we capture the JobId with another EventListener
        StaticEventManager::getInstance()->attach(
            'message_dispatcher',
            'dispatch.post',
            function (EventInterface $e) use (&$jobId) {
                $jobId = $e->getParam('jobId');
            }
        );

        //Prepare the Command
        $writeLine = WriteLine::fromPayload($_GET['write']);

        //...and send it to a receiver via CommandBus
        $serviceBusManager->getCommandBus('resque-sample-bus')->send($writeLine);

        echo 'Message is sent with JobId: ' . $jobId . '. You can check the status with '
            . strtok($_SERVER["REQUEST_URI"], '?') . '<b>?status=' . $jobId . '</b>';

    } elseif (isset($_GET['status'])) {
        $status = new \Resque_Job_Status($_GET['status']);

        switch($status->get()) {
            case \Resque_Job_Status::STATUS_WAITING:
                echo 'Status: waiting. If you did not start a worker yet, than open a console, and run: <b>php '
                    . __DIR__ . '/start-worker.php</b>';
                break;
            case \Resque_Job_Status::STATUS_RUNNING:
                echo 'Status: running. Wait a moment, the job should finish soon.';
                break;
            case \Resque_Job_Status::STATUS_COMPLETE:
                echo 'Status: complete. You should see a new line with your text, when you open: <b>'
                    . strtok($_SERVER["REQUEST_URI"], '?') . '</b>';
                break;
            case \Resque_Job_Status::STATUS_FAILED:
                echo "Status failed: Something went wrong. Stop current worker. Try again writing some text with: "
                    . strtok($_SERVER["REQUEST_URI"], '?') . "<b>?write=some text</b>' "
                    . "and start a new worker with this command: <b>VVERBOSE=1 php " . __DIR__ . "/start-worker.php</b>. "
                    . "You should be able to see the error which causes the job to fail.";
                break;
            default:
                echo "Job can not be found. Maybe you've passed an old or incomplete job id to the status param?";

        }

    } else {
        $serviceBusManager = new ServiceBusManager(StaticServiceBusRegistry::getConfiguration());

        echo "use '" . strtok($_SERVER["REQUEST_URI"], '?') . "<b>?write=some text</b>' to add new lines to the output"
            . "<br><br><font color='grey'>This sample requires a running <b>redis-server</b> and write access to: <b>" . __DIR__ . "</b></font><br><br>";
        echo nl2br($serviceBusManager->get('file_writer')->getContent());
    }
}