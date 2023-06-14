<?php

namespace Smoren\Schemator\Interfaces;

use Smoren\Schemator\Exceptions\PathNotArrayException;
use Smoren\Schemator\Exceptions\PathNotExistException;

/**
 * Nested accessor tool interface.
 *
 * @template TPath of string|string[]|null
 *
 * Path delimiter for nested levels:
 * - dot character by default (example path: `"city.id"`)
 * - your custom character or string can be used as delimiter (example pathes: `"city/id"`, `"city[:]id"`)
 * - custom delimiter can be passed as optional param to the constructor of this interface implementation.
 *
 * Examples paths:
 * - `""` or `null` or `[]` — get root container.
 * - `"city.country.name"` or `["city", "country", "name"]` — get name of the country stored in the city sub-container.
 */
interface NestedAccessorInterface
{
    /**
     * Returns true if path exists in the container.
     *
     * Available path parts:
     * @see NestedAccessorInterface::get()
     *
     * @param TPath $path
     *
     * @return bool
     *
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function exist($path): bool;

    /**
     * Returns true if path exists in the container and it is not null.
     *
     * Available path parts:
     * @see NestedAccessorInterface::get()
     *
     * @param TPath $path
     *
     * @return bool
     *
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function isset($path): bool;

    /**
     * Returns value stored by path (or array of values when path contains asterisk characters).
     *
     * Available path parts:
     * - integer indexes of arrays;
     * - string keys for arrays, ArrayAccess instances, stdClass instances;
     * - string names of properties (public or available by public getters);
     * - asterisk character `*` as "for each" operator;
     * - repeating asterisks mean going through nesting levels);
     * - several asterisks can be combined into one part of the path (e.g. `***`);
     * - pipe character `|` as canceling "for each" operation.
     *
     * Examples:
     * - `""` or `null` or `[]` (for getting root container)
     * - `"city.country.name"` or `["city", "country", "name"]`
     * - `"country.cities.*.name"` or `["country", "cities", "*", "name"]`
     * - `"country.cities.*.|.0.name"` or `["country", "cities", "*", "|", 0, "name"]`
     * - `"*.prices.*.value"` or `["*", "prices", "*", "value"]`
     *
     * @param TPath $path
     *
     * @return mixed
     *
     * @throws PathNotExistException when path does not exist in container (only in strict mode).
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function get($path = null, bool $strict = true);

    /**
     * Sets value to the container by path.
     *
     * Creates path if it does not exist.
     *
     * Available path parts:
     * - integer indexes of arrays;
     * - string keys for arrays, ArrayAccess instances, stdClass instances;
     * - string names of properties (public or available by public getters);
     *
     * @param TPath $path
     * @param mixed $value
     *
     * @return $this
     *
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function set($path, $value): NestedAccessorInterface;

    /**
     * Updates value by path.
     *
     * Available path parts:
     * @see NestedAccessorInterface::set()
     *
     * @param TPath $path
     * @param mixed $value
     *
     * @return $this
     *
     * @throws PathNotExistException when path does not exist in container.
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function update($path, $value): self;

    /**
     * Appends value to the container stored by path.
     *
     * Available path parts:
     * @see NestedAccessorInterface::set()
     *
     * @param TPath $path
     * @param mixed $value
     *
     * @return $this
     *
     * @throws PathNotArrayException if values stored by path is not an array or ArrayAccess instance.
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function append($path, $value): self;

    /**
     * Deletes value stored by path.
     *
     * Available path parts:
     * @see NestedAccessorInterface::set()
     *
     * @param TPath $path
     * @param bool $strict
     *
     * @return $this
     *
     * @throws PathNotExistException when path does not exist in container (in strict mode only).
     * @throws \InvalidArgumentException when invalid path passed.
     */
    public function delete($path, bool $strict = true): self;
}
