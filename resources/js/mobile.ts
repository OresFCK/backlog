import { App } from '@capacitor/app';
import { Browser } from '@capacitor/browser';
import { Capacitor } from '@capacitor/core';
import { Keyboard, KeyboardResize } from '@capacitor/keyboard';
import { Network } from '@capacitor/network';
import { SplashScreen } from '@capacitor/splash-screen';
import { StatusBar, Style } from '@capacitor/status-bar';

const APP_HOST = 'curator.gg';
const WEB_ORIGIN = `https://${APP_HOST}`;

const isInternalUrl = (url: URL) =>
    url.hostname === APP_HOST || url.hostname.endsWith(`.${APP_HOST}`);

const isSteamAuthUrl = (url: URL) =>
    url.hostname === 'steamcommunity.com' &&
    url.pathname.startsWith('/openid/');

const initializeMobileApp = async () => {
    if (!Capacitor.isNativePlatform()) {
        return;
    }

    document.documentElement.classList.add('capacitor-native');

    await Promise.allSettled([
        StatusBar.setStyle({ style: Style.Light }),
        StatusBar.setBackgroundColor({ color: '#09090b' }),
        StatusBar.setOverlaysWebView({ overlay: false }),
        Keyboard.setResizeMode({ mode: KeyboardResize.Body }),
        SplashScreen.hide(),
    ]);

    await App.addListener('backButton', ({ canGoBack }) => {
        if (canGoBack) {
            window.history.back();
            return;
        }

        void App.exitApp();
    });

    await App.addListener('appUrlOpen', ({ url }) => {
        try {
            const incomingUrl = new URL(url);

            if (incomingUrl.protocol === 'curatorgg:') {
                window.location.href = `${WEB_ORIGIN}${incomingUrl.pathname}${incomingUrl.search}${incomingUrl.hash}`;
                return;
            }

            if (isInternalUrl(incomingUrl)) {
                window.location.href = incomingUrl.href;
            }
        } catch {
            // Ignore malformed deep links supplied by other applications.
        }
    });

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const anchor = target.closest('a[href]');

        if (!(anchor instanceof HTMLAnchorElement)) {
            return;
        }

        const url = new URL(anchor.href, window.location.href);

        if (url.protocol === 'http:' || url.protocol === 'https:') {
            if (!isInternalUrl(url) && !isSteamAuthUrl(url)) {
                event.preventDefault();
                void Browser.open({ url: url.href });
            }
        }
    });

    const updateConnectionState = (connected: boolean) => {
        document.documentElement.classList.toggle(
            'capacitor-offline',
            !connected,
        );
    };

    const initialStatus = await Network.getStatus();
    updateConnectionState(initialStatus.connected);

    await Network.addListener('networkStatusChange', ({ connected }) => {
        updateConnectionState(connected);
    });
};

void initializeMobileApp();
