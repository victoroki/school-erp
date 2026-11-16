<?php

namespace App\Support;

/**
 * The single place that relates a class to the CBC stage its learning areas are
 * grouped under.
 *
 * Two vocabularies describe "what level is this for", and nothing connected them:
 *
 *   cbc_learning_areas.level   VARCHAR — a CBC *stage*: 'Pre-Primary',
 *                              'Lower Primary', 'Upper Primary', 'Junior School'
 *   subjects.grade_level       TINYINT — a single grade, in the same numeric space
 *                              as classes.numeric_value
 *
 * A stage covers a range of grades, so the two cannot be compared directly. In
 * practice that meant the competency-assessment screen offered every learning
 * area in the school — a Grade 4 teacher was shown Junior School areas — because
 * there was no way to ask which areas belonged to a given class.
 *
 * Note the numeric space: classes.numeric_value is a sequential index, not the
 * grade number. PP1 is 1, PP2 is 2, and Grade 1 is 3, so Grade 1 sits at 3 and
 * Grade 12 at 14. Mapping it as if it were the grade number would be wrong for
 * every class in the school.
 */
class CbcStage
{
    public const PRE_PRIMARY = 'Pre-Primary';

    public const LOWER_PRIMARY = 'Lower Primary';

    public const UPPER_PRIMARY = 'Upper Primary';

    public const JUNIOR = 'Junior School';

    public const SENIOR = 'Senior School';

    /**
     * The stage a class belongs to, from its display name where possible and its
     * numeric value otherwise. The name is preferred because it states the grade
     * outright ("Grade 4"), while numeric_value depends on the offsets used when
     * the classes were created.
     */
    public static function forClass(?string $className, int|string|null $numericValue = null): ?string
    {
        $name = trim((string) $className);

        if (preg_match('/^PP\s*[12]$/i', $name)) {
            return self::PRE_PRIMARY;
        }

        if (preg_match('/(\d{1,2})/', $name, $matches)) {
            return self::forGrade((int) $matches[1]);
        }

        return $numericValue === null || $numericValue === ''
            ? null
            : self::forNumericValue((int) $numericValue);
    }

    /**
     * CBC: Pre-Primary (PP1-PP2), Lower Primary (1-3), Upper Primary (4-6),
     * Junior School (7-9), Senior School (10-12).
     */
    public static function forGrade(int $grade): ?string
    {
        return match (true) {
            $grade < 1 => self::PRE_PRIMARY,
            $grade <= 3 => self::LOWER_PRIMARY,
            $grade <= 6 => self::UPPER_PRIMARY,
            $grade <= 9 => self::JUNIOR,
            $grade <= 12 => self::SENIOR,
            default => null,
        };
    }

    /**
     * classes.numeric_value, which is a sequential index rather than the grade.
     */
    public static function forNumericValue(int $numericValue): ?string
    {
        return match (true) {
            $numericValue < 1 => null,
            $numericValue <= 2 => self::PRE_PRIMARY,
            $numericValue <= 5 => self::LOWER_PRIMARY,
            $numericValue <= 8 => self::UPPER_PRIMARY,
            $numericValue <= 11 => self::JUNIOR,
            $numericValue <= 14 => self::SENIOR,
            default => null,
        };
    }

    /**
     * Every stage name, for validation and for dropdowns.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::PRE_PRIMARY, self::LOWER_PRIMARY, self::UPPER_PRIMARY, self::JUNIOR, self::SENIOR];
    }
}
