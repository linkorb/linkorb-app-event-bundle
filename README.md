# linkorb/app-event-bundle

Integrates and provides a handy configuration for linkorb/app-event and its standard scheme for
logging Application Events.

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require linkorb/app-event-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require linkorb/app-event-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    LinkORB\AppEventBundle\LinkORBAppEventBundle::class => ['all' => true],
];
```

Usage
=====

The bundle will automatically inject a special App Event logger into services
and controllers which implement AppEventLoggerAwareInterface.  There are a few
ways to achieve this.

Use AppEventLoggerTrait which provides an implementation of both
AppEventLoggerAwareInterface and AppEventLoggerInterface:

```php

use LinkORB\AppEvent\AppEventLoggerAwareInterface;
use LinkORB\AppEvent\AppEventLoggerInterface;
use LinkORB\AppEvent\AppEventLoggerTrait;

class MyService implements AppEventLoggerAwareInterface,
    AppEventLoggerInterface
{
    use AppEventLoggerTrait;

    public function myMethod()
    {
        // using the trait makes it very simple to add AppEvent logging:
        $this->log('my.app.event', ['some-info' => ...], 'notice');
    }
}
```

If your Controllers extend Symfony's AbstractController you can instead make
them extend AppEventLoggingController which does the above for you and also
extends Symfony's AbstractController:

```php

use LinkORB\AppEventBundle\Logger\AppEventLoggingController;

class MyController extends AppEventLoggingController
{
    public function myAction()
    {
        $this->log('my.app.event', ['some-info' => ...], 'notice');
    }
}
```

Your services can extend AppEventLoggingService to get the same benefit:

```php

use LinkORB\AppEventBundle\Logger\AppEventLoggingService;

class MyService extends AppEventLoggingService
{
    public function myMethod()
    {
        $this->log('my.app.event', ['some-info' => ...], 'notice');

        // by omission of the third argument, log() will log to the minimum log
        // level, which is whatever you set in the Monolog handler config
        $this->log('my.app.event', ['some-info' => ...);

        // you can also call the logger methods directly, but only do this
        // when the bundle is configured in all environments
        $this->appEventLogger->error('my.app.event', ['some-info' => ...]);
    }
}
```

Configuration
=============

You need to create a Monolog configuration for each of the environments in
which the bundle is enabled (which by default is all of them).  Put this in
each of the Monolog config files:

```yaml
monolog:
  channels:
    - app_event
  handlers:
    app_events:
      type: stream
      path: "%kernel.logs_dir%/app-events-%kernel.environment%.ndjson"
      level: info
      channels: ["app_event"]
```

In the above config, we instruct Symfony's Monolog Bundle to create an
additional Logger service with the name `monolog.logger.app_event` and to
create an instance of Monlog's StreamHandler which will be used by our logger
to write to the file at `path`.  The minimum logging level for our logger is
set to INFO.  This is the minimum you need to do to configure the logger, but
there are a few extra things you can configure.

The following configurations can be set independently in each environment, for
example you could place the directives in a file named
`config/packages/prod/linkorb_app_event.yaml`.  If the bundle has been enabled
in every environment then you can configure it simultaneously for all
environments in `config/packages/linkorb_app_event.yaml`.

You can turn off the TokenProcessor which automatically adds information to App
events about the authenticated user:

```yaml
linkorb_app_event:
  token_processor: false
```

You can turn on the TagProcessor which will add your tags to App events:

```yaml
linkorb_app_event:
  tag_processor:
    tags:
      mytag:
      myothertag:
      tagwithvalue: a-value
```

Finally there are a few things you can configure that you are unlikely to need.

You can change the name of the logging channel from the default `app_event`:

```yaml
linkorb_app_event:
  channel_name: "my_channel_name"
```

Remember to use this channel name instead of `app_event` in the Monolog config
files.

You can also change the name of the logging handler from the default
`app_events`:

```yaml
linkorb_app_event:
  handler_name: "my_handler_name"
```

Remember to use this handler name instead of `app_events` in the Monolog config
files.

Happy Application Event Logging!
