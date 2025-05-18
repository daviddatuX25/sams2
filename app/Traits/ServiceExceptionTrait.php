<?php

namespace App\Traits;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\BusinessRuleException;
use CodeIgniter\Validation\Exceptions\ValidationException;

trait ServiceExceptionTrait
{
    /**
     * Throws a NotFoundException for missing resources.
     *
     * @param string $resource The resource type (e.g., 'User', 'Class')
     * @param mixed $identifier The resource identifier
     * @throws NotFoundException
     */
    protected function throwNotFound(string $resource, $identifier): void
    {
        throw new NotFoundException("{$resource} with ID {$identifier} not found");
    }

    /**
     * Throws an UnauthorizedException for unauthorized actions.
     *
     * @param string $message Optional custom message
     * @throws UnauthorizedException
     */
    protected function throwUnauthorized(string $message = ""): void
    {
        throw new UnauthorizedException($message ?: "Unauthorized access");
    }

    /**
     * Throws a ValidationException for validation errors.
     *
     * @param string $message Error message
     * @throws ValidationException
     */
    protected function throwValidationError(string $message): void
    {
        throw new ValidationException($message);
    }

    /**
     * Throws a BusinessRuleException for business rule violations.
     *
     * @param string $message Error message
     * @throws BusinessRuleException
     */
    protected function throwBusinessRule(string $message): void
    {
        throw new BusinessRuleException($message);
    }
}