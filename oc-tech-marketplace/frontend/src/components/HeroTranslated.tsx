"use client";

import { useTranslation } from "@/lib/i18n";

export function HeroTranslated() {
  const { t } = useTranslation();

  return (
    <>
      <span className="glass rounded-full px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-foreground/70">
        {t("hero.badge")}
      </span>
      <h1 className="mt-6 text-4xl font-bold tracking-tight sm:text-6xl">
        {t("hero.title1")}
        <br />
        <span className="brand-gradient-text">{t("hero.title2")}</span>
      </h1>
      <p className="mt-6 max-w-2xl text-lg text-foreground/60">{t("hero.subtitle")}</p>
    </>
  );
}
