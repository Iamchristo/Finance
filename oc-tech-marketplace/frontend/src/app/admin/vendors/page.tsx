import { Suspense } from "react";
import { AdminVendorsView } from "@/components/admin/AdminVendorsView";

export default function AdminVendorsPage() {
  return (
    <Suspense fallback={null}>
      <AdminVendorsView />
    </Suspense>
  );
}
