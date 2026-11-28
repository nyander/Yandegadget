import { tk } from '@/hooks/use-trans';
import type { ReservationDetail } from '@/types';

/** Reservation status names, marked for translation (see App\Enums\ReservationStatus). */
export const reservationStatusLabels: Record<
    ReservationDetail['status'],
    string
> = {
    awaiting_payment: tk('Waiting for payment'),
    reserved: tk('Paid, ready to collect'),
    collected: tk('Collected'),
    cancelled: tk('Cancelled'),
};
