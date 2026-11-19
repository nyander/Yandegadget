export type Price = {
    amount: number;
    currency: string;
    formatted: string;
};

export type ShopImage = {
    thumb: string;
    large: string;
    alt: string;
};

export type CategoryLink = {
    name: string;
    slug: string;
    count?: number;
};

export type ProductCard = {
    id: number;
    name: string;
    slug: string;
    brand: string | null;
    category: CategoryLink;
    image: ShopImage | null;
    from_price: Price | null;
    in_store_count: number;
    reserved_count: number;
};

export type StockUnitOffer = {
    reference: string;
    status: 'in_store' | 'reserved';
    price: Price;
    condition: {
        grade: string;
        name: string;
        description: string | null;
    };
    condition_notes: string | null;
    photos: ShopImage[];
};

export type ProductDetail = {
    id: number;
    name: string;
    slug: string;
    brand: string | null;
    model: string | null;
    description: string | null;
    specs: Record<string, string>;
    category: CategoryLink;
    images: ShopImage[];
    units: StockUnitOffer[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
};

export type ShopSort = 'newest' | 'price_asc' | 'price_desc';
