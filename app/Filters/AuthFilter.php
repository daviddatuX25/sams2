<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Exceptions\UnauthorizedException;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
  
        $role = session()->get('role');
        if (!in_array($role, ['student', 'teacher', 'admin'])) {
            if ( $request->isAJAX()) {
                throw new UnauthorizedException('Please log in to access this resource.', 401);
            }
            return redirect()->to('/login')->with('error', 'Please log in.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }
}