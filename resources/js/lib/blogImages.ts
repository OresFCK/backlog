// Image markers refer only to this post's gallery, never arbitrary URLs or HTML.
export const imageMarkerPattern = /\[\[image:(\d+)\]\]/g;

export function remapImageMarkers(body: string, order: number[]): string {
    return body.replace(imageMarkerPattern, (_marker, number) => {
        const index = order.indexOf(Number(number) - 1);

        return index < 0 ? '' : `[[image:${index + 1}]]`;
    });
}

export function splitImageContent(body: string, images: string[]) {
    return body.split(/(\[\[image:\d+\]\])/g).map((text) => {
        const match = /^\[\[image:(\d+)\]\]$/.exec(text);

        return match
            ? { text: '', image: images[Number(match[1]) - 1] ?? null }
            : { text, image: null };
    });
}

export function galleryImages(body: string, images: string[]): string[] {
    const used = new Set(
        Array.from(
            body.matchAll(imageMarkerPattern),
            (match) => Number(match[1]) - 1,
        ),
    );

    return images.filter((_image, index) => !used.has(index));
}
