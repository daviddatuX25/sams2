<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Psr\Log\LoggerInterface;
use App\Traits\ExceptionHandlingTrait;

class BaseController extends Controller
{
    use ExceptionHandlingTrait;
    protected $request;
    protected $helpers = ['response', 'form', 'url', 'html'];
    protected $superadmin = 4;

    public function initController(RequestInterface $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

}