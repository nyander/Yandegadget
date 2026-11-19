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
    image: string | null;
    checked: boolean;
    outcome: ReceiptOutcomeValue | null;
    outcome_label: string | null;
    notes: string | null;
    checked_by: string | null;
    checked_at: string | null;
    photos: { thumb: string; full: string }[];
};
