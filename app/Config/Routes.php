<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomeController::index');

$routes->get('auth', 'AuthController::index');
$routes->get('auth/(:segment)', 'AuthController::index/$1');
$routes->match(['get', 'post'], 'auth/(:segment)/(:segment)', 'AuthController::index/$1/$2');
$routes->match(['get', 'post'], 'auth/forgot_password', 'AuthController::forgotPassword');
$routes->get('auth/logout', 'AuthController::logout');

$routes->group('student', ['filter' => 'student'], function($routes) {
    $routes->get('/', 'StudentController::dashboard');
    $routes->get('dashboard', 'StudentController::dashboard');
    $routes->get('classes', 'StudentController::classes');
    $routes->get('classes/(:segment)/details', 'StudentController::classDetails/$1');
    $routes->get('classes/(:segment)/sessions', 'StudentController::classSessions/$1');
    $routes->get('classes/(:segment)/leave_requests', 'StudentController::classLeaveRequests/$1');
    $routes->get('classes/(:segment)/attendance', 'StudentController::classAttendance/$1');
    $routes->get('attendance', 'StudentController::attendance');
    $routes->get('notifications', 'StudentController::notifications');
    $routes->get('profile', 'StudentController::profile');
});



