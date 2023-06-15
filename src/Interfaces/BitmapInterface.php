<?php

declare(strict_types=1);

namespace Smoren\Schemator\Interfaces;

interface BitmapInterface
{
    /**
     * Creates Bitmap instance from one of int value, list of true bits or another Bitmap instance.
     *
     * @param BitmapInterface|int|array<int> $value value (e. g. 5 or [0, 2])
     * @return BitmapInterface
     */
    public static function create($value): BitmapInterface;

    /**
     * Creates Bitmap instance from the list of true bits.
     *
     * @param array<int> $bits array of true bit positions
     * @return BitmapInterface
     */
    public static function createFromArray(array $bits): BitmapInterface;

    /**
     * Converts argument value to Bitmap instance.
     *
     * @param BitmapInterface|int|array<int> $bitmap value to convert
     * @return BitmapInterface
     */
    public static function toBitmap($bitmap): BitmapInterface;

    /**
     * Getter for value field.
     *
     * @return int
     */
    public function getValue(): int;

    /**
     * Returns true if this bitmap has intersection with another one.
     *
     * @param BitmapInterface|int|array<int> $bitmap another bitmap
     *
     * @return bool
     */
    public function intersectsWith($bitmap): bool;

    /**
     * Returns true if this bitmap includes another one.
     *
     * @param BitmapInterface|int|array<int> $bitmap another bitmap
     *
     * @return bool
     */
    public function includes($bitmap): bool;

    /**
     * Returns true if this bitmap is equal with another one.
     *
     * @param BitmapInterface|int|array<int> $bitmap another bitmap
     *
     * @return bool
     */
    public function isEqualWith($bitmap): bool;

    /**
     * Returns new Bitmap instance by bitwise addition with another bitmap.
     *
     * @param BitmapInterface|int|array<int> $bitmap another bitmap
     *
     * @return BitmapInterface
     */
    public function add($bitmap): BitmapInterface;

    /**
     * Returns new Bitmap instance by bitwise subtraction with another bitmap.
     *
     * @param BitmapInterface|int|array<int> $bitmap another bitmap
     *
     * @return BitmapInterface
     */
    public function sub($bitmap): BitmapInterface;

    /**
     * Returns true if this bitmap has true bit on given position.
     *
     * @param int $bitPosition bit position
     *
     * @return bool
     */
    public function hasBit(int $bitPosition): bool;

    /**
     * Returns an array of true bit positions.
     *
     * @return array<int>
     */
    public function toArray(): array;
}
