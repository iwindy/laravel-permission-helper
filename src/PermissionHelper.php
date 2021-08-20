<?php


namespace Iwindy\LaravelPermissionHelper;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Support\Facades\Route;

class PermissionHelper
{
    /**
     * @throws \ReflectionException
     */
    public function generateNodes(): array
    {
        AnnotationRegistry::loadAnnotationClass(Annotation\Permission::class);

        $nodes = [];
        foreach (Route::getRoutes() as $route) {
            $controller = $route->getActionName();
            if ($route->getActionMethod() == 'Closure' || !strpos($controller, '@')) {
                continue;
            }
            list($class, $method) = explode('@', $controller);

            $refClass = new \ReflectionClass($class);
            $refMethod = $refClass->getMethod($method);
            $reader = new AnnotationReader();
            $perm = $reader->getMethodAnnotation($refMethod, Annotation\Permission::class);
            $annotation = $reader->getClassAnnotation($refClass, Annotation\Guard::class);

            $nodes[] = [
                'guard' => $annotation->guard,
                'menu'  => implode('.', $perm->menu),
                'name'  => implode('|', $route->methods()) . ':' . $route->uri()
            ];
        }

        return $nodes;
    }

    public function getFullNodes($nodes): array
    {
        $new_notes = [];
        foreach ($nodes as $item) {
            $path = explode('.', $item['menu']);
            $level_count = count($path);
            $str = '';
            foreach ($path as $k => $v) {
                $name = lang('permissions.' . $v);
                if ($level_count != $k + 1) {
                    $str = trim($str . '.' . $v, '.');
                    $new_notes[$str]['guard'] = $item['guard'];
                    $new_notes[$str]['menu'] = $str;
                    $new_notes[$str]['show_name'] = $name;
                } else {
                    $new_notes[$item['menu']] = [
                        'guard'      => $item['guard'],
                        'menu'      => $item['menu'],
                        'show_name' => $name,
                        'name'      => $item['name']
                    ];
                }
            }
        }
        return $new_notes;
    }

    public function getNodesTree($nodes): array
    {
        $tree = [];
        foreach ($nodes as $key => $value) {
            if (strripos($key, '.')) {
                $pkey = substr($key, 0, strripos($key, '.'));
                $nodes[$pkey]['children'][] = &$nodes[$key];
            } else {
                $tree[] = &$nodes[$key];
            }
        }
        return $tree;
    }
}
