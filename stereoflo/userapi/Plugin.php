<?php namespace Stereoflo\Userapi;

use Backend;
use System\Classes\PluginBase;

/**
 * userapi Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'User Api',
            'description' => 'Extend Rainlab User plugin',
            'author'      => 'StereoFlo',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

//        return [
//            'Stereoflo\Userapi\Components\MyComponent' => 'myComponent',
//        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

//        return [
//            'stereoflo.userapi.some_permission' => [
//                'tab' => 'userapi',
//                'label' => 'Some permission'
//            ],
//        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

//        return [
//            'userapi' => [
//                'label'       => 'userapi',
//                'url'         => Backend::url('stereoflo/userapi/mycontroller'),
//                'icon'        => 'icon-leaf',
//                'permissions' => ['stereoflo.userapi.*'],
//                'order'       => 500,
//            ],
//        ];
    }
}
