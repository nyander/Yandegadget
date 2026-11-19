<?php

namespace App\Enums;

/**
 * Every permission in the app. Authorise with these, never with role names or IDs.
 */
enum Permission: string
{
    case ManageCatalogue = 'catalogue.manage';
    case ManageShipments = 'shipments.manage';
    case ReceiveShipments = 'shipments.receive';
    case RecordSales = 'sales.record';
    case RequestProducts = 'requests.create';
    case ManageRequests = 'requests.manage';
    case SubmitSupplierOffers = 'supplier-offers.submit';
    case ReviewSupplierOffers = 'supplier-offers.review';
    case ViewReports = 'reports.view';
    case ManageFinances = 'finances.manage';
    case ManageSettings = 'settings.manage';
    case ManageUsers = 'users.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission) => $permission->value, self::cases());
    }
}
