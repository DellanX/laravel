<?php
/**
 * Copyright 2020 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Core\Validation;

use Illuminate\Support\Arr;
use LaravelJsonApi\Core\Rules\AllowedFieldSets;
use LaravelJsonApi\Core\Rules\AllowedFilterParameters;
use LaravelJsonApi\Core\Rules\AllowedIncludePaths;
use LaravelJsonApi\Core\Rules\AllowedPageParameters;
use LaravelJsonApi\Core\Rules\AllowedSortParameters;
use LaravelJsonApi\Core\Rules\DateTimeIso8601;
use LaravelJsonApi\Core\Rules\ParameterNotSupported;
use LaravelJsonApi\Core\Rules\HasMany;
use LaravelJsonApi\Core\Rules\HasOne;
use function is_null;

class Rule
{

    /**
     * Get a date time ISO8601 validation rule instance.
     *
     * @return DateTimeIso8601
     */
    public static function dateTime(): DateTimeIso8601
    {
        return new DateTimeIso8601();
    }

    /**
     * Get a sparse field sets constraint builder instance.
     *
     * @param string|array|null $allowed
     * @return AllowedFieldSets
     */
    public static function fieldSets($allowed = null): AllowedFieldSets
    {
        return new AllowedFieldSets($allowed);
    }

    /**
     * Get a filter parameter constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedFilterParameters
     */
    public static function filter($allowed = null): AllowedFilterParameters
    {
        return new AllowedFilterParameters(
            is_null($allowed) ? $allowed : Arr::wrap($allowed)
        );
    }

    /**
     * Get an include paths constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedIncludePaths
     */
    public static function includePaths($allowed = null): AllowedIncludePaths
    {
        return new AllowedIncludePaths(
            is_null($allowed) ? $allowed : Arr::wrap($allowed)
        );
    }

    /**
     * Get a not supported parameter rule instance.
     *
     * @param string|null $name
     * @return ParameterNotSupported
     */
    public static function notSupported(string $name = null): ParameterNotSupported
    {
        return new ParameterNotSupported($name);
    }

    /**
     * Get a page parameter constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedPageParameters
     */
    public static function page($allowed = null): AllowedPageParameters
    {
        return new AllowedPageParameters(
            is_null($allowed) ? $allowed : Arr::wrap($allowed)
        );
    }

    /**
     * Get a sort parameter constraint builder instance.
     *
     * @param string|string[]|null $allowed
     * @return AllowedSortParameters
     */
    public static function sort($allowed = null): AllowedSortParameters
    {
        return new AllowedSortParameters(
            is_null($allowed) ? $allowed : Arr::wrap($allowed)
        );
    }

    /**
     * Get a validation rule instance for a to-many relation.
     *
     * @param string ...$types
     * @return HasMany
     */
    public static function toMany(string ...$types): HasMany
    {
        return new HasMany(...$types);
    }

    /**
     * Get a validation rule instance for a to-many relation.
     *
     * @param string ...$types
     * @return HasOne
     */
    public static function toOne(string ...$types): HasOne
    {
        return new HasOne(...$types);
    }
}