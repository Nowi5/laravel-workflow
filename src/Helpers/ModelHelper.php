<?php
namespace Workflow\Helpers;

use Illuminate\Database\Eloquent\Model;

final class ModelHelper
{
    /**
     * Get all possible attributes of a model.
     */
    public static function getAllAttributes(Model $model): array
    {
        return array_merge(
            array_keys($model->getAttributes()),
            $model->getFillable(),
            array_keys($model->getCasts())
        );
    }

    /**
     * Check if an attribute exists in a model.
     */
    public static function attributeExists(Model $model, string $attribute): bool
    {
        return in_array($attribute, self::getAllAttributes($model));
    }
}
