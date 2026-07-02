import { Suspense } from "react";
import { ProductsBrowser } from "@/components/ProductsBrowser";

export default function ProductsPage() {
  return (
    <Suspense fallback={null}>
      <ProductsBrowser />
    </Suspense>
  );
}
