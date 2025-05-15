<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home Route
$routes->get('/', 'HomeController::index');

// Authentication Routes
$routes->get('auth', 'AuthController::index');
$routes->get('auth/(:segment)', 'AuthController::index/$1');
$routes->match(['GET', 'POST'], 'auth/(:segment)/(:segment)', 'AuthController::index/$1/$2');
$routes->match(['GET', 'POST'], 'auth/forgot_password', 'AuthController::forgotPassword');
$routes->match(['GET', 'POST'], 'auth/change_password', 'AuthController::changePassword');
$routes->get('auth/logout', 'AuthController::logout');

// Student Routes
$routes->group('student', ['filter' => 'student'], function ($routes) {
    $routes->get('/', 'StudentController::index');
    $routes->get('dashboard', 'StudentController::index');
    $routes->get('classes', 'StudentController::classes');
    $routes->get('classes/(:num)', 'StudentController::classDetail/$1');
    $routes->get('attendance', 'StudentController::attendance');
    $routes->get('schedule', 'StudentController::schedule');
    $routes->get('notifications', 'StudentController::notifications');
    $routes->match(['GET', 'POST'], 'leave_requests', 'StudentController::leaveRequests');
    $routes->match(['GET', 'POST'], 'profile', 'StudentController::profile');
});

// Teacher Routes
$routes->group('teacher', ['filter' => 'teacher'], function ($routes) {
    $routes->get('/', 'TeacherController::index');
    $routes->get('dashboard', 'TeacherController::index');
    $routes->get('classes', 'TeacherController::classes');
    $routes->match(['GET', 'POST'], 'classes/(:num)', 'TeacherController::classDetail/$1');
    $routes->match(['GET', 'POST'], 'leave_requests', 'TeacherController::leaveRequests');
    $routes->get('schedule', 'TeacherController::schedule');
    $routes->get('reports', 'TeacherController::reports');
    $routes->match(['GET', 'POST'], 'profile', 'TeacherController::profile');
});

// Admin Routes (Placeholder)
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('dashboard', 'AdminController::index');
    $routes->get('users', 'AdminController::users');
    $routes->match(['GET', 'POST'], 'users/(:num)', 'AdminController::userDetail/$1');
    $routes->get('classes', 'AdminController::classes');
    $routes->match(['GET', 'POST'], 'classes/(:num)', 'AdminController::classDetail/$1');
    $routes->get('reports', 'AdminController::reports');
    $routes->match(['GET', 'POST'], 'profile', 'AdminController::profile');
});