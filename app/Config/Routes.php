<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */
$routes->get('test', 'Home::test');

$routes->get('/', 'Home::index');
$routes->get('/book', 'Home::book');
$routes->get('books/request', 'Books\BooksController::requestInfo');

service('auth')->routes($routes);

$routes->group('admin', ['filter' => 'session'], static function (RouteCollection $routes) {

    // Dashboard & resource lain
    $routes->get('/', 'Dashboard\DashboardController');
    $routes->get('dashboard', 'Dashboard\DashboardController::dashboard');

    $routes->resource('members', ['controller' => 'Members\MembersController']);

    $routes->get('books/exportPdf', 'Books\BooksController::exportPdf');
    $routes->get('books/exportExcel', 'Books\BooksController::exportExcel');
    $routes->resource('books', ['controller' => 'Books\BooksController']);

    $routes->resource('categories', ['controller' => 'Books\CategoriesController']);
    $routes->resource('racks', ['controller' => 'Books\RacksController']);

    // Loans routes
    $routes->get('loans/new/members/search', 'Loans\LoansController::searchMember');
    $routes->get('loans/new/books/search', 'Loans\LoansController::searchBook');
    $routes->post('loans/new', 'Loans\LoansController::new');

    $routes->get('loans/exportPdf', 'Loans\LoansController::exportPdf');
    $routes->get('loans/exportExcel', 'Loans\LoansController::exportExcel');
    $routes->resource('loans', ['controller' => 'Loans\LoansController']);

    // ===== Returns routes =====
    // Export harus sebelum resource agar tidak tertangkap show($uid)
    $routes->get('returns/exportPdf', 'Loans\ReturnsController::exportPdf');
    $routes->get('returns/exportExcel', 'Loans\ReturnsController::exportExcel');

    $routes->get('returns/new/search', 'Loans\ReturnsController::searchLoan');
    $routes->resource('returns', ['controller' => 'Loans\ReturnsController']);

    // Fines routes (taruh sebelum resource!)
    $routes->get('fines/returns/search', 'Loans\FinesController::searchReturn');
    $routes->get('fines/pay/(:any)', 'Loans\FinesController::pay/$1');

    // Export PDF & Excel (harus sebelum resource)
    $routes->get('fines/exportPdf', 'Loans\FinesController::exportPdf');
    $routes->get('fines/exportExcel/(:any)', 'Loans\FinesController::exportExcel/$1');
    $routes->get('fines/exportExcelPaid', 'Loans\FinesController::exportExcelPaid');
    $routes->get('fines/exportExcelUnpaid', 'Loans\FinesController::exportExcelUnpaid');

    $routes->resource('fines/settings', ['controller' => 'Loans\FineSettingsController', 'filter' => 'group:superadmin']);
    $routes->resource('fines', ['controller' => 'Loans\FinesController']);

    // Users routes
    $routes->group('users', ['filter' => 'group:superadmin'], static function (RouteCollection $routes) {
        $routes->get('new', 'Users\RegisterController::index');
        $routes->post('', 'Users\RegisterController::registerAction');
    });
    $routes->resource('users', ['controller' => 'Users\UsersController', 'filter' => 'group:superadmin']);
});


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
