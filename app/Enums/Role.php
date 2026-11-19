<?php

namespace App\Enums;

/**
 * The four roles carried over from 2020. Roles are looked up by name, so
 * their database IDs and seeding order don't matter.
 */
enum Role: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Customer = 'customer';
    case Supplier = 'supplier';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Staff => 'Store staff',
            self::Customer => 'Customer',
            self::Supplier => 'Supplier',
        };
    }

    /**
     * The permissions this role is granted by the reference-data seeder.
     *
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Staff => [
                Permission::ReceiveShipments,
                Permission::RecordSales,
            ],
            self::Customer => [
                Permission::RequestProducts,
            ],
            self::Supplier => [
                Permission::SubmitSupplierOffers,
            ],
        };
    }
}
