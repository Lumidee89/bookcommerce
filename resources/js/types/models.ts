export interface BookCard {
    id: number;
    title: string;
    slug: string;
    subtitle: string | null;
    author: string;
    cover_url: string | null;
    sales_status: 'available' | 'free' | 'forthcoming' | 'display_only';
    sales_status_label: string;
    is_purchasable: boolean;
    from_price: number | null;
    currency: string;
    formats: string[];
}

export interface BookFormatOption {
    id: number;
    type: string;
    label: string;
    is_digital: boolean;
    price: number | null;
    in_stock: boolean;
    stock_quantity: number | null;
}

export interface BookDetail extends BookCard {
    description_html: string;
    table_of_contents_html: string;
    topics: string[];
    isbn: string | null;
    publisher: string | null;
    published_on: string | null;
    sample_url: string | null;
    categories: { name: string; slug: string }[];
    format_options: BookFormatOption[];
}

export interface ArticleCard {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    image_url: string | null;
    published_at: string | null;
    category: { name: string; slug: string } | null;
    author: string;
}

export interface ArticleDetail extends ArticleCard {
    body_html: string;
    tags: { name: string; slug: string }[];
    related_links: { label: string; url: string }[];
}

export interface VideoCard {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    thumbnail_url: string | null;
    duration: string | null;
    published_at: string | null;
    category: { name: string; slug: string } | null;
}

export interface VideoDetail extends VideoCard {
    embed_url: string | null;
    url: string;
    transcript: string | null;
}

export interface EventCard {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    starts_at: string;
    ends_at: string | null;
    timezone: string;
    venue: string | null;
    location: string | null;
    is_online: boolean;
    status: string;
    status_label: string;
    image_url: string | null;
}

export interface EventDetail extends EventCard {
    description_html: string;
    online_url: string | null;
    registration_url: string | null;
}

export interface AlbumCard {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    event_date: string | null;
    location: string | null;
    cover_url: string | null;
    photos_count: number;
}

export interface PhotoItem {
    id: number;
    url: string;
    thumb_url: string;
    caption: string | null;
    credit: string | null;
    alt: string;
    allow_download: boolean;
}

export interface AwardItem {
    id: number;
    category: string;
    category_label: string;
    title: string;
    issuer: string | null;
    awarded_on: string | null;
    description: string | null;
    image_url: string | null;
    certificate_url: string | null;
    url: string | null;
}

export interface TestimonialItem {
    id: number;
    name: string;
    role: string | null;
    organisation: string | null;
    body: string;
    photo_url: string | null;
    video_url: string | null;
}

export interface ServiceCard {
    id: number;
    name: string;
    slug: string;
    summary: string | null;
    audience: string | null;
    duration_minutes: number;
    fee: number;
    currency: string;
    delivery_method: string;
    delivery_method_label: string;
}

export interface ServiceDetail extends ServiceCard {
    description_html: string;
    preparation_instructions: string | null;
    cancellation_policy: string | null;
    rescheduling_policy: string | null;
}

export interface BookingSummary {
    reference: string;
    service_name: string;
    service_slug: string | null;
    starts_at: string;
    ends_at: string;
    client_timezone: string;
    name: string;
    email: string;
    fee: number;
    currency: string;
    status: string;
    status_label: string;
    delivery_method: string | null;
    meeting_url: string | null;
    meeting_details: string | null;
    hold_expires_at: string | null;
}

export interface CartLine {
    format_id: number;
    book_title: string;
    book_slug: string;
    cover_url: string | null;
    format_label: string;
    is_digital: boolean;
    quantity: number;
    unit_price: number | null;
    line_total: number;
    stock_quantity: number | null;
}

export interface CartSummary {
    currency: string;
    lines: CartLine[];
    subtotal: number;
    requires_shipping: boolean;
    issues: string[];
    can_checkout: boolean;
}

export interface DeliveryOptionItem {
    id: number;
    name: string;
    description: string | null;
    fee: number;
    currency: string;
    estimate: string | null;
}
