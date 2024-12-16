<?php

namespace Ofaws\Favorite;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class Favorite
{
    /**
     * Indicates if Prompt Center routes will be registered.
     */
    public static bool $registersRoutes = true;

    /**
     * Configure Prompt Center to not register its routes.
     */
    public static function ignoreRoutes(): static
    {
        static::$registersRoutes = false;

        return new static;
    }

    public static function constrainedAssets(): array
    {
        $result = [];

        foreach (config('favorite.assets') as $class => $columns) {
            $result[$class] = fn (Builder $query) => $query->select($columns);
        }

        return $result;
    }

    public static function allowedAssetsRule(): string
    {
        return 'in:'.implode(',', array_keys(config('favorite.morph_map')));
    }

    public static function assetTableNameRule(string $asset): string
    {
        throw_unless(isset(config('favorite.morph_map')[$asset]), ValidationException::withMessages(['asset_type' => 'Non existent asset type']));

        return 'exists:'.config('favorite.morph_map')[$asset].',id';
    }
}
