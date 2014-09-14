<?php
/*
 * This file is part of the prooph/php-service-bus.
 * (c) Alexander Miertsch <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 17.03.14 - 00:26
 */
namespace Prooph\ServiceBus\Example\Resque {

    use Prooph\ServiceBus\Command\Command;
    use Zend\ServiceManager\FactoryInterface;
    use Zend\ServiceManager\ServiceLocatorInterface;

    class WriteLine extends Command
    {
        protected function convertPayload($aText)
        {
            return array('line' => $aText);
        }

        public function getLine()
        {
            return $this->payload['line'];
        }
    }

    class FileWriter
    {
        protected $file;

        public function __construct($aFile)
        {
            if (! file_exists($aFile)) {
                if (! @file_put_contents($aFile, "dump.txt created. File access works as expected.\n")) {
                    throw new \RuntimeException(
                        sprintf(
                            'Can not create file %s. Access denied. Please check the permissions',
                            $aFile
                        )
                    );
                }
            }

            $this->file = $aFile;
        }

        public function handleWriteLine(WriteLine $aCommand)
        {
            if (! @file_put_contents($this->file, $aCommand->getLine() . "\n", FILE_APPEND)) {
                throw new \RuntimeException(
                    sprintf(
                        'Can not write new line to file %s. Access denied. Please check the permissions',
                        $this->file
                    )
                );
            }
        }

        public function getContent()
        {
            return  file_get_contents($this->file);
        }
    }

    class FileWriterFactory implements FactoryInterface
    {
        /**
         * Create service
         *
         * @param ServiceLocatorInterface $serviceLocator
         * @return mixed
         */
        public function createService(ServiceLocatorInterface $serviceLocator)
        {
            $config = $serviceLocator->get('configuration');

            return new FileWriter($config['file_writer']['file']);
        }
    }
}