<?php namespace Waka\Workflow;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;
use App;
use Event;

/**
 * workflow Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Waka.Wutils',
    ];

    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'waka.workflow::lang.plugin.name',
            'description' => 'waka.workflow::lang.plugin.description',
            'author'      => 'waka',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerListColumnTypes()
    {
        return [
            'workflow' => [\Waka\Workflow\Columns\WorkflowColumn::class, 'render'],
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     */
    public function register(): void
    {
        $aliasLoader = AliasLoader::getInstance();
        $aliasLoader->alias('Workflow', \ZeroDaHero\LaravelWorkflow\Facades\WorkflowFacade::class);
        App::register(\ZeroDaHero\LaravelWorkflow\WorkflowServiceProvider::class);
        $registeredAppPathConfig = require __DIR__ . '/config/workflow.php';
        \Config::set('workflow', $registeredAppPathConfig);

        $this->registerConsoleCommand('waka.workflow', 'Waka\Workflow\Console\WorkflowCreate');
        $this->registerConsoleCommand('waka.workflowDump', 'Waka\Workflow\Console\WorkflowDump');
        /**NODS-A Supprimer ??*/ //$this->registerConsoleCommand('waka:workflowODump', 'Waka\Workflow\Console\WorkflowOnlineDump');
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot(): void
    {
        /**
         * POUR LE WORKFLOW COLUMN
         */
        /**NODS- pourquoi ce code ??? */
        Event::listen('backend.list.extendColumns', function ($widget) {
            /** @var \Backend\Widgets\Lists $widget */
            foreach ($widget->config->columns as $name => $config) {
                if (empty($config['type']) || $config['type'] !== 'workflow') {
                    continue;
                }
                // Store field config here, before that unofficial fields was removed
                \Waka\Workflow\Columns\WorkflowColumn::storeFieldConfig($name, $config);
            }
        });

    }

    /**
     * Registers any frontend components implemented in this plugin.
     */
    public function registerComponents(): array
    {
        return []; // Remove this line to activate

        return [
            'Waka\Workflow\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any backend permissions used by this plugin.
     */
    public function registerPermissions(): array
    {
        return []; // Remove this line to activate
    }

    /**
     * Registers backend navigation items for this plugin.
     */
    public function registerNavigation(): array
    {
        return []; // Remove this line to activate

        return [
            'workflow' => [
                'label'       => 'waka.workflow::lang.plugin.name',
                'url'         => Backend::url('waka/workflow/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['waka.workflow.*'],
                'order'       => 500,
            ],
        ];
    }
}
