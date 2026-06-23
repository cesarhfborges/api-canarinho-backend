<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Config extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /**
     * Get a configuration value with caching.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("config_{$key}", function () use ($key, $default) {
            $config = self::find($key);

            if ($config) {
                return json_decode($config->value, true);
            }

            return $default;
        });
    }

    /**
     * Set a configuration value and clear its cache.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setValue(string $key, mixed $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value)]
        );

        Cache::forget("config_{$key}");
    }
}
