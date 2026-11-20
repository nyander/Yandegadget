import type { Price } from './shop';

export type RequestStatusValue =
    | 'awaiting_deposit'
    | 'open'
    | 'sourced'
    | 'ready'
    | 'fulfilled'
    | 'cancelled';

export type RequestListItem = {
    id: number;
    reference: string;
    description: string;
    status: RequestStatusValue;
    status_label: string;
    category: string;
    condition: string;
    created_at: string | null;
};

export type RequestDetail = {
    id: number;
    reference: string;
    description: string;
    notes: string | null;
    status: RequestStatusValue;
    status_label: string;
    category: string;
    condition: { grade: string; name: string; description: string | null };
    deposit: Price | null;
    created_at: string | null;
    paid_at: string | null;
    sourced_at: string | null;
    ready_at: string | null;
    fulfilled_at: string | null;
    cancelled_at: string | null;
    cancel_reason: string | null;
    item: {
        reference: string;
        product: string;
        shop_slug: string | null;
        price: Price | null;
    } | null;
    payment: {
        status: string;
        status_label: string;
        channel: string | null;
        refunded: boolean;
    } | null;
    can_pay: boolean;
    can_cancel: boolean;
    /** What a cancellation now would refund, or null when nothing has been paid. */
    cancel_quote: {
        refund: Price;
        kept: Price | null;
        fee_percent: number;
    } | null;
    collect_by: string | null;
    refunded: Price | null;
};

export type ConditionOption = {
    id: number;
    grade: string;
    name: string;
    description: string | null;
    deposit: Price | null;
};
