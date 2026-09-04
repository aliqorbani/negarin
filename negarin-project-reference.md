# پروژه نگارین — مرجع ساختار فایل‌ها

آخرین بروزرسانی: ۱۳ شهریور ۱۴۰۵ (بر اساس ریپوی گیت‌هاب)

> این فایل مرجع سریع برای پیدا کردن مسیر فایل‌ها و پوشه‌های پروژه است. هر بار که فایل/پوشه‌ای اضافه، حذف یا جابجا شد، این فایل هم باید بروز شود.

**سورس اصلی و کانونیک پروژه:** [`github.com/aliqorbani/negarin`](https://github.com/aliqorbani/negarin.git) (شاخه `main`). از این به بعد به‌جای آپلود zip، این ریپو کلون می‌شود.

---

## ۱. ساختار قالب وردپرس/ووکامرس (negarin-theme.zip)

```
negarin/
├── README.md
├── style.css
├── index.php
├── functions.php
├── header.php
├── footer.php
├── page.php
├── single.php
├── archive.php
├── package.json
├── package-lock.json
├── .gitignore
├── vite.config.js
├── tailwind.config.js
├── postcss.config.js
│
├── assets/
│   ├── css/
│   │   ├── app.css
│   │   └── admin.css
│   └── js/
│       ├── app.js
│       ├── ajax-cart.js
│       ├── cart.js
│       ├── checkout.js
│       ├── custom-order.js
│       ├── fragments.js
│       ├── size-select.js
│       ├── toast.js
│       └── otp.js
│
├── inc/
│   ├── classes/
│   │   └── OffcanvasMenuWalker.php
│   ├── helpers/
│   │   └── template-tags.php
│   ├── hooks/
│   │   ├── acf.php
│   │   ├── enqueue.php
│   │   ├── image-sizes.php
│   │   ├── nav-menus.php
│   │   ├── notices.php
│   │   ├── otp-guards.php
│   │   ├── setup.php
│   │   └── woocommerce.php
│   └── services/
│       ├── AccountMenu.php
│       ├── AddressBook.php
│       ├── BlogFields.php
│       ├── BuildCleaner.php
│       ├── CheckoutFields.php
│       ├── CustomOrder.php
│       ├── FlexibleContent.php
│       ├── FooterMessage.php
│       ├── OtpAuth.php
│       ├── ProductFields.php
│       ├── ProductSizing.php
│       ├── QuickSearch.php
│       ├── Seo.php
│       ├── ThemeOptions.php
│       └── Sms/
│           ├── KavenegarGateway.php
│           ├── LogGateway.php
│           ├── MelliPayamakGateway.php
│           └── SmsGatewayInterface.php
│
├── template-parts/
│   ├── components/
│   │   ├── accordion-item.php
│   │   ├── breadcrumbs.php
│   │   ├── cart-drawer-count.php
│   │   ├── custom-order-modal.php
│   │   ├── otp-login-form.php
│   │   ├── product-gallery.php
│   │   ├── quantity-stepper.php
│   │   ├── related-products.php
│   │   ├── size-chart-modal.php
│   │   ├── size-select-button.php
│   │   ├── size-select-modal.php
│   │   ├── toast-container.php
│   │   └── toc.php
│   ├── footer/
│   │   └── site-footer.php
│   ├── header/
│   │   ├── announcement-bar.php
│   │   ├── offcanvas-menu.php
│   │   └── site-header.php
│   └── sections/
│       ├── banner.php
│       ├── hero.php
│       ├── image_grid.php
│       ├── image_text.php
│       └── product_carousel.php
│
├── templates/
│   └── page-builder.php
│
└── woocommerce/
    ├── archive-product.php
    ├── content-product.php
    ├── content-single-product.php
    ├── cart/
    │   └── cart.php
    ├── checkout/
    │   ├── form-checkout.php
    │   └── thankyou.php
    ├── loop/
    │   ├── loop-start.php
    │   └── loop-end.php
    └── myaccount/
        ├── form-edit-account.php
        ├── my-account.php
        └── orders.php
```

**نکات ساختاری:**
- استک: وردپرس + ووکامرس + ACF (acf-json/) + Vite + Tailwind CSS
- پیامک OTP از طریق درگاه Kavenegar/ملی‌پیامک (`inc/services/Sms/`) پشت یک اینترفیس مشترک (`SmsGatewayInterface.php`)، با `LogGateway` برای تست
- سیستم Page Builder سفارشی با بخش‌های قابل ترکیب (`template-parts/sections/`) به همراه `FlexibleContent.php`
- کامپوننت‌های قابل استفاده مجدد در `template-parts/components/`
- **انتخاب سایز / سفارش شخصی (شهریور ۱۴۰۵):** هر عبا محصول متغیر واقعی ووکامرس با attribute سراسری «سایز» (`pa_size`، اعداد ۳۲..۵۶) است — attribute و ترم‌ها به‌صورت کد در `ProductSizing.php` ساخته می‌شوند، نه دستی از ادمین. صفحه محصول دکمه «انتخاب سایز» را نشان می‌دهد که مودال `size-select-modal.php` را باز می‌کند؛ «راهنمای سایز» (`size-chart-modal.php`) و «سفارش شخصی» (`custom-order-modal.php`) هر دو روی همان مودال stack می‌شوند. سفارش شخصی فقط برای کاربر لاگین‌شده در دسترس است (بدون فیلد نام/شماره مهمان) — کاربر مهمان به `/my-account/?redirect_to=...` هدایت می‌شود و `OtpAuth.php` بعد از ورود دقیقاً به همان صفحه برمی‌گرداند. هر دو مسیر افزودن به سبد از طریق REST مستقیم (`negarin/v1/size-select/add-to-cart` و `negarin/v1/custom-order/add-to-cart`) انجام می‌شود، نه فرم کلاسیک.
- **Toast (شهریور ۱۴۰۵):** همه‌ی پیام‌های ووکامرس (`wc_add_notice`) به‌جای اشغال بخشی از صفحه، به‌صورت toast نمایش داده می‌شوند — `inc/hooks/notices.php` خروجی‌های پیش‌فرض را حذف و در یک container مخفی (`#negarin-wc-notices`) جمع می‌کند؛ `assets/js/toast.js` آن را می‌خواند و بعد از ۱۰ ثانیه خودش پاک می‌کند.

---

## ۲. اسکرین‌شات‌های طراحی (negarin-screenshot.zip)

```
screenshots-mobile/
├── homepage.png / homepage.jpg
├── login-signup.png / login-signup.jpg
├── otp-input.png
├── navigation-menu.png
├── single-product.png / single-product.jpg
└── customized-order-modal.png

screenshots-desktop/
├── login-signup.png / login-signup.jpg
├── otp-input.png / otp-input.jpg
├── single-product.png / single-product.jpg
├── menu-structure-(desktop-mobile).png
├── size-helper-modal.png / size-helper-modal.jpg
├── customized-order-modal.png
├── cart.png
├── cart-empty.png
├── form-shipping.png
├── form-payment-select.png
├── success-order-complete.png
├── dashboard-no-orders.png
├── dashboard-orders.png
└── dashboard-profile.png
```

**نکته:** برخی صفحات هم نسخه `.png` و هم `.jpg` دارند (احتمالاً نسخه خام و فشرده‌شده). موبایل و دسکتاپ صفحات مشترکی دارند (لاگین/ثبت‌نام، OTP، تک‌محصول، مودال سفارش سفارشی، راهنمای سایز) اما بعضی صفحات فقط در یکی از دو حالت طراحی شده‌اند (مثلاً صفحات سبد خرید، فرم‌های تسویه‌حساب و داشبورد فعلاً فقط دسکتاپ دارند).

---

## ۳. گیت و روال کاری

- سورس اصلی در `https://github.com/aliqorbani/negarin.git` (شاخه `main`) نگه‌داری می‌شود.
- `.gitignore` فعلی: `node_modules/`، `assets/build/`، `.DS_Store`، `*.log` — یعنی خروجی build و پکیج‌های نصب‌شده وارد گیت نمی‌شوند و باید طبق `negarin-deploy-guide.md` قبل از دیپلوی ساخته شوند.
- در ابتدای هر نشست کاری جدید، به‌جای آپلود zip، ریپو کلون/pull می‌شود.
- تغییرات اعمال‌شده در طول گفتگو در پایان به‌صورت فایل تحویل داده می‌شود؛ ارسال نهایی (`commit` / `push`) روی سیستم خود کاربر انجام می‌شود.

## ۴. قوانین کاری پروژه

- **هر تغییری که در گفتگو اعمال یا پیشنهاد می‌شود، باید در سورس واقعی پروژه (فایل‌های تم) هم اعمال/آپدیت شود** — نه فقط توضیح داده شود.
- وقتی فایل یا پوشه‌ای اضافه/حذف/جابجا شد، این فایل مرجع (`negarin-project-reference.md`) هم باید بروزرسانی شود.
- نام‌گذاری کلیدهای `name` در `FlexibleContent.php` باید همیشه دقیقاً با نام فایل مقابلش در `template-parts/sections/` یکی باشد (با آندرلاین، نه خط تیره) — این نکته باعث باگ «سکشن پیدا نشد» شده بود.
