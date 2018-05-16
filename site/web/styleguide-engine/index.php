<?php
require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;

/**
 * Styleguide Parser
 */
function styleguideIterator($directory = null, $files = array())
{
    // Check if we're dealing with a symlink
    if (is_link($directory)) {
        // if we are then return the path of the linked directory
        $directory = readlink($directory);
    }

    // Get directory contents
    $directoryContents = glob($directory.DIRECTORY_SEPARATOR.'*');

    foreach ($directoryContents as $directoryItem) {
        if (is_file($directoryItem)) {
            // Make sure we're dealing with a blade template
            if (strpos($directoryItem, '.blade')) {
                // Get the file's name
                $name = pathinfo(explode('.blade', $directoryItem)[0])['basename'];

                $list = array($name);

                $files = array_merge($files, $list);

                usort($files, function($current, $next) {
                    return strcmp($current, $next) > 0;
                });
            }
        } elseif (is_dir($directoryItem)) {
            $itemName = pathinfo($directoryItem)['basename'];
            $list = array($itemName => styleguideIterator($directoryItem));

            if(!empty($files))
                $files = array_merge_recursive($files, $list);
            else {
                $files = $list;
            }
        }
    }

    return $files;
}

/**
 * Illuminate/view
 *
 * Requires: illuminate/filesystem
 *
 * @source https://github.com/illuminate/view
 */
$config = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$app = new \Slim\App($config);

// Entry point into the application.
// All requests to styleguide.roots-styleguide.test/ come into this route
// e.g. /components/button --> { section: components, component: button }
$app->get('/[{section}/{component}]', function ($request, $response, $args) {
    // Configuration
    $templateExt = '.blade';
    $styleguideDir = '/srv/www/roots-styleguide.com/current/web/app/themes/roots-styleguide/styleguide';
    $pathsToTemplates = [$styleguideDir, __DIR__];
    $pathToCompiledTemplates = __DIR__ . '/cache';

    // Dependencies
    $filesystem = new Filesystem;
    $eventDispatcher = new Dispatcher(new Container);

    // Create View Factory capable of rendering PHP and Blade templates
    $viewResolver = new EngineResolver;
    $bladeCompiler = new BladeCompiler($filesystem, $pathToCompiledTemplates);

    $viewResolver->register('blade', function () use ($bladeCompiler, $filesystem) {
        return new CompilerEngine($bladeCompiler, $filesystem);
    });

    $viewFinder = new FileViewFinder($filesystem, $pathsToTemplates);
    $viewFactory = new Factory($viewResolver, $viewFinder, $eventDispatcher);

    // Contruct all the styleguide data
    $data = [
        'asset_path' => '//roots-styleguide.test/app/themes/roots-styleguide/dist', // This should be dynamic
        'current' => ($args && $args['component']) ? $args['component'] : '',
        'nav' => [],
        'page' => []
    ];

    // Build the nav from contents of $styleguideDir
    $data['nav'] = styleguideIterator($styleguideDir);

    if ($args) {
        $section = $args['section'];
        $component = $args['component'];

        // Section and component should map to directories in app/themes/.../styleguide
        $componentPath = $styleguideDir.'/'.$section.'/'.$component;

        // Grab all the files in the directory infered from the url
        $files = glob($componentPath.'/*');

        // Get the content file based on the name of the folder
        $contentFile = array_values(array_filter($files, function($file) {
            return strpos($file, '.content.php');
        }))[0];

        include $contentFile;

        // Fetch the top level component based on the url
        $variants = $data['nav'][$section][$component];

        foreach ($variants as $variant) {
            // Create the partial reference path for blade. i.e. 'components.button.large'
            $template = join([$section, $component, $variant], '.');

            // Pull some data to pass to the partial
            $data['pageTitle'] = ucfirst($component);
            $data['page'][$variant]['title'] = $variant;

            // Render the blade partial into the data object for display as markup
            $data['page'][$variant]['markup'] = $viewFactory->make($template, $content[$variant])->render();
        }
    } else {
        // Homepage data
        $data['pageTitle'] = 'Styleguide';
    }

     // ???
    usort($data['page'], function($current, $next) {
        return strcmp($current['title'], $next['title']) > 0;
    });

    // Render the partial with its data
    echo $viewFactory->make('index', $data)->render();
});

$app->run();
