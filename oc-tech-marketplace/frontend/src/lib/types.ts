export type Vendor = {
  id: number;
  store_name: string;
  slug: string;
  description: string | null;
  website_url?: string | null;
  verification_status: string;
  commission_rate: string;
  wallet_balance?: string;
};

export type Category = {
  id: number;
  name: string;
  slug: string;
  children?: Category[];
};

export type License = {
  id: number;
  product_id: number;
  type: string;
  name: string;
  price: string;
  terms: string | null;
  download_limit: number | null;
};

export type ProductFile = {
  id: number;
  version: string;
  size_bytes: number | null;
  changelog: string | null;
  is_current: boolean;
};

export type Review = {
  id: number;
  product_id: number;
  user_id: number;
  rating: number;
  comment: string | null;
  is_verified_purchase: boolean;
  vendor_reply: string | null;
  helpful_votes: number;
  created_at: string;
  user?: { id: number; name: string };
};

export type Product = {
  id: number;
  vendor_id: number;
  category_id: number;
  title: string;
  slug: string;
  summary: string | null;
  description: string | null;
  thumbnail_url: string | null;
  base_price: string;
  status: string;
  sales_count: number;
  download_count: number;
  average_rating: string;
  published_at: string | null;
  vendor?: Vendor;
  category?: Category;
  licenses?: License[];
  files?: ProductFile[];
  reviews?: Review[];
};

export type WishlistEntry = {
  id: number;
  product_id: number;
  product?: Product;
};

export type Coupon = {
  id: number;
  code: string;
  type: "percentage" | "fixed";
  value: string;
  usage_limit: number | null;
  used_count: number;
  expires_at: string | null;
  is_active: boolean;
};

export type VendorAnalytics = {
  total_revenue: number;
  total_sales: number;
  wallet_balance: string;
  product_count: number;
  top_products: { id: number; title: string; sales_count: number; average_rating: string }[];
  recent_orders: OrderItem[];
};

export type WithdrawalRequest = {
  id: number;
  amount: string;
  status: "pending" | "approved" | "rejected";
  payout_method: string;
  notes: string | null;
  created_at: string;
};

export type SupportTicketMessage = {
  id: number;
  message: string;
  created_at: string;
  user?: { id: number; name: string; role?: string };
};

export type SupportTicket = {
  id: number;
  subject: string;
  status: string;
  priority: string;
  created_at: string;
  user?: { id: number; name: string; email: string };
  messages?: SupportTicketMessage[];
};

export type Paginated<T> = {
  data: T[];
  current_page: number;
  last_page: number;
  total: number;
};

export type Order = {
  id: number;
  order_number: string;
  status: string;
  subtotal: string;
  discount_total: string;
  tax_total: string;
  grand_total: string;
  payment_gateway: string | null;
  paid_at: string | null;
  created_at: string;
  items?: OrderItem[];
};

export type OrderItem = {
  id: number;
  product_id: number;
  license_id: number;
  price: string;
  vendor_earnings: string;
  downloads_used: number;
  product?: Product;
  license?: License;
  order?: Order;
};

export type Wallet = {
  id: number;
  balance: string;
  currency: string;
  transactions?: WalletTransaction[];
};

export type WalletTransaction = {
  id: number;
  type: "credit" | "debit";
  amount: string;
  balance_after: string;
  description: string | null;
  created_at: string;
};
