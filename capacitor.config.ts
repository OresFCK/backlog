import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'gg.curator.app',
    appName: 'Curator.gg',
    webDir: 'mobile-web',
    server: {
        url: 'https://curator.gg',
        cleartext: false,
        allowNavigation: ['curator.gg', '*.curator.gg', 'steamcommunity.com'],
        errorPath: 'offline.html',
    },
    plugins: {
        App: {
            disableBackButtonHandler: true,
        },
        Keyboard: {
            resize: 'body',
            resizeOnFullScreen: true,
        },
        SplashScreen: {
            launchAutoHide: true,
            launchShowDuration: 1200,
            backgroundColor: '#09090b',
            showSpinner: false,
        },
        StatusBar: {
            overlaysWebView: false,
            style: 'LIGHT',
            backgroundColor: '#09090b',
        },
    },
};

export default config;
