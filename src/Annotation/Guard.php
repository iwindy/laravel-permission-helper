<?php


namespace Iwindy\LaravelPermissionHelper\Annotation;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;
/**
 * @Annotation
 * @NamedArgumentConstructor
 */
class Guard implements NamedArgumentConstructorAnnotation
{
    public $guard;

    public function __construct(string $guard){
        $this->guard = $guard;
    }
}
