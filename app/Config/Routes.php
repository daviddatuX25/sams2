<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home Route
$routes->get('/', 'HomeController::index');

// Authentication Routes
$routes->get('auth', 'AuthController::index');
$routes->match(['GET', 'POST'], 'auth/forgot_password', 'AuthController::forgotPassword');
$routes->get('auth/(:segment)', 'AuthController::index/$1');
$routes->match(['GET', 'POST'], 'auth/(:segment)/(:segment)', 'AuthController::index/$1/$2');

// Shared Role Routes
$routes->group('user', ['filter' => 'auth'], function ($routes) {
    $routes->match(['GET', 'POST'], 'profile', 'UserController::profile');
    $routes->match(['GET', 'POST'], 'notification', 'UserController::notification');
    $routes->get('logout', 'UserController::logout');
});

// Student Routes
$routes->group('student', ['filter' => 'student'], function ($routes) {
    $routes->get('/', 'StudentController::index');
    $routes->get('dashboard', 'StudentController::index');
    $routes->get('classes', 'StudentController::classes');
    $routes->match(['POST', 'GET'], 'classes/(:num)', 'StudentController::classDetail/$1');
    $routes->get('attendance', 'StudentController::attendance');
    $routes->get('schedule', 'StudentController::schedule');
    $routes->match(['GET', 'POST'], 'leave_requests', 'StudentController::leaveRequests');
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
    $routes->get('logout', 'TeacherController::logout');
});

// Admin Routes
$routes->group('admin', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'AdminController::users'); // Default to users view
    $routes->match(['GET', 'POST'], 'users', 'AdminController::users');
    $routes->match(['GET', 'POST'], 'subjects', 'AdminController::subjects');
    $routes->match(['GET', 'POST'], 'classes', 'AdminController::classes');
    $routes->match(['GET', 'POST'], 'enrollment-terms', 'AdminController::enrollmentTerms');
    $routes->match(['GET', 'POST'], 'student-assignments', 'AdminController::studentAssignments');
    $routes->match(['GET', 'POST'], 'teacher-assignments', 'AdminController::teacherAssignments');
    $routes->match(['GET', 'POST'], 'rooms', 'AdminController::rooms');
    $routes->match(['GET', 'POST'], 'trackers', 'AdminController::trackers');
    $routes->match(['GET', 'POST'], 'notifications', 'AdminController::notifications');
    $routes->match(['GET', 'POST'], 'class-sessions', 'AdminController::classSessions');
    $routes->match(['GET', 'POST'], 'class-session-settings', 'AdminController::classSessionSettings');
    $routes->match(['GET', 'POST'], 'attendance', 'AdminController::attendance');
    $routes->match(['GET', 'POST'], 'tracker-logs', 'AdminController::trackerLogs');
});