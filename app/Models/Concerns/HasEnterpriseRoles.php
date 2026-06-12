<?php

namespace App\Models\Concerns;

use App\Enums\EnterpriseRole;

trait HasEnterpriseRoles
{
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(EnterpriseRole::SuperAdmin->value);
    }

    public function isHrAdmin(): bool
    {
        return $this->hasRole(EnterpriseRole::HrAdmin->value);
    }

    public function isEmployee(): bool
    {
        return $this->hasRole(EnterpriseRole::Employee->value);
    }

    public function isProjectManager(): bool
    {
        return $this->hasAnyRole([
            EnterpriseRole::ProjectManager->value,
            'Manager',
        ]);
    }

    public function canManageProjects(): bool
    {
        return $this->hasAnyRole(EnterpriseRole::projectManagementRoles());
    }

    public function canEditTasks(): bool
    {
        return $this->hasAnyRole([
            ...EnterpriseRole::taskEditorRoles(),
            'Manager',
        ]);
    }

    public function canApproveLeave(): bool
    {
        return $this->hasAnyRole(EnterpriseRole::leaveApprovalRoles());
    }

    public function canFinalizeLeave(): bool
    {
        return $this->hasAnyRole([
            EnterpriseRole::SuperAdmin->value,
            EnterpriseRole::HrAdmin->value,
        ]);
    }

    public function canViewHrDashboard(): bool
    {
        return $this->hasAnyRole([
            EnterpriseRole::SuperAdmin->value,
            EnterpriseRole::HrAdmin->value,
        ]);
    }
}
