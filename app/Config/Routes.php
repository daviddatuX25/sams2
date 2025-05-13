<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomeController::index');

$routes->get('auth', 'AuthController::index');
$routes->get('auth/logout', 'AuthController::logout');
$routes->match(['GET', 'POST'], 'auth/forgot_password', 'AuthController::forgotPassword');
$routes->get('auth/(:segment)', 'AuthController::index/$1');
$routes->match(['GET', 'POST'], 'auth/(:segment)/(:segment)', 'AuthController::index/$1/$2');

$routes->group('student', ['filter' => 'student'], function($routes) {
    $routes->get('/', 'StudentController::index');
    $routes->get('dashboard', 'StudentController::index');
    $routes->get('classes', 'StudentController::classes');
    $routes->get('classes/(:segment)', 'StudentController::classDetail/$1');
    $routes->get('attendance', 'StudentController::attendance');
    $routes->get('schedule', 'StudentController::schedule');
    $routes->get('notifications', 'StudentController::notifications');
    $routes->match(['GET', 'POST'], 'profile', 'StudentController::profile');
    $routes->post('leave_requests', 'StudentController::leaveRequests');
});

// Teacher Routes
$routes->group('teacher', ['filter' => 'teacher'], function ($routes) {
    $routes->get('/', 'TeacherController::index');
    $routes->get('classes', 'TeacherController::classes');
    $routes->match(['GET', 'POST'], 'classes/(:num)', 'TeacherController::classDetail/$1');
    $routes->match(['GET', 'POST'], 'leave_requests', 'TeacherController::leaveRequests');
    $routes->get('schedule', 'TeacherController::schedule');
    $routes->get('reports', 'TeacherController::reports');
    $routes->match(['GET', 'POST'], 'profile', 'TeacherController::profile');
});



