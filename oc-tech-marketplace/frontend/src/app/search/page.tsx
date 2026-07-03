import { Suspense } from "react";
import { AiSearchView } from "@/components/AiSearchView";

export default function SearchPage() {
  return (
    <Suspense fallback={null}>
      <AiSearchView />
    </Suspense>
  );
}
