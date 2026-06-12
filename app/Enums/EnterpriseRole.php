<?php

namespace App\Enums;

enum EnterpriseRole: string
{
    case SuperAdmin = 'Super Admin';
    case HrAdmin = 'HR Admin';
    case ProjectManager = 'Project Manager';
    case TeamLead = 'Team Lead';
    case Employee = 'Employee';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** @return list<string> */
    public static function projectManagementRoles(): array
    {
        return [
            self::SuperAdmin->value,
            self::HrAdmin->value,
            self::ProjectManager->value,
        ];
    }

    /** Roles that may edit any task (not only their own). */
    public static function taskEditorRoles(): array
    {
        return [
            self::SuperAdmin->value,
            self::HrAdmin->value,
            self::ProjectManager->value,
        ];
    }

    /** @return list<string> */
    public static function leaveApprovalRoles(): array
    {
        return [
            self::SuperAdmin->value,
            self::HrAdmin->value,
            self::TeamLead->value,
        ];
    }
}
