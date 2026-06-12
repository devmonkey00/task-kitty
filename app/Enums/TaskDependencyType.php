<?php

namespace App\Enums;

enum TaskDependencyType: string
{
    case FinishToStart = 'finish_to_start';

    public function label(): string
    {
        return match ($this) {
            self::FinishToStart => 'Finish to Start',
        };
    }
}
