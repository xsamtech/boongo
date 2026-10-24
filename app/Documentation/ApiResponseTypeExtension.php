<?php

namespace App\Documentation;

use App\Http\Controllers\API\BaseController;
use Dedoc\Scramble\Infer\Extensions\Event\MethodCallEvent;
use Dedoc\Scramble\Infer\Extensions\MethodReturnTypeExtension;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\KeyedArrayType;
use Dedoc\Scramble\Support\Type\Literal\LiteralBooleanType;
use Dedoc\Scramble\Support\Type\Literal\LiteralIntegerType;
use Dedoc\Scramble\Support\Type\NullType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\UnknownType;
use Illuminate\Http\JsonResponse;

/** Describes the shared response helpers without executing controllers or resources. */
class ApiResponseTypeExtension implements MethodReturnTypeExtension
{
    public function shouldHandle(ObjectType $type): bool
    {
        return $type->isInstanceOf(BaseController::class);
    }

    public function getMethodReturnType(MethodCallEvent $event): ?Type
    {
        if ($event->name === 'handleResponse') {
            $items = [
                new ArrayItemType_('success', new LiteralBooleanType(true)),
                new ArrayItemType_('message', new StringType),
                new ArrayItemType_('data', $event->getArg('result', 0, new UnknownType)),
                new ArrayItemType_('ad', $event->getArg('ad', 4, new NullType)),
            ];

            foreach (['lastPage' => 2, 'count' => 3] as $name => $position) {
                $value = $event->getArg($name, $position, new NullType);
                if (! $value instanceof NullType) {
                    $items[] = new ArrayItemType_($name, $value);
                }
            }

            return new Generic(JsonResponse::class, [new KeyedArrayType($items), new LiteralIntegerType(200)]);
        }

        if ($event->name === 'handleError') {
            $message = $event->getArg('errorMsg', 1, new KeyedArrayType);
            $hasDetails = ! ($message instanceof KeyedArrayType && $message->items === []);
            $items = [new ArrayItemType_('success', new LiteralBooleanType(false))];
            if ($hasDetails) {
                $items[] = new ArrayItemType_('data', $event->getArg('error', 0, new UnknownType));
            }
            $items[] = new ArrayItemType_('message', $hasDetails ? $message : $event->getArg('error', 0, new StringType));

            return new Generic(JsonResponse::class, [
                new KeyedArrayType($items),
                $event->getArg('code', 2, new LiteralIntegerType(404)),
            ]);
        }

        return null;
    }
}
