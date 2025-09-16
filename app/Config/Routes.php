<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index', ['filter' => 'auth']);
$routes->get('/register', 'Auth::showRegister');
$routes->post('/register', 'Auth::register');
$routes->get('/login', 'Auth::showLogin');
$routes->post('/login', 'Auth::login');
$routes->post('/logout', 'Auth::logout');
