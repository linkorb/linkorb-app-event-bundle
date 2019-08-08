<?php

namespace LinkORB\AppEventBundle\Logger;

use LinkORB\AppEvent\AppEventLoggerAwareInterface;
use LinkORB\AppEvent\AppEventLoggerInterface;
use LinkORB\AppEvent\AppEventLoggerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AppEventLoggingController extends AbstractController implements AppEventLoggerAwareInterface, AppEventLoggerInterface
{
    use AppEventLoggerTrait;
}
