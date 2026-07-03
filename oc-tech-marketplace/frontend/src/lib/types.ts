export type Vendor = {
  id: number;
  store_name: string;
  slug: string;
  description: string | null;
  website_url?: string | null;
  verification_status: string;
  commission_rate: string;
  wallet_balance?: string;
  is_owner?: boolean;
  user?: { id: number; name: string; email: string };
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
  status?: string;
  report_reason?: string | null;
  reported_at?: string | null;
  created_at: string;
  user?: { id: number; name: string };
  product?: { id: number; title: string };
};

export type FlashSale = {
  id: number;
  product_id: number;
  discount_percent: number;
  starts_at: string;
  ends_at: string;
  product?: { id: number; title: string; slug: string; base_price: string };
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
  active_flash_sale?: FlashSale | null;
  vendor?: Vendor;
  category?: Category;
  licenses?: License[];
  files?: ProductFile[];
  reviews?: Review[];
};

export function effectiveProductPrice(product: Product): number {
  const base = parseFloat(product.base_price);
  if (!product.active_flash_sale) return base;
  return Math.round(base * (1 - product.active_flash_sale.discount_percent / 100) * 100) / 100;
}

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
  held_in_escrow?: boolean;
  escrow_released_at?: string | null;
  is_flagged?: boolean;
  fraud_reasons?: string[] | null;
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

export type PlatformAnalytics = {
  total_gmv: number;
  platform_revenue: number;
  total_orders: number;
  total_users: number;
  total_vendors: number;
  total_products: number;
  pending_vendor_approvals: number;
  pending_withdrawals: number;
  pending_orders: number;
  top_vendors: { id: number; store_name: string; total_earnings: number }[];
};

export type AuditLogEntry = {
  id: number;
  action: string;
  subject_type: string | null;
  subject_id: number | null;
  metadata: Record<string, unknown> | null;
  created_at: string;
  user?: { id: number; name: string } | null;
};

export type Bundle = {
  id: number;
  vendor_id: number;
  title: string;
  slug: string;
  description: string | null;
  bundle_price: string;
  is_active: boolean;
  vendor?: { id: number; store_name: string };
  products?: Product[];
};

export type ReferralReward = {
  id: number;
  amount: string;
  created_at: string;
  referred_user?: { id: number; name: string };
};

export type ReferralSummary = {
  referral_code: string;
  referred_count: number;
  total_earned: number;
  rewards: ReferralReward[];
};

export type AiSearchResponse = {
  ai_powered: boolean;
  summary: string;
  results: Product[];
};

export type AiChatTurn = {
  role: "user" | "assistant";
  content: string;
};

export type BlogPost = {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  body: string;
  cover_image_url: string | null;
  status: string;
  published_at: string | null;
  created_at: string;
  author?: { id: number; name: string } | null;
};

export type ApiKeyInfo = {
  id: number;
  name: string;
  prefix: string;
  last_used_at: string | null;
  revoked_at: string | null;
  created_at: string;
};

export type WebhookEvent = "order.completed" | "product.published" | "withdrawal.approved";

export type WebhookEndpoint = {
  id: number;
  url: string;
  secret: string;
  events: WebhookEvent[];
  is_active: boolean;
  created_at: string;
};

export type WebhookDelivery = {
  id: number;
  event: string;
  response_status: number | null;
  error: string | null;
  created_at: string;
};

export type VendorTeamMember = {
  id: number;
  role: "owner" | "manager" | "staff";
  user?: { id: number; name: string; email: string };
};
