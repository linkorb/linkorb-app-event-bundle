<?php

namespace LinkORB\AppEventBundle\Logger;

use LinkORB\AppEvent\AppEventLoggerAwareInterface;
use LinkORB\AppEvent\AppEventLoggerInterface;
use LinkORB\AppEvent\AppEventLoggerTrait;

abstract class AppEventLoggingService implements AppEventLoggerAwareInterface, AppEventLoggerInterface
{
    use AppEventLoggerTrait;
}
