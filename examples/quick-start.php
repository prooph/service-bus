<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 15.03.14 - 22:34
 */
namespace {
    require_once '../vendor/autoload.php';
}

namespace Prooph\ServiceBus\Example\Command {

    use Prooph\ServiceBus\Command\AbstractCommand;

    class EchoText extends AbstractCommand
    {
        protected function convertPayload($textOrPayload)
        {
            if (is_string($textOrPayload)) {
                $textOrPayload = array('text' => $textOrPayload);
            }

            return $textOrPayload;
        }

        public function getText()
        {
            return $this->payload['text'];
        }
    }
}

namespace {
    use Prooph\ServiceBus\Example\Command\EchoText;
    use Prooph\ServiceBus\Service\ServiceBusConfiguration;
    use Prooph\ServiceBus\Service\ServiceBusManager;

    //The ServiceBus environment is set up by a special configuration class
    $serviceBusConfig = new ServiceBusConfiguration();

    //Register a callback as CommandHandler for the EchoText command
    $serviceBusConfig->setCommandMap(array(
        'Prooph\ServiceBus\Example\Command\EchoText' => function (EchoText $aCommand) {
            echo $aCommand->getText();
        }
    ));

    //The ServiceBusManager is the central class, that manages the complete service bus environment
    $serviceBusManager = new ServiceBusManager($serviceBusConfig);

    //We create a new Command
    $echoText = EchoText::fromPayload('It works');

    //... and send it to a handler via routing system of the ServiceBus
    $serviceBusManager->route($echoText);

    //Output should be: It works
}

