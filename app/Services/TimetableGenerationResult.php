<?php

namespace App\Services;

class TimetableGenerationResult
{
    /** @var array<int, array<string, mixed>> Proposed timetable rows ready to persist. */
    public array $placements = [];

    /** @var array<int, array{class_section: string, subject: string, teachers: array<int, string>, reason: string}> */
    public array $unplaced = [];

    public function isComplete(): bool
    {
        return count($this->unplaced) === 0;
    }

    public function placedCount(): int
    {
        return count($this->placements);
    }

    public function unplacedCount(): int
    {
        return count($this->unplaced);
    }
}
