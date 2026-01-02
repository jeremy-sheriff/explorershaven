<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        $valueToStore = is_array($value) ? json_encode($value) : $value;

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valueToStore,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get current academic year
     */
    public static function currentAcademicYear(): string
    {
        return static::get('current_academic_year', date('Y'));
    }

    /**
     * Get max grade level
     */
    public static function maxGradeLevel(): int
    {
        return static::get('max_grade_level', 12);
    }

    /**
     * Check if auto-graduate is enabled
     */
    public static function isAutoGraduateEnabled(): bool
    {
        return static::get('auto_graduate_enabled', true);
    }
}
