<?php

namespace App\Documentation;

use App\Http\Resources\File;
use App\Http\Resources\FileGroups;
use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Type\ObjectType as InferredObjectType;
use Dedoc\Scramble\Support\Type\Type;

class FileGroupsTypeExtension extends TypeToSchemaExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type->isInstanceOf(FileGroups::class);
    }

    public function toSchema(Type $type)
    {
        return (new ObjectType)
            ->setDescription('Fichiers regroupés par type_id ; clé untyped pour les fichiers sans type. Dans chaque groupe : position croissante, positions nulles à la fin, puis id croissant.')
            ->additionalProperties((new ArrayType)->setItems(
                $this->openApiTransformer->transform(new InferredObjectType(File::class))
            ));
    }
}
