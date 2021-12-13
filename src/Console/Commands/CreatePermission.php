<?php


namespace  Iwindy\LaravelPermissionHelper\Console\Commands;


use Illuminate\Console\Command;
use Iwindy\LaravelPermissionHelper\Facade\PermissionHelper;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreatePermission extends Command
{
    /**
     * 命令名称及签名
     *
     * @var string
     */
    protected $signature = 'create-permission';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '生成权限';

    public function handle()
    {
        $nodes = collect(PermissionHelper::generateNodes())->pluck(null, 'name');
        $permission_names = Permission::query()->pluck('name');
        if ($remove = $permission_names->diff($nodes->keys())->toArray()) {
            $roles = Role::all();
            foreach ($roles as $role) {
                $role->revokePermissionTo($remove);
            }
            Permission::query()->whereIn('name',$remove)->delete();
        }

        if ($add = $nodes->keys()->diff($permission_names)) {
            foreach ($add as $value) {
                Permission::create([
                    'guard_name' => $nodes->get($value)['guard_name'],
                    'name'       => $nodes->get($value)['name'],
                    'path'       => $nodes->get($value)['path'],
                ]);
            }
        }

        echo "权限生成完成";
    }
}
