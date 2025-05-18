<?php
namespace App\Traits;

use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\BusinessRuleException;

trait ExceptionHandlingTrait
{
    /**
     * Wraps a controller action to handle exceptions and return standardized responses.
     *
     * @param callable $action The controller action to execute
     * @return mixed Response (JSON, redirect, or view)
     */
    protected function handleAction(callable $action)
    {
        try {
            return $action();
        } catch (ValidationException $e) {
            return $this->handleValidationException($e);
        } catch (NotFoundException $e) {
            return $this->handleNotFoundException($e);
        } catch (UnauthorizedException $e) {
            return $this->handleUnauthorizedException($e);
        } catch (BusinessRuleException $e) {
            return $this->handleBusinessRuleException($e);
        } catch (DatabaseException $e) {
            return $this->handleDatabaseException($e);
        } catch (\Exception $e) {
            return $this->handleGenericException($e);
        }
    }

    private function handleValidationException(ValidationException $e)
    {
        $errors = explode(', ', $e->getMessage());
        if ($this->request->isAJAX() || $this->isApiRequest()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ])->setStatusCode(400);
        }

        session()->setFlashdata('error', implode(', ', $errors));
        return redirect()->back()->withInput();
    }

    private function handleNotFoundException(NotFoundException $e)
    {
        if ($this->request->isAJAX() || $this->isApiRequest()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage() ?: 'Resource not found'
            ])->setStatusCode(404);
        }

        session()->setFlashdata('error', $e->getMessage() ?: 'Resource not found');
        return redirect()->back();
    }

    private function handleUnauthorizedException(UnauthorizedException $e)
    {
        if ($this->request->isAJAX() || $this->isApiRequest()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unauthorized access'
            ])->setStatusCode(403);
        }

        session()->setFlashdata('error', $e->getMessage() ?: 'Unauthorized access');
        return redirect()->to('/auth');
    }

    private function handleBusinessRuleException(BusinessRuleException $e)
    {
        if ($this->request->isAJAX() || $this->isApiRequest()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage() ?: 'Business rule violation'
            ])->setStatusCode(422);
        }

        session()->setFlashdata('error', $e->getMessage() ?: 'Business rule violation');
        return redirect()->back()->withInput();
    }

    private function handleDatabaseException(DatabaseException $e)
    {
        if (ENVIRONMENT === 'production') {
            log_message('critical', 'Database error: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return $this->handleProductionError();
        }

        throw new \Exception('Database error: ' . $e->getMessage(), 500, $e);
    }

    private function handleGenericException(\Exception $e)
    {
        if (ENVIRONMENT === 'production') {
            log_message('critical', 'Unexpected error: ' . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return $this->handleProductionError();
        }

        throw $e;
    }

    private function handleProductionError()
    {
        if ($this->request->isAJAX() || $this->isApiRequest()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.'
            ])->setStatusCode(500);
        }

        return view('errors/html/production', ['message' => 'Something went wrong.']);
    }

    private function isApiRequest(): bool
    {
        return $this->request->hasHeader('X-API-KEY') || strpos($this->request->getPath(), 'api') === 0;
    }
}