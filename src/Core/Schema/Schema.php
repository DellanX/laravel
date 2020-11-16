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

namespace LaravelJsonApi\Core\Schema;

use LaravelJsonApi\Contracts\Schema\Attribute;
use LaravelJsonApi\Contracts\Schema\Field;
use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Schema\Schema as SchemaContract;
use LaravelJsonApi\Contracts\Schema\SchemaAware as SchemaAwareContract;
use LaravelJsonApi\Core\Resources\ResourceResolver;
use LogicException;
use function array_keys;
use function sprintf;

abstract class Schema implements SchemaContract, SchemaAwareContract, \IteratorAggregate
{

    use SchemaAware;

    /**
     * @var callable|null
     */
    public static $resourceTypeResolver;

    /**
     * @var callable|null
     */
    protected static $resourceResolver;

    /**
     * The maximum depth of include paths.
     *
     * @var int
     */
    protected int $maxDepth = 1;

    /**
     * @var array|null
     */
    private ?array $fields = null;

    /**
     * Get the resource fields.
     *
     * @return iterable
     */
    abstract public function fields(): iterable;

    /**
     * Specify the callback to use to guess the resource type from the schema class.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessTypeUsing(callable $resolver): void
    {
        static::$resourceTypeResolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public static function type(): string
    {
        $resolver = static::$resourceResolver ?: new TypeResolver();

        return $resolver(static::class);
    }

    /**
     * Specify the callback to use to guess the resource class from the schema class.
     *
     * @param callable $resolver
     * @return void
     */
    public static function guessResourceUsing(callable $resolver): void
    {
        static::$resourceResolver = $resolver;
    }

    /**
     * @inheritDoc
     */
    public static function resource(): string
    {
        $resolver = static::$resourceResolver ?: new ResourceResolver();

        return $resolver(static::class);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        yield from $this->allFields();
    }

    /**
     * @inheritDoc
     */
    public function id(): ID
    {
        $field = $this->allFields()['id'] ?? null;

        if ($field instanceof ID) {
            return $field;
        }

        throw new LogicException('Expecting an id field to exist.');
    }

    /**
     * @inheritDoc
     */
    public function fieldNames(): array
    {
        return array_keys($this->allFields());
    }

    /**
     * @inheritDoc
     */
    public function isField(string $name): bool
    {
        return isset($this->allFields()[$name]);
    }

    /**
     * @inheritDoc
     */
    public function attributes(): iterable
    {
        foreach ($this as $field) {
            if ($field instanceof Attribute) {
                yield $field->name() => $field;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function attribute(string $name): Attribute
    {
        $field = $this->allFields()[$name] ?? null;

        if ($field instanceof Attribute) {
            return $field;
        }

        throw new LogicException(sprintf(
            'Attribute %s does not exist on resource schema %s.',
            $name,
            $this->type()
        ));
    }

    /**
     * @inheritDoc
     */
    public function isAttribute(string $name): bool
    {
        $field = $this->allFields()[$name] ?? null;

        return $field instanceof Attribute;
    }

    /**
     * @inheritDoc
     */
    public function relationships(): iterable
    {
        foreach ($this as $field) {
            if ($field instanceof Relation) {
                yield $field->name() => $field;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function relationship(string $name): Relation
    {
        $field = $this->allFields()[$name] ?? null;

        if ($field instanceof Relation) {
            return $field;
        }

        throw new LogicException(sprintf(
            'Relationship %s does not exist on resource schema %s.',
            $name,
            $this->type()
        ));
    }

    /**
     * @inheritDoc
     */
    public function isRelationship(string $name): bool
    {
        $field = $this->allFields()[$name] ?? null;

        return $field instanceof Relation;
    }

    /**
     * @inheritDoc
     */
    public function includePaths(): iterable
    {
        return new IncludePathIterator(
            $this->schemas(),
            $this,
            $this->maxDepth
        );
    }

    /**
     * @inheritDoc
     */
    public function sparseFields(): iterable
    {
        /** @var Field $field */
        foreach ($this as $field) {
            if ($field->isSparseField()) {
                yield $field->name();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function sortable(): iterable
    {
        if ($this->id()->isSortable()) {
            yield $this->id()->name();
        }

        /** @var Attribute $attr */
        foreach ($this->attributes() as $attr) {
            if ($attr->isSortable()) {
                yield $attr->name();
            }
        }
    }

    /**
     * @return array
     */
    private function allFields(): array
    {
        if (is_array($this->fields)) {
            return $this->fields;
        }

        return $this->fields = collect($this->fields())->keyBy(function (Field $field) {
            if ($field instanceof SchemaAwareContract) {
                $field->withSchemas($this->schemas());
            }

            return $field->name();
        })->sortKeys()->all();
    }
}