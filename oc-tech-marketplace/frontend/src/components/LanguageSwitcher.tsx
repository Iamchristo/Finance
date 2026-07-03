"use client";

import { useLocaleStore } from "@/store/locale";

export function LanguageSwitcher() {
  const { locale, setLocale } = useLocaleStore();

  return (
    <button
      onClick={() => setLocale(locale === "en" ? "ar" : "en")}
      className="text-sm font-medium text-foreground/70 hover:text-foreground"
      aria-label="Switch language"
    >
      {locale === "en" ? "AR" : "EN"}
    </button>
  );
}
