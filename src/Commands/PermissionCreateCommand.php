<?php
namespace Houdunwang\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class PermissionCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hd:permission {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成权限数据';

    protected $module;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->module = ucfirst($this->argument('name'));
        $config       = include \Module::getModulePath($this->module).'/config/permission.php';
        app()['cache']->forget('spatie.permission.cache');
        foreach ($config as $guard => $permissions) {
            foreach ($permissions as $accessLists) {
                foreach ($accessLists as $access) {
                    $name = $this->module.'::'.$access;
                    if ( ! Permission::where(['name' => $name, 'guard_name' => $guard])->first()) {
                        Permission::create(['name' => $name,'guard_name'=>$guard]);
                    }
                }
            }
        }
        $this->info("{$this->module} permission install successFully");
    }

    protected function resetTables()
    {
        $ids = \DB::table('permissions')->where('name', 'like', "{$this->module}::%")->pluck('id');
        if ($ids) {
            \DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
            \DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
            \DB::table('permissions')->whereIn('id', $ids)->delete();
        }
    }
}
