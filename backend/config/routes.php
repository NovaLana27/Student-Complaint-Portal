<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
    
        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });
        $routes->scope('/api', function (RouteBuilder $builder): void {
        // Auth and form data
        $builder->post('/auth/register', ['controller' => 'Auth', 'action' => 'register']);
        $builder->post('/auth/login', ['controller' => 'Auth', 'action' => 'login']);
        $builder->get('/auth/me', ['controller' => 'Auth', 'action' => 'me']);
        $builder->get('/meta/options', ['controller' => 'Meta', 'action' => 'options']);

        // Complaints (the "track" route must come BEFORE the {id} routes)
        $builder->get('/complaints/track/{ref}', ['controller' => 'Complaints', 'action' => 'track'])
            ->setPass(['ref']);
        $builder->post('/complaints', ['controller' => 'Complaints', 'action' => 'add']);
        $builder->get('/complaints', ['controller' => 'Complaints', 'action' => 'index']);
        $builder->get('/complaints/{id}', ['controller' => 'Complaints', 'action' => 'view'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);
        $builder->patch('/complaints/{id}/status', ['controller' => 'Complaints', 'action' => 'updateStatus'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);
        $builder->patch('/complaints/{id}/assign', ['controller' => 'Complaints', 'action' => 'assign'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);
        $builder->post('/complaints/{id}/responses', ['controller' => 'Complaints', 'action' => 'respond'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);
        $builder->post('/complaints/{id}/reopen', ['controller' => 'Complaints', 'action' => 'reopen'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);

        // Admin: users
        $builder->get('/users', ['controller' => 'Users', 'action' => 'index']);
        $builder->post('/users', ['controller' => 'Users', 'action' => 'add']);
        $builder->patch('/users/{id}', ['controller' => 'Users', 'action' => 'update'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);

        // Notifications
        $builder->get('/notifications', ['controller' => 'Notifications', 'action' => 'index']);
        $builder->patch('/notifications/{id}/read', ['controller' => 'Notifications', 'action' => 'markRead'])
            ->setPatterns(['id' => '[0-9]+'])->setPass(['id']);

        // Reports
        $builder->get('/reports/summary', ['controller' => 'Reports', 'action' => 'summary']);
        $builder->get('/reports/resolution-time', ['controller' => 'Reports', 'action' => 'resolutionTime']);
        $builder->get('/reports/export', ['controller' => 'Reports', 'action' => 'export']);
    });
    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
