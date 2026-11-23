import { Badge } from '@/components/ui/badge';
import { useTrans } from '@/hooks/use-trans';
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
    const { t } = useTrans();

    return (
        <Badge
            key={status}
            className={cn(
                'animate-in duration-300 zoom-in-95 fade-in',
                styles[status],
            )}
        >
            {t(label)}
        </Badge>
    );
}
