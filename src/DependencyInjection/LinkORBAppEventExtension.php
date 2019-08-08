<?php

namespace LinkORB\AppEventBundle\DependencyInjection;

use LinkORB\AppEvent\AppEventLoggerAwareInterface;
use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\TagProcessor;
use Symfony\Bridge\Monolog\Processor\TokenProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class LinkORBAppEventExtension extends Extension implements CompilerPassInterface
{
    const DEFAULT_CHANNEL = 'app_event';
    const DEFAULT_HANDLER = 'app_events';

    private $loggerDefn;

    protected $channelName;
    protected $handlerName;
    protected $registerTagProcessor = true;
    protected $registerTokenProcessor = true;
    protected $tags = [];

    /*
     * Eschew the autogenerated alias "link_orb_app_event".
     */
    public function getAlias()
    {
        return 'linkorb_app_event';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.xml');

        // ask the container to tag services that want App Event Logger
        $container
            ->registerForAutoconfiguration(AppEventLoggerAwareInterface::class)
            ->addTag('linkorb_app_event.app_event_emitter')
        ;

        // process the bundle config
        $config = $this->processConfiguration(
            new Configuration($container->getParameter('kernel.project_dir')),
            $configs
        );
        if (false === $config['tag_processor']['enabled']) {
            $this->registerTagProcessor = false;
        } else {
            $this->tags = $config['tag_processor']['tags'];
        }
        if (false === $config['token_processor']['enabled']) {
            $this->registerTokenProcessor = false;
        }
        $this->channelName = $config['channel_name'];
        $this->handlerName = $config['handler_name'];
    }

    public function process(ContainerBuilder $container)
    {
        // Add JsonFormatter to the container
        try {
            $container->findDefinition(JsonFormatter::class);
        } catch (ServiceNotFoundException $e) {
            $container->setDefinition(JsonFormatter::class, new Definition(JsonFormatter::class));
        }

        // Inject JsonFormatter into this bundle's AppEventFormatter
        $formatterDefn = $container->getDefinition('linkorb_app_event.app_event_formatter');
        $formatterDefn->replaceArgument('$formatter', new Reference(JsonFormatter::class));

        // Inject AppEventFormatter into a handler defined in symfony's monolog bundle config (e.g. config/packages/monolog.yaml)
        try {
            $appEventsHandlerDefn = $container->findDefinition("monolog.handler.{$this->handlerName}");
        } catch (ServiceNotFoundException $e) {
            $env = $container->getParameter('kernel.environment');
            throw new \LogicException(
                "The Event Log cannot be configured because a handler named \"{$this->handlerName}\" has not been configured.  Did you forget to define a Monlog handler for the \"{$env}\" environment?",
                null,
                $e
            );
        }
        $appEventsHandlerDefn->addMethodCall('setFormatter', [new Reference('linkorb_app_event.app_event_formatter')]);

        // inject the App Event Logger into the services that want it and inject
        // the minimum log level as the default logging level
        $appEventLoggerReference = new Reference("monolog.logger.{$this->channelName}");
        // this second arg to the handler should be the integer log level
        $level = $appEventsHandlerDefn->getArgument(1);
        if (\is_int($level)) {
            foreach ($container->findTaggedServiceIds('linkorb_app_event.app_event_emitter') as $id => $tags) {
                $container
                    ->findDefinition($id)
                    ->addMethodCall('setAppEventLogger', [$appEventLoggerReference])
                    ->addMethodCall('setDefaultLogLevel', [$level])
                ;
            }
        } else {
            foreach ($container->findTaggedServiceIds('linkorb_app_event.app_event_emitter') as $id => $tags) {
                $container
                    ->findDefinition($id)
                    ->addMethodCall('setAppEventLogger', [$appEventLoggerReference])
                ;
            }
        }

        // register the token processor that adds user into to each event
        if ($this->registerTokenProcessor) {
            $tokenProcessorDefn = new Definition(TokenProcessor::class);
            $tokenProcessorDefn->setArgument('$tokenStorage', new Reference('security.token_storage'));
            $container->setDefinition(TokenProcessor::class, $tokenProcessorDefn);
            $this->getLoggerDefinition($container)
                ->addMethodCall('pushProcessor', [new Reference(TokenProcessor::class)])
            ;
        }

        // register the tag processor that adds tags to each event
        if ($this->registerTagProcessor) {
            $tagProcessorDefn = new Definition(TagProcessor::class, ['$tags' => $this->tags]);
            $container->setDefinition(TagProcessor::class, $tagProcessorDefn);
            $this->getLoggerDefinition($container)
                ->addMethodCall('pushProcessor', [new Reference(TagProcessor::class)])
            ;
        }
    }

    private function getLoggerDefinition($container)
    {
        if (null === $this->loggerDefn) {
            $this->loggerDefn = $container->findDefinition("monolog.logger.{$this->channelName}");
        }

        return $this->loggerDefn;
    }
}
