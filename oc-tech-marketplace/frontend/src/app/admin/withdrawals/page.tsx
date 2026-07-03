import { Suspense } from "react";
import { AdminWithdrawalsView } from "@/components/admin/AdminWithdrawalsView";

export default function AdminWithdrawalsPage() {
  return (
    <Suspense fallback={null}>
      <AdminWithdrawalsView />
    </Suspense>
  );
}
