<?php

/** @var \Laravel\Lumen\Routing\Router $router */

// Health & Monitoring Routes
$router->group(['prefix' => 'api/health'], function () use ($router) {
    $router->get('/', function () {
        return response()->json(['status' => 'ok', 'message' => 'System is healthy', 'timestamp' => time()]);
    });
    
    $router->get('/database', function () {
        try {
            app('db')->connection()->getPdo();
            return response()->json(['status' => 'ok', 'message' => 'Database connection is established']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Database connection failed'], 500);
        }
    });
});

// Admin Routes
$router->group(['prefix' => 'api/admin', 'namespace' => 'Admin'], function () use ($router) {
    // Auth & Registration
    $router->post('login', 'AdminAuthController@login');
    $router->post('register', 'AdminAuthController@register');
    $router->post('logout', 'AdminAuthController@logout');
    
    // Protected Admin Routes
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('me', 'AdminAuthController@me');
        $router->put('me', 'AdminAuthController@updateMe');
        $router->put('me/password', 'AdminAuthController@updateMyPassword');
        
        // Dashboard Metrics
        $router->get('dashboard/metrics', 'DashboardController@metrics');
        
        // Gestão de Usuários (Apenas Admins)
        $router->get('users', 'UserController@index');
        $router->post('users', 'UserController@store');
        $router->get('users/{id}', 'UserController@show');
        $router->put('users/{id}', 'UserController@update');
        $router->delete('users/{id}', 'UserController@destroy');
        $router->put('users/{id}/password', 'UserController@updatePassword');
        
        // Projetos
        $router->get('projects', 'ProjectController@index');
        $router->post('projects', 'ProjectController@store');
        $router->get('projects/{id}', 'ProjectController@show');
        $router->put('projects/{id}', 'ProjectController@update');
        $router->delete('projects/{id}', 'ProjectController@destroy');

        // Endpoints
        $router->get('projects/{projectId}/endpoints', 'EndpointController@index');
        $router->post('projects/{projectId}/endpoints', 'EndpointController@store');
        $router->get('projects/{projectId}/endpoints/{id}', 'EndpointController@show');
        $router->put('endpoints/{id}', 'EndpointController@update');
        $router->delete('endpoints/{id}', 'EndpointController@destroy');
        $router->post('projects/{projectId}/endpoints/{id}/generate', 'MockGeneratorController@generate');

        // Tokens do Projeto
        $router->get('projects/{projectId}/tokens', 'ProjectTokenController@index');
        $router->post('projects/{projectId}/tokens', 'ProjectTokenController@store');
        $router->delete('tokens/{id}', 'ProjectTokenController@destroy');

        // Dynamic Rules
        $router->get('endpoints/{endpointId}/rules', 'DynamicRuleController@index');
        $router->post('endpoints/{endpointId}/rules', 'DynamicRuleController@store');
        $router->put('rules/{id}', 'DynamicRuleController@update');
        $router->delete('rules/{id}', 'DynamicRuleController@destroy');
    });
});

// Dynamic Mock API Routes
$router->group(['prefix' => 'api/{username}/{projectSlug}', 'middleware' => ['project_token', 'track_metrics']], function () use ($router) {
    $router->get('{path:.*}', 'DynamicApiController@handleRequest');
    $router->post('{path:.*}', 'DynamicApiController@handleRequest');
    $router->put('{path:.*}', 'DynamicApiController@handleRequest');
    $router->patch('{path:.*}', 'DynamicApiController@handleRequest');
    $router->delete('{path:.*}', 'DynamicApiController@handleRequest');
});
