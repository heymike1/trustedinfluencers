Drop these files in place; the markup already points at them.

- `/images/og-default.png`   1200×630, the default share image (Open Graph / Twitter)
- `/favicon.ico`             32×32 (a placeholder ships with Laravel; replace it)
- `/icon.svg`                vector favicon
- `/apple-touch-icon.png`    180×180
- `/android-chrome-192x192.png`, `/android-chrome-512x512.png`   for site.webmanifest

Override the default share image with `APP_OG_IMAGE` in `.env`.
