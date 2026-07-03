import { create } from "zustand";
import { persist } from "zustand/middleware";

type CompareState = {
  ids: number[];
  toggle: (id: number) => void;
  clear: () => void;
};

const MAX_COMPARE = 4;

export const useCompareStore = create<CompareState>()(
  persist(
    (set, get) => ({
      ids: [],
      toggle: (id) => {
        const { ids } = get();
        if (ids.includes(id)) {
          set({ ids: ids.filter((i) => i !== id) });
        } else if (ids.length < MAX_COMPARE) {
          set({ ids: [...ids, id] });
        }
      },
      clear: () => set({ ids: [] }),
    }),
    { name: "octech-compare" },
  ),
);
