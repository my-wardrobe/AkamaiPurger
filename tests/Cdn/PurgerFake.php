<?php
/**
 * Short description file...
 *
 * Long description file (if need)...
 *
 * @package ${VENDOR}\\${BUNDLE}\\$PACKAGE
 * @author  maurogadaleta
 * @date    30/04/2013 17:55
 */

namespace Mw\Tests\Cdn;

use Monolog\Logger;
use Mw\Cdn\Purger;

class PurgerFake extends Purger
{
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
}