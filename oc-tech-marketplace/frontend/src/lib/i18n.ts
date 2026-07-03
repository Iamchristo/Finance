import { useLocaleStore } from "@/store/locale";

export const RTL_LOCALES = ["ar"];

const dictionary = {
  en: {
    "nav.products": "Products",
    "nav.bundles": "Bundles",
    "nav.blog": "Blog",
    "nav.becomeVendor": "Become a Vendor",
    "hero.badge": "We Bring Your Ideas To Life",
    "hero.title1": "All Digital Products.",
    "hero.title2": "One Smart Marketplace.",
    "hero.subtitle":
      "Buy, sell, and manage websites, templates, UI kits, AI agents, source code, and more — built for creators, developers, designers, and businesses worldwide.",
    "footer.rights": "All rights reserved.",
  },
  ar: {
    "nav.products": "المنتجات",
    "nav.bundles": "الحزم",
    "nav.blog": "المدونة",
    "nav.becomeVendor": "كن بائعًا",
    "hero.badge": "نحقق أفكارك على أرض الواقع",
    "hero.title1": "كل المنتجات الرقمية.",
    "hero.title2": "سوق ذكي واحد.",
    "hero.subtitle":
      "بع واشترِ وأدر المواقع والقوالب وواجهات المستخدم ووكلاء الذكاء الاصطناعي والأكواد المصدرية والمزيد — مصمم للمبدعين والمطورين والمصممين والشركات حول العالم.",
    "footer.rights": "جميع الحقوق محفوظة.",
  },
} as const;

export type TranslationKey = keyof (typeof dictionary)["en"];

export function useTranslation() {
  const locale = useLocaleStore((s) => s.locale);

  return {
    locale,
    t: (key: TranslationKey) => dictionary[locale][key] ?? dictionary.en[key],
  };
}
