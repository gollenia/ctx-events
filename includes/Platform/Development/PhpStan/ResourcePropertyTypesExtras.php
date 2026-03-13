<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Development\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;

final class ResourcePropertyTypesExtras implements Rule
{
    public function __construct(
        private string $resourceInterface = \Contexis\Events\Shared\Presentation\Contracts\Resource::class,
    ) {}

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Class_ || $node->name === null) {
            return [];
        }

        $className = $scope->getClassReflection()?->getName();
        if ($className === null) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null || !$classReflection->implementsInterface($this->resourceInterface)) {
            return [];
        }

        $errors = [];

        foreach ($classReflection->getNativeReflection()->getProperties() as $prop) {
            // nur public props (dein Beispiel)
            if (!$prop->isPublic()) {
                continue;
            }

            $propName = $prop->getName();
            $type = $classReflection->getProperty($propName, $scope)->getReadableType();

            if (!$this->isAllowedType($type)) {
                $errors[] = sprintf(
                    'Resource property %s::$%s has forbidden type: %s',
                    $className,
                    $propName,
                    $type->describe(VerbosityLevel::typeOnly()),
                );
            }
        }

        return $errors;
    }

    private function isAllowedType(Type $type): bool
    {
        // null ok
        if ($type instanceof NullType) return true;

        // scalar ok
        if ($type instanceof StringType
            || $type instanceof IntegerType
            || $type instanceof BooleanType
            || $type instanceof FloatType
        ) return true;

        // union: alle teile müssen ok sein
        if ($type instanceof UnionType) {
            foreach ($type->getTypes() as $inner) {
                if (!$this->isAllowedType($inner)) {
                    return false;
                }
            }
            return true;
        }

        // array: value type muss ok sein
        if ($type instanceof ArrayType) {
            return $this->isAllowedType($type->getItemType());
        }

        // object types: Resource, DateTimeInterface, BackedEnum erlaubt
        if ($type instanceof ObjectType) {
            $class = $type->getClassName();

            if (is_a($class, \DateTimeInterface::class, true)) return true;
            if (is_a($class, \BackedEnum::class, true)) return true;
            if (is_a($class, $this->resourceInterface, true)) return true;

            // VERBOTEN: alles aus Domain
            if (str_contains($class, '\\Domain\\')) return false;

            // Optional: explizite Allowlist für Presentation DTOs/Value types
            if (str_contains($class, '\\Presentation\\')) return true;

            return false;
        }

        // Mixed? bei Ressourcen meistens nein
        if ($type instanceof MixedType) {
            return false;
        }

        return false;
    }
}