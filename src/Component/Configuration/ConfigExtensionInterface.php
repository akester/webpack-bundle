<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * @author Harold Iedema <hiedema@hostnet.nl>
 */
interface ConfigExtensionInterface extends CodeBlockProviderInterface
{
    /**
     * Applies plugin-specific configuration to the TreeBuilder used to parse configuration from the application. This
     * method is declared static because we can't instantiate anything from the container at this point.
     *
     * See http://symfony.com/doc/current/components/config/definition.html for more information regarding creating
     * configuration.
     *
     * @param  NodeBuilder $node_builder
     * @return string
     */
    public static function applyConfiguration(NodeBuilder $node_builder);
}
