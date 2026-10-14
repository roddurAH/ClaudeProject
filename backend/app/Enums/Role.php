<?php

namespace App\Enums;

/**
 * Who a user is in the system. Staff roles can open the admin panel;
 * "farmer" is every ordinary mobile-app user (farmer, gardener, learner …).
 *
 * Permissions are defined as Gates in AppServiceProvider — keep the two in sync.
 */
enum Role: string
{
    case SuperAdmin = 'super_admin'; // everything, incl. creating other admins
    case Admin = 'admin';            // content, prices, alerts, questions, app users
    case Expert = 'expert';          // answers questions, edits crop knowledge
    case DataEntry = 'data_entry';   // market prices only
    case Farmer = 'farmer';          // mobile app user

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super admin',
            self::Admin => 'Admin',
            self::Expert => 'Agriculture expert',
            self::DataEntry => 'Price data entry',
            self::Farmer => 'App user',
        };
    }

    public function isStaff(): bool
    {
        return $this !== self::Farmer;
    }

    /** @return list<self> */
    public static function staff(): array
    {
        return array_values(array_filter(self::cases(), fn (self $r) => $r->isStaff()));
    }
}
