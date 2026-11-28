import type { Price } from './shop';

export type ShipmentSummary = {
    id: number;
    reference: string;
    status: 'draft' | 'in_transit' | 'received';
    carrier: string | null;
    destination: string;
    dispatched_on: string | null;
    expected_on: string | null;
    completed_at: string | null;
    items_count: number;
    checked_count: number;
};

export type ShipmentDetail = ShipmentSummary & {
    tracking_number: string | null;
    tracking_url?: string | null;
    notes: string | null;
};

export type ReceiptOutcomeValue = 'received' | 'damaged' | 'missing';

export type ShipmentItemRow = {
    id: number;
    reference: string;
    product: string;
    brand: string | null;
    serial_number: string | null;
    condition: { grade: string; name: string };
    condition_notes: string | null;
    /** The bulk purchase it belongs to, if any. */
    batch: { id: number; reference: string } | null;
    image: string | null;
    checked: boolean;
    outcome: ReceiptOutcomeValue | null;
    outcome_label: string | null;
    notes: string | null;
    checked_by: string | null;
    checked_at: string | null;
    photos: { thumb: string; full: string }[];
};

export type SellableUnit = {
    id: number;
    reference: string;
    product: string;
    brand: string | null;
    status: 'received' | 'reserved';
    condition: { grade: string; name: string };
    image: string | null;
    listed: Price | null;
};

export type PaymentMethodOption = {
    value: string;
    label: string;
    takes_reference: boolean;
};

export type SaleReceipt = {
    id: number;
    reference: string;
    product: string;
    brand: string | null;
    unit_reference: string;
    serial_number: string | null;
    condition: string;
    listed: Price;
    price_paid: Price;
    deposit_applied: Price | null;
    paid_in_store: Price;
    request_reference: string | null;
    discount_percent: number;
    payment_method: string;
    payment_reference: string | null;
    customer_name: string | null;
    customer_phone: string | null;
    notes: string | null;
    seller: string;
    location: string;
    address: string | null;
    sold_at: string;
    voided_at: string | null;
    void_reason: string | null;
};
