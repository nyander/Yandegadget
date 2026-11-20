import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { RequestStatusValue } from '@/types';

const styles: Record<RequestStatusValue, string> = {
    awaiting_deposit: 'bg-muted text-foreground',
    open: 'bg-blue-600 text-white',
    sourced: 'bg-brand-gold text-black',
    ready: 'bg-brand-green text-white',
    fulfilled: 'bg-foreground text-background',
    cancelled: 'bg-brand-red text-white',
};

export function RequestStatusBadge({
    status,
    label,
}: {
    status: RequestStatusValue;
    label: string;
}) {
    return <Badge className={cn(styles[status])}>{label}</Badge>;
}
