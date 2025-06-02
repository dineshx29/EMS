<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/test', 'Test::index');
$routes->get('/api/health', 'Test::health');

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    
    // Auth Routes (No authentication required)
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/logout', 'AuthController::logout');
    $routes->post('auth/refresh', 'AuthController::refresh');
    
    // Protected Auth Routes
    $routes->get('auth/profile', 'AuthController::profile');
    $routes->put('auth/profile', 'AuthController::updateProfile');
    $routes->post('auth/change-password', 'AuthController::changePassword');
    
    // Employee Routes
    $routes->group('employees', function($routes) {
        $routes->get('/', 'EmployeeController::index');
        $routes->get('show/(:num)', 'EmployeeController::show/$1');
        $routes->post('create', 'EmployeeController::create');
        $routes->put('update/(:num)', 'EmployeeController::update/$1');
        $routes->delete('delete/(:num)', 'EmployeeController::delete/$1');
        $routes->post('toggle-status/(:num)', 'EmployeeController::toggleStatus/$1');
        $routes->get('departments', 'EmployeeController::departments');
        $routes->get('department-stats', 'EmployeeController::departmentStats');
        $routes->get('stats', 'EmployeeController::stats');
        $routes->get('managers', 'EmployeeController::managers');
        $routes->get('by-manager/(:num)', 'EmployeeController::getByManager/$1');
        $routes->get('by-department/(:segment)', 'EmployeeController::getByDepartment/$1');
        $routes->get('export', 'EmployeeController::export');
    });
    
    // User Routes
    $routes->group('users', function($routes) {
        $routes->get('/', 'UserController::index');
        $routes->get('show/(:num)', 'UserController::show/$1');
        $routes->post('create', 'UserController::create');
        $routes->put('update/(:num)', 'UserController::update/$1');
        $routes->delete('delete/(:num)', 'UserController::delete/$1');
        $routes->post('toggle-status/(:num)', 'UserController::toggleStatus/$1');
        $routes->post('reset-password/(:num)', 'UserController::resetPassword/$1');
        $routes->get('stats', 'UserController::stats');
        $routes->get('by-role/(:num)', 'UserController::getByRole/$1');
        $routes->post('change-role/(:num)', 'UserController::changeRole/$1');
        $routes->get('active', 'UserController::getActiveUsers');
    });
    
    // Role Routes
    $routes->group('roles', function($routes) {
        $routes->get('/', 'RoleController::index');
        $routes->get('show/(:num)', 'RoleController::show/$1');
        $routes->post('create', 'RoleController::create');
        $routes->put('update/(:num)', 'RoleController::update/$1');
        $routes->delete('delete/(:num)', 'RoleController::delete/$1');
        $routes->get('all', 'RoleController::getAllRoles');
        $routes->get('permissions', 'RoleController::getPermissions');
        $routes->get('check-permissions/(:num)', 'RoleController::checkRolePermissions/$1');
        $routes->get('stats', 'RoleController::stats');
        $routes->post('assign-permissions/(:num)', 'RoleController::assignPermissions/$1');
        $routes->get('users/(:num)', 'RoleController::getRoleUsers/$1');
    });
    
});
