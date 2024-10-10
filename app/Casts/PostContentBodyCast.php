<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PostContentBodyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($model->type === PostContentEnum::LIST->value) {
                return explode('|$*$|', $value);
        }

        if ($model->type === PostContentEnum::SUB_LIST->value) {
            return explode('|$*$|', $value);
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($model->type === PostContentEnum::LIST->value) {
            return implode('|$*$|', $value);
        }

        if ($model->type === PostContentEnum::SUB_LIST->value) {
            return implode('|$*$|', $value);
        }

        return $value;
    }
}
