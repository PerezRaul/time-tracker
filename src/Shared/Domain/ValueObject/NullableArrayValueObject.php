<?php

declare(strict_types=1);

namespace Src\Shared\Domain\ValueObject;

use Src\Shared\Domain\ArrUtils;
use Src\Shared\Domain\ValueObject\Traits\HasValues;
use Src\Shared\Domain\ValueObject\Types\HasTypes;
use InvalidArgumentException;
use RuntimeException;

use function Lambdish\Phunctional\each;

abstract class NullableArrayValueObject implements NullableArrayValueObjectInterface
{
    use HasValues;
    use HasTypes;

    protected bool $hasValues = false;
    protected int  $minLength = 0;
    private ?array $value;

    public function __construct(?array $value)
    {
        $this->value = null;

        if (null !== $value) {
            $hasValues = !empty(self::values()) && $this->hasValues;

            $this->ensureMinLengthIsOk($value);
            $this->ensureStructureIsValid($value);
            each(function ($valueItem) use ($hasValues) {
                $this->ensureValueIsOfValidType($valueItem);
                if ($hasValues) {
                    $this->ensureIsBetweenAcceptedValues($valueItem);
                }
            }, $value);

            $this->value = $value;
        }
    }

    public function value(): ?array
    {
        return $this->value;
    }

    public function json(): ?string
    {
        if (null === $this->value) {
            return null;
        }

        $json = json_encode($this->value);

        if (false === $json) {
            throw new RuntimeException('Unable to encode array value.');
        }

        return $json;
    }

    public function add(array $items): static
    {
        $value = $this->value();

        if (null === $value) {
            $value = [];
        }

        array_push($value, ...$items);

        return new static($value);
    }

    public function set(string $key, mixed $setValue): static
    {
        $value = $this->value();

        if (null === $value) {
            $value = [];
        }

        ArrUtils::set($value, $key, $setValue);

        return new static($value);
    }

    public function forget(string ...$keys): static
    {
        $value = $this->value();

        if (null === $value) {
            return $this;
        }

        ArrUtils::forget($value, ...$keys);

        $this->ensureMinLengthIsOk($value);

        return new static($value);
    }

    public function forgetValue(mixed ...$values): static
    {
        $value = $this->value();

        if (null === $value) {
            return $this;
        }

        ArrUtils::forgetValue($value, ...$values);

        $this->ensureMinLengthIsOk($value);

        return new static($value);
    }

    public function hasValue(mixed ...$values): bool
    {
        if (null === $this->value()) {
            return false;
        }

        return ArrUtils::hasValue($this->value(), ...$values);
    }

    public function hasAnyValue(mixed ...$values): bool
    {
        if (null === $this->value()) {
            return false;
        }

        return ArrUtils::hasAnyValue($this->value(), ...$values);
    }

    public function count(): ?int
    {
        if (null === $this->value) {
            return null;
        }

        return count($this->value);
    }

    public function isEmpty(): ?bool
    {
        if (null === $this->value) {
            return null;
        }

        return empty($this->value);
    }

    protected function structure(): ?array
    {
        return null;
    }

    private function ensureMinLengthIsOk(array $value): void
    {
        if (count($value) < $this->minLength) {
            throw new InvalidArgumentException(
                sprintf('Value must contains at least %d items.', $this->minLength),
            );
        }
    }

    private function ensureStructureIsValid(array $value): void
    {
        $structure = $this->structure();

        if (null === $structure) {
            return;
        }

        foreach ($structure as $key) {
            if (!ArrUtils::has($value, $key)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Value %s does not match structure %s',
                        json_encode($value),
                        json_encode($structure)),
                );
            }
        }
    }
}
