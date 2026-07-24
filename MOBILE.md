# Curator.gg mobile app

The `android/` and `ios/` directories contain Capacitor 8 projects for the
mobile version of Curator.gg.

## Architecture

The current Laravel/Inertia frontend is rendered by the server. The native
container therefore loads `https://curator.gg` and uses `mobile-web/offline.html`
when the server cannot be reached. `resources/js/mobile.ts` enables native-only
behaviour after the website loads inside Capacitor.

This is suitable for internal testing and an initial Android build. Before an
App Store submission, consider moving the authenticated mobile experience to a
locally bundled SPA backed by a versioned API. Capacitor documents `server.url`
as a live-reload option rather than its preferred production architecture, and
Apple may reject an app that offers too little functionality beyond the website.

## Requirements

- Node.js 22 or newer
- Android Studio with the Android SDK and JDK required by Capacitor 8
- macOS with the current Xcode version for iOS builds

## Common commands

```bash
npm install
npm run build
npm run mobile:sync
npm run mobile:doctor
npm run mobile:open:android
```

On macOS, open the iOS project with:

```bash
npm run mobile:open:ios
```

Run `npm run mobile:sync` after changing `capacitor.config.ts`, installing a
Capacitor plugin, or editing files under `mobile-web/`.

Changes to `resources/js/mobile.ts` are part of the normal website bundle and
must also be deployed to `curator.gg` before they are visible in the native app.

## Deep links

Both platforms accept links using the custom scheme:

```text
curatorgg://reviews/123
```

The native listener maps the path to `https://curator.gg/reviews/123`. Universal
Links and Android App Links require production signing information and hosting
the platform association files on `curator.gg`; add those once store signing is
configured.

## Release checklist

1. Replace the default Capacitor launcher and splash assets with approved
   Curator.gg brand assets.
2. Increment Android `versionCode` / `versionName` and iOS build / marketing
   versions.
3. Configure Android signing and the Apple development team.
4. Add Universal Links / Android App Links after signing identifiers are known.
5. Verify Steam OpenID login, file uploads, external links, back navigation,
   keyboard behaviour, safe areas, and the offline screen on physical devices.
6. Review the store privacy forms and disclose account data, analytics and ads.
