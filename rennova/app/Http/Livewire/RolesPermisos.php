<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Usuario;

class RolesPermisos extends Component
{
    public $activeTab = 'roles';
    public $selectedRole = null;
    public $selectedUser = null;
    public $rolePermissions = [];
    public $userRoles = [];
    public $userModelClassName = null;
    
    // Para crear nuevo rol
    public $newRoleName = '';
    public $busqueda = '';

    protected function userModelClass(): string
    {
        $model = config('auth.providers.users.model');

        if (is_string($model) && class_exists($model)) {
            return $model;
        }

        return User::class;
    }

    protected function userSearchColumns(string $userModel): array
    {
        if ($userModel === Usuario::class) {
            return ['nombre', 'apellido', 'email'];
        }

        return ['name', 'email'];
    }

    protected function userSearchOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    public function displayUserName($user): string
    {
        if ($user instanceof Usuario) {
            return trim($user->nombre . ' ' . $user->apellido);
        }

        return (string) ($user->name ?? $user->email ?? '');
    }

    public function render()
    {
        $roles = Role::with('permissions')->get();
        
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            // Agrupar por módulo (extraer el módulo del nombre del permiso)
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? implode('-', array_slice($parts, 1)) : 'otros';
        });
        
        $userModel = $this->userModelClass();
        $query = $userModel::query();
        if ($this->busqueda) {
            $columns = $this->userSearchColumns($userModel);
            $operator = $this->userSearchOperator();
            $query->where(function ($query) use ($columns, $operator) {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($column, $operator, '%' . $this->busqueda . '%');
                }
            });
        }
        $users = $query->with('roles')->get();

        if ($users->isEmpty() && $userModel !== Usuario::class && class_exists(Usuario::class)) {
            $userModel = Usuario::class;
            $query = $userModel::query();
            if ($this->busqueda) {
                $columns = $this->userSearchColumns($userModel);
                $operator = $this->userSearchOperator();
                $query->where(function ($query) use ($columns, $operator) {
                    foreach ($columns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $query->{$method}($column, $operator, '%' . $this->busqueda . '%');
                    }
                });
            }
            $users = $query->with('roles')->get();
        }

        $this->userModelClassName = $userModel;
        
        return view('livewire.roles-permisos', compact('roles', 'permissions', 'users'));
    }

    public function selectRole($roleId)
    {
        $this->selectedRole = $roleId;
        $role = Role::find($roleId);
        $this->rolePermissions = $role ? $role->permissions->pluck('name')->toArray() : [];
    }

    public function updateRolePermissions()
    {
        if (!$this->selectedRole) {
            session()->flash('error', 'Debe seleccionar un rol');
            return;
        }

        $role = Role::find($this->selectedRole);
        $role->syncPermissions($this->rolePermissions);
        
        session()->flash('message', 'Permisos del rol actualizados correctamente');
    }

    public function createRole()
    {
        $this->validate([
            'newRoleName' => 'required|unique:roles,name|min:3'
        ], [
            'newRoleName.required' => 'El nombre del rol es requerido',
            'newRoleName.unique' => 'Ya existe un rol con ese nombre',
            'newRoleName.min' => 'El nombre debe tener al menos 3 caracteres'
        ]);

        Role::create(['name' => $this->newRoleName, 'guard_name' => 'web']);
        
        session()->flash('message', "Rol '{$this->newRoleName}' creado correctamente");
        $this->newRoleName = '';
    }

    public function deleteRole($roleId)
    {
        $role = Role::find($roleId);
        
        if ($role->name === 'Administrador') {
            session()->flash('error', 'No se puede eliminar el rol Administrador');
            return;
        }

        if ($role->users()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un rol que tiene usuarios asignados');
            return;
        }

        $role->delete();
        session()->flash('message', 'Rol eliminado correctamente');
        
        if ($this->selectedRole == $roleId) {
            $this->selectedRole = null;
            $this->rolePermissions = [];
        }
    }

    public function selectUser($userId)
    {
        $this->selectedUser = $userId;
        $userModel = $this->userModelClassName ?: $this->userModelClass();
        $user = $userModel::find($userId);
        $this->userRoles = $user ? $user->roles->pluck('name')->toArray() : [];
    }

    public function updateUserRoles()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'Debe seleccionar un usuario');
            return;
        }

        $userModel = $this->userModelClassName ?: $this->userModelClass();
        $user = $userModel::find($this->selectedUser);
        $user->syncRoles($this->userRoles);
        
        session()->flash('message', 'Roles del usuario actualizados correctamente');
    }

    public function updatedBusqueda()
    {
        // Livewire volverá a renderizar automáticamente
    }
}
