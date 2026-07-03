"use client";

import { useEffect } from "react";
import { RTL_LOCALES } from "@/lib/i18n";
import { useLocaleStore } from "@/store/locale";

export function LocaleController() {
  const locale = useLocaleStore((s) => s.locale);

  useEffect(() => {
    document.documentElement.lang = locale;
    document.documentElement.dir = RTL_LOCALES.includes(locale) ? "rtl" : "ltr";
  }, [locale]);

  return null;
}
