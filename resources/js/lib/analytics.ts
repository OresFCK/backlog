type AnalyticsProperties = Record<string, string | number | boolean | null>;

declare global {
    interface Window {
        dataLayer?: Array<Record<string, unknown>>;
    }
}

export const track = (event: string, properties: AnalyticsProperties = {}) => {
    if (typeof window === 'undefined') return;

    const payload = { event, ...properties };
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(payload);
    window.dispatchEvent(
        new CustomEvent('curator:analytics', { detail: payload }),
    );
};
