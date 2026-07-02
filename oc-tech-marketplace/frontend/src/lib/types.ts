export type Vendor = {
  id: number;
  store_name: string;
  slug: string;
  description: string | null;
  verification_status: string;
  commission_rate: string;
  balance: string;
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
