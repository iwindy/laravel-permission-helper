<?php


namespace Iwindy\LaravelPermissionHelper\Facade;


use Illuminate\Support\Facades\Facade;

/**
 * Class Permission
 * @mixin \Iwindy\LaravelPermissionHelper\PermissionHelper
 * @method static generateNodes()
 * @method static getFullNodes(array $node)
 * @method static getNodesTree(array $node)
 */
class PermissionHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Iwindy\LaravelPermissionHelper\PermissionHelper::class;
    }
}
