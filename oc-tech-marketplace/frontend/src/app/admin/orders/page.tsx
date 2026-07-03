import { Suspense } from "react";
import { AdminOrdersView } from "@/components/admin/AdminOrdersView";

export default function AdminOrdersPage() {
  return (
    <Suspense fallback={null}>
      <AdminOrdersView />
    </Suspense>
  );
}
