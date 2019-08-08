<?php

namespace LinkORB\AppEventBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('linkorb_app_event');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('tag_processor')
                    ->canBeDisabled()
                    ->children()
                        ->arrayNode('tags')
                            ->useAttributeAsKey('name')
                            ->scalarPrototype()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('token_processor')
                    ->canBeDisabled()
                ->end()
                ->scalarNode('handler_name')
                    ->defaultValue(LinkORBAppEventExtension::DEFAULT_HANDLER)
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('channel_name')
                    ->defaultValue(LinkORBAppEventExtension::DEFAULT_CHANNEL)
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
