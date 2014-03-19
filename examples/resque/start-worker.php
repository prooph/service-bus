<?php
/*
 * This file is part of the codeliner/php-service-bus.
 * (c) Alexander Miertsch <kontakt@codeliner.ws>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Date: 16.03.14 - 23:46
 */
chdir(__DIR__);

require_once '../../vendor/autoload.php';

include 'classes.php';

putenv('QUEUE=resque-sample-queue');
putenv('APP_INCLUDE=classes.php');

include '../../vendor/chrisboulton/php-resque/resque.php';