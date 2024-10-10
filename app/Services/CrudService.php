<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

class CrudService
{
    /**
     * Generic function to read a record by ID.
     *
     * @param Model $model
     * @param int $id
     * @return Model|null
     */
    public function read(Model $model, int $id): ?Model
    {
        return $model->find($id);
    }

    /**
     * Generic function to update a record.
     *
     * @param Model $model
     * @param int $id
     * @param array $data
     * @return Model|null
     */
    public function update(Model $model, int $id, array $data): ?Model
    {
        $record = $model->find($id);

        if ($record) {
            $record->update($data);
            return $record;
        }

        return null;
    }

    /**
     * Generic function to delete a record.
     *
     * @param Model $model
     * @param int $id
     * @return bool
     */
    public function delete(Model $model, int $id): bool
    {
        $record = $model->find($id);

        if ($record) {
            return $record->delete();
        }

        return false;
    }
}
