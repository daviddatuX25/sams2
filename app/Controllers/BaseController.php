<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'html'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    public function renderPage($pageTitle, $page)
    {
        echo view('includes/header', ['pageTitle' => $pageTitle]);

        if (is_array($page)) {
            foreach ($page as $p) {
                echo $p;
            }
        } else {
            echo $page;
        }
        echo view('includes/footer');
    }

    /**
     * Returns the authenticated user's details.
     *
     * @return array|null Returns ['user_id' => int, 'role' => string] or null if not authenticated
     * @throws \Exception If authentication fails or user is not found
     */
    protected function getAuthenticatedUser()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            throw new \Exception('User not authenticated.');
        }

        // Fetch user from UserModel to ensure validity and get role
        $userModel = new \App\Models\UserModel();
        $user = $userModel->select('user_id, role')->find($userId);

        if (!$user) {
            throw new \Exception('Authenticated user not found.');
        }

        return [
            'user_id' => $user['user_id'],
            'role' => $user['role']
        ];
    }

}
