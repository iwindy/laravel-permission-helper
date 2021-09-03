<?php


namespace Iwindy\LaravelPermissionHelper;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Support\Facades\Route;
use Mnabialek\LaravelVersion\Version;

class PermissionHelper
{
    protected $version;

    public function __construct(Version $version)
    {
        $this->version = $version;
    }

    /**
     * @throws \ReflectionException
     */
    public function generateNodes(): array
    {
        AnnotationRegistry::loadAnnotationClass(Annotation\Permission::class);

        $nodes = [];
        foreach (Route::getRoutes() as $route) {
            $controller = $this->getController($route);
            $method = $this->getMethod($route);
            if (!$controller || $controller == 'Closure' || !$method || $method == 'Closure') {
                continue;
            }

            $refClass = new \ReflectionClass($controller);
            $refMethod = $refClass->getMethod($method);
            $reader = new AnnotationReader();
            $annotation = $reader->getClassAnnotation($refClass, Annotation\Guard::class);
            $perm = $reader->getMethodAnnotation($refMethod, Annotation\Permission::class);

            if (!$annotation || !$perm) {
                continue;
            }

            $nodes[] = [
                'guard_name' => $annotation->guard,
                'path'       => implode('.', $perm->menu),
                'name'       => $this->getPermissionName($route)
            ];
        }

        return $nodes;
    }

    public function getFullNodes($nodes): array
    {
        $new_notes = [];
        foreach ($nodes as $item) {
            $path = explode('.', $item['path']);
            $level_count = count($path);
            $str = '';
            foreach ($path as $k => $v) {
                $name = lang('permissions.' . $v);
                if ($level_count != $k + 1) {
                    $str = trim($str . '.' . $v, '.');
                    $new_notes[$str]['guard_name'] = $item['guard_name'];
                    $new_notes[$str]['path'] = $str;
                    $new_notes[$str]['label'] = $name;
                } else {
                    $new_notes[$item['path']] = [
                        'guard_name' => $item['guard_name'],
                        'path'       => $item['path'],
                        'label'      => $name,
                        'name'       => $item['name']
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

    protected function getController($route)
    {
        if ($this->version->isLaravel()) {
            $action = $route->getAction();
            if ($action['prefix'] == '_ignition') {
                return false;
            }
            if (isset($action['controller'])) {
                return $route->getController();
            }
        } else {
            if (empty($route['action']['uses'])) {
                return 'Closure';
            }

            return current(explode("@", $route['action']['uses']));

        }
        return 'Closure';
    }

    protected function getMethod($route)
    {
        if ($this->version->isLaravel()) {
            return $route->getActionMethod();
        } else {
            if (!empty($route['action']['uses'])) {
                $data = $route['action']['uses'];
                if (($pos = strpos($data, "@")) !== false) {
                    return substr($data, $pos + 1);
                }
            }
            return false;
        }
    }

    protected function getPermissionName($route)
    {
        if ($this->version->isLaravel()) {
            if (in_array('GET', $route->methods())) {
                return 'GET:' . $route->uri();
            }
            return $route->methods()[0] . ':' . $route->uri();
        } else {
            return $route['method'] . ':' . $route['uri'];
        }
    }
}
