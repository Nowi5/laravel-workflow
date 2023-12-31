<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests'
    ]);

    if (! class_exists('Workflow\Models\Basemodel')) {
        class_alias(Model::class, 'Workflow\Models\Basemodel');
    }

    $rectorConfig->parallel(240);

    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::PRIVATIZATION,
        SetList::NAMING,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::CODING_STYLE,
    ]);
};