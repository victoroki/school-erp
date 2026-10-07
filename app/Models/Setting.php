<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Key/value application settings.
 *
 * The `settings` table was created with the schema but never used, so it is
 * the natural home for values an administrator needs to change — the library's
 * fine rate and loan period, for example — without a code change.
 */
class Setting extends Model
{
    public $table = 'settings';
    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'field_type',
        'options',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Per-request memo. Settings are read a handful of times per request at
     * most (issue a book, compute a fine), and the values are needed on every
     * book issue, so this avoids a query per lookup without introducing a
     * cache that could go stale between the settings page and the next issue.
     *
     * @var array<string, mixed>
     */
    private static array $resolved = [];

    public static function forgetResolved(): void
    {
        self::$resolved = [];
    }

    /**
     * Read a setting, falling back when it is absent or unparseable.
     *
     * A missing or blank row is a normal state, not an error: a fresh install
     * has no rows until the seeder runs, and an administrator clearing a field
     * should fall back to the default rather than break issuing.
     */
    public static function value(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$resolved)) {
            return self::$resolved[$key];
        }

        $row = self::query()->where('setting_key', $key)->first();

        $value = $row && $row->setting_value !== null && $row->setting_value !== ''
            ? $row->setting_value
            : $default;

        return self::$resolved[$key] = $value;
    }

    public static function number(string $key, float $default): float
    {
        $value = self::value($key);

        return is_numeric($value) ? (float) $value : $default;
    }

    public static function integer(string $key, int $default): int
    {
        $value = self::value($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Create or update a setting.
     */
    public static function put(string $key, mixed $value, string $fieldType = 'text'): self
    {
        $setting = self::query()->firstOrNew(['setting_key' => $key]);
        $setting->setting_value = $value === null ? null : (string) $value;
        $setting->field_type = $fieldType;
        $setting->save();

        unset(self::$resolved[$key]);

        return $setting;
    }
}
