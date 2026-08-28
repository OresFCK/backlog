import assert from 'node:assert/strict';
import test from 'node:test';

import {
    galleryImages,
    remapImageMarkers,
    splitImageContent,
} from '../../resources/js/lib/blogImages.ts';

test('inline images preserve surrounding text and are not duplicated in the gallery', () => {
    const body = 'Before\n[[image:2]]\nAfter';
    assert.deepEqual(splitImageContent(body, ['first.jpg', 'second.jpg']), [
        { text: 'Before\n', image: null },
        { text: '', image: 'second.jpg' },
        { text: '\nAfter', image: null },
    ]);
    assert.deepEqual(galleryImages(body, ['first.jpg', 'second.jpg']), [
        'first.jpg',
    ]);
});

test('deleting an image removes its markers and shifts remaining references', () => {
    assert.equal(
        remapImageMarkers('A [[image:1]] B [[image:2]] C [[image:3]]', [0, 2]),
        'A [[image:1]] B  C [[image:2]]',
    );
});

test('reordering images preserves the identity of inline images', () => {
    assert.equal(
        remapImageMarkers('[[image:1]] [[image:2]] [[image:1]]', [1, 0]),
        '[[image:2]] [[image:1]] [[image:2]]',
    );
});

test('replacing or clearing a gallery removes old references', () => {
    assert.equal(
        remapImageMarkers('Before [[image:1]] after', []),
        'Before  after',
    );
});

test('missing and invalid image indices cannot render arbitrary URLs', () => {
    const blocks = splitImageContent(
        '[[image:0]][[image:99]][[image:https://example.com]]',
        ['first.jpg'],
    );
    assert.ok(blocks.every((block) => block.image === null));
    assert.deepEqual(galleryImages('No markers here', ['first.jpg']), [
        'first.jpg',
    ]);
});
