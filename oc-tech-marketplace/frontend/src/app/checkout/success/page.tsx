import { Suspense } from "react";
import { CheckoutSuccess } from "@/components/CheckoutSuccess";

export default function CheckoutSuccessPage() {
  return (
    <Suspense fallback={null}>
      <CheckoutSuccess />
    </Suspense>
  );
}
