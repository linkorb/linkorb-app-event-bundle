<?php

namespace LinkORB\AppEventBundle\Logger;

use LinkORB\AppEvent\AppEventLoggerAwareInterface;
use LinkORB\AppEvent\AppEventLoggerInterface;
use LinkORB\AppEvent\AppEventLoggerTrait;

abstract class AbstractEventLoggingService implements AppEventLoggerAwareInterface, AppEventLoggerInterface
{
    use AppEventLoggerTrait;
}
