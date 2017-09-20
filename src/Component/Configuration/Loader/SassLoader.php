<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types = 1);
namespace Hostnet\Component\Webpack\Configuration\Loader;

use Hostnet\Component\Webpack\Configuration\CodeBlock;
use Hostnet\Component\Webpack\Configuration\ConfigExtensionInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class SassLoader implements LoaderInterface, ConfigExtensionInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public static function applyConfiguration(NodeBuilder $node_builder)
    {
        $node_builder
            ->arrayNode('sass')
                ->canBeDisabled()
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('all_chunks')->defaultTrue()->end()
                    ->scalarNode('filename')->defaultNull()->end()
                    ->arrayNode('include_paths')
                        ->defaultValue(array())
                        ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    /** {@inheritdoc} */
    public function getCodeBlocks()
    {
        $config = $this->config['loaders']['sass'];

        $block = new CodeBlock;

        if (! $config['enabled']) {
            return [$block];
        }

        if (empty($config['filename'])) {
            // If the filename is not set, apply inline style tags.
            $jsonData = array(
                array(
                    'loader' => 'style-loader'
                ),
                array(
                    'loader' => 'css-loader'
                ),
                array(
                    'loader' => 'sass-loader',
                    'options' => array(
                        'includePaths' => $config['include_paths']
                    )
                )
            );
            $json = sprintf('{ test: %s, use: %s }', '/\.scss$/', json_encode($jsonData));

            $block->set(CodeBlock::LOADER, $json);
            return [$block];
        }


        // If a filename is set, apply the ExtractTextPlugin
        $fn = 'fn_extract_text_plugin_sass';
        $jsonData = array(
            array(
                'loader' => 'css-loader'
            ),
            array(
                'loader' => 'sass-loader',
                'options' => array(
                    'includePaths' => $config['include_paths']
                )
            )
        );
        $json = sprintf('{ test: %s, use: %s }', '/\.scss$/', $fn . '.extract(' . json_encode($jsonData) . ')');
        $code_blocks = [(new CodeBlock())
            ->set(CodeBlock::HEADER, 'var ' . $fn . ' = require("extract-text-webpack-plugin");')
            ->set(CodeBlock::LOADER, $json)
            ->set(CodeBlock::PLUGIN, 'new ' . $fn . '("' . $config['filename'] . '", {'. (
                $config['all_chunks'] ? 'allChunks: true' : ''
            ) . '})')
        ];

        // If a common_filename is set, apply the CommonsChunkPlugin.
//         if (! empty($this->config['output']['common_id'])) {
//             $code_blocks[] = (new CodeBlock())
//                 ->set(CodeBlock::PLUGIN, sprintf(
//                     'new %s({name: \'%s\', filename: \'%s\'})',
//                     'webpack.optimize.CommonsChunkPlugin',
//                     $this->config['output']['common_id'],
//                     $this->config['output']['common_id'] . '.js'
//                 ));
//         }

        return $code_blocks;
    }
}
