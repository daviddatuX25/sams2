<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Validation\Exceptions\ValidationException;
use CodeIgniter\Database\Exceptions\DatabaseException;

class BaseModel extends Model
{
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    protected $useSoftDeletes = true;

    /**
     * Insert data into the database with validation.
     *
     * @param mixed $data The data to insert (array or object)
     * @param bool $returnID Whether to return the insert ID
     * @return mixed
     * @throws ValidationException
     */
    public function insert($data = null, bool $returnID = true)
    {
        try {
            // Validate data if provided
            if ($data !== null && !$this->validate($data)) {
                throw new ValidationException(implode(', ', $this->errors()));
            }

            // Attempt insert
            return parent::insert($data, $returnID);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * Update data in the database with validation.
     *
     * @param mixed $id The ID(s) to update
     * @param mixed $data The data to update
     * @return bool
     * @throws ValidationException
     */
    public function update($id = null, $data = null): bool
    {
        try {
            // Validate data if provided
            if ($data !== null && !$this->validate($data)) {
                throw new ValidationException(implode(', ', $this->errors()));
            }

            return parent::update($id, $data);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * Delete data from the database.
     *
     * @param mixed $id The ID(s) to delete
     * @param bool $purge Whether to perform a hard delete
     * @return bool
     */
    public function delete($id = null, bool $purge = false): bool
    {
        try {
            return parent::delete($id, $purge);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * Find a record by ID.
     *
     * @param mixed $id The ID(s) to find
     * @return mixed
     */
    public function find($id = null): mixed
    {
        try {
            return parent::find($id);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * Find all records with optional limit and offset.
     *
     * @param ?int $limit Number of records to retrieve
     * @param int $offset Starting point for retrieval
     * @return array
     */
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        try {
            return parent::findAll($limit, $offset);
        } catch (DatabaseException $e) {
            throw $e;
        }
    }

    /**
     * Find the first record.
     *
     * @return mixed
     */
    public function first(): mixed
    {
        try {
            return parent::first();
        } catch (DatabaseException $e) {
            throw $e;
        }
    }
}