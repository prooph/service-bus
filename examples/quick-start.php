<?php
/**
 * This file is part of the prooph/service-bus.
 * (c) 2014-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2015-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace {
    require_once __DIR__ . '/../vendor/autoload.php';
}

namespace Prooph\ServiceBus\Example\Command {

    use Prooph\Common\Messaging\Command;

    class EchoText extends Command
    {
        /**
         * @var string
         */
        private $text;

        protected $messageName = 'Prooph\ServiceBus\Example\Command\EchoText';

        public function __construct(string $text)
        {
            $this->text = $text;
        }

        public function getText(): string
        {
            return $this->text;
        }

        /**
         * Return message payload as array
         */
        public function payload(): array
        {
            return ['text' => $this->text];
        }

        /**
         * This method is called when message is instantiated named constructor fromArray
         */
        protected function setPayload(array $payload): void
        {
            $this->text = $payload['text'];
        }
    }
}

namespace {
    use Prooph\ServiceBus\CommandBus;
    use Prooph\ServiceBus\Example\Command\EchoText;
    use Prooph\ServiceBus\Plugin\Router\CommandRouter;

    $commandBus = new CommandBus();

    $router = new CommandRouter();

    //Register a callback as CommandHandler for the EchoText command
    $router->route('Prooph\ServiceBus\Example\Command\EchoText')
        ->to(function (EchoText $aCommand) {
            echo $aCommand->getText();
        });

    //Expand command bus with the router plugin
    $commandBus->utilize($router);

    //We create a new Command
    $echoText = new EchoText('It works');

    //... and dispatch it
    $commandBus->dispatch($echoText);

    //Output should be: It works
}
