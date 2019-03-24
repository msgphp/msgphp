<?php

declare(strict_types=1);

namespace MsgPhp\SA\PHPStan;

use MsgPhp\Domain\Factory\DomainObjectFactory;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class DomainObjectFactoryTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return DomainObjectFactory::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return \in_array($methodReflection->getName(), ['create', 'reference'], true);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());

        if (!isset($methodCall->args[0])) {
            return $parametersAcceptor->getReturnType();
        }

        $argType = $scope->getType($methodCall->args[0]->value);

        if (!$argType instanceof ConstantStringType) {
            return $parametersAcceptor->getReturnType();
        }

        return new ObjectType($argType->getValue());
    }
}
