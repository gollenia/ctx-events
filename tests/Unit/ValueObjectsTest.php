<?php
declare(strict_types=1);

use Contexis\Events\Location\Domain\GeoCoordinates;
use Contexis\Events\Media\Domain\ImageSize;
use Contexis\Events\Media\Domain\ImageSizes;
use Contexis\Events\Media\Domain\Image;
use Contexis\Events\Media\Domain\ImageId;
use Contexis\Events\Shared\Domain\ValueObjects\Currency;
use Contexis\Events\Shared\Domain\ValueObjects\Price;

test('Price cannot be negative', function () {
    expect(fn() => new Price(-1, Currency::fromCode('EUR')))->toThrow(\InvalidArgumentException::class);
});

test('Price equality compares amount and currency', function () {
    $a = new Price(1000, Currency::fromCode('EUR'));
    $b = new Price(1000, Currency::fromCode('EUR'));
    expect($a->equals($b))->toBeTrue();
});

test('Image url() returns size url when available', function () {
    $small = new ImageSize('https://site/small.jpg', 320, 240);
    $sizes = new ImageSizes(['small' => $small]);
    $image = new Image(ImageId::from(1), 'https://site/original.jpg', 'alt', 800, 600, 'image/jpeg', $sizes);

    expect($image->url('small'))->toBe('https://site/small.jpg');
});

test('Image url() falls back to original url when size missing', function () {
    $image = new Image(ImageId::from(1), 'https://site/original.jpg', 'alt', 800, 600, 'image/jpeg');
    expect($image->url('nonexistent'))->toBe('https://site/original.jpg');
});

test('Image url() returns empty string when no url at all', function () {
    $image = new Image(ImageId::from(0), null, null, null, null, null);
    expect($image->url())->toBe('');
});

test('ImageSizes getSize() returns null for unknown key', function () {
    $sizes = new ImageSizes(['thumb' => new ImageSize('t.jpg', 100, 100)]);
    expect($sizes->getSize('large'))->toBeNull();
});



test('accepts valid coordinates', function () {
    $coords = new GeoCoordinates(47.8, 13.0);
    expect($coords->latitude)->toBe(47.8)
        ->and($coords->longitude)->toBe(13.0);
});

test('rejects NaN or infinite values', function () {
    expect(fn() => new GeoCoordinates(NAN, 0.0))
        ->toThrow(\InvalidArgumentException::class);
    expect(fn() => new GeoCoordinates(INF, 0.0))
        ->toThrow(\InvalidArgumentException::class);
});

test('rejects latitude out of range', function () {
    expect(fn() => new GeoCoordinates(-91, 0))
        ->toThrow(\InvalidArgumentException::class);
    expect(fn() => new GeoCoordinates(91, 0))
        ->toThrow(\InvalidArgumentException::class);
});

test('rejects longitude out of range', function () {
    expect(fn() => new GeoCoordinates(0, -181))
        ->toThrow(\InvalidArgumentException::class);
    expect(fn() => new GeoCoordinates(0, 181))
        ->toThrow(\InvalidArgumentException::class);
});
