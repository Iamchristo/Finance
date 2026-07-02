import { create } from "zustand";
import { persist } from "zustand/middleware";

export type CartItem = {
  productId: number;
  productTitle: string;
  productSlug: string;
  licenseId: number;
  licenseName: string;
  price: number;
};

type CartState = {
  items: CartItem[];
  addItem: (item: CartItem) => void;
  removeItem: (licenseId: number) => void;
  clear: () => void;
};

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      addItem: (item) => {
        if (get().items.some((i) => i.licenseId === item.licenseId)) {
          return;
        }
        set({ items: [...get().items, item] });
      },
      removeItem: (licenseId) => set({ items: get().items.filter((i) => i.licenseId !== licenseId) }),
      clear: () => set({ items: [] }),
    }),
    { name: "octech-cart" },
  ),
);

export function cartSubtotal(items: CartItem[]): number {
  return items.reduce((sum, item) => sum + item.price, 0);
}
