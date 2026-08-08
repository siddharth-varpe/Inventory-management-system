<?php

declare(strict_types=1);

namespace App\Domain\Procurement;

use App\Models\Supplier;
use App\Models\User;
use InvalidArgumentException;
use DomainException;

class ApprovalEngine
{
    /**
     * Threshold Matrix:
     * - <= 10,000      : Procurement Executive
     * - 10,001 - 50,000: Purchase Manager
     * - 50,001 - 200,00: Finance Manager
     * - > 200,000      : Director / Admin
     */
    public function getRequiredRoleForAmount(float $amount): string
    {
        if ($amount <= 10000) {
            return 'Procurement Executive';
        }

        if ($amount <= 50000) {
            return 'Purchase Manager';
        }

        if ($amount <= 200000) {
            return 'Finance Manager';
        }

        return 'Director';
    }

    /**
     * Check if user can approve the given amount.
     */
    public function canApprove(User $user, float $amount): bool
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        if (empty($userRoles) || in_array('Admin', $userRoles) || in_array('Super Admin', $userRoles) || in_array('CEO', $userRoles)) {
            return true;
        }

        $requiredRole = $this->getRequiredRoleForAmount($amount);

        if ($requiredRole === 'Procurement Executive') {
            return true;
        }

        if ($requiredRole === 'Purchase Manager') {
            return in_array('Purchase Manager', $userRoles) || in_array('Inventory Manager', $userRoles);
        }

        if ($requiredRole === 'Finance Manager') {
            return in_array('Finance Manager', $userRoles);
        }

        return in_array('Director', $userRoles);
    }

    /**
     * Evaluate approval level against user limits.
     */
    public function evaluateApprovalLevel(float $amount, ?User $user = null): void
    {
        if ($user && !$this->canApprove($user, $amount)) {
            $requiredRole = $this->getRequiredRoleForAmount($amount);
            throw new DomainException("Approval Limit Exceeded: Amount ₹" . number_format($amount, 2) . " requires {$requiredRole} authorization.");
        }
    }

    /**
     * Validate supplier eligibility for issuing Purchase Orders.
     */
    public function validateSupplier(Supplier $supplier): void
    {
        if ($supplier->status === 'blacklisted') {
            throw new InvalidArgumentException("Cannot process procurement with blacklisted supplier: {$supplier->name}");
        }

        if ($supplier->status === 'inactive') {
            throw new InvalidArgumentException("Cannot process procurement with inactive supplier: {$supplier->name}");
        }
    }
}
