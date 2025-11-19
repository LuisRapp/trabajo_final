<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermisos extends Component
{
    public $selectedRole = null;
    public $selectedUser = null;
    public $rolePermissions = [];
    public $userRoles = [];
    
    // Para crear nuevo rol
    public $newRoleName = '';
    public $busqueda = '';

    public function render()
    {
        $roles = Role::with('permissions')->get();
        
        $permissions = Permission::orderBy('name')->get()->groupBy(function($permission) {
            // Agrupar por módulo (extraer el módulo del nombre del permiso)
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? implode('-', array_slice($parts, 1)) : 'otros';
        });
        
        $query = User::query();
        if ($this->busqueda) {
            $query->where('name', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('email', 'ilike', '%' . $this->busqueda . '%');
        }
        $users = $query->with('roles')->get();
        
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
        $user = User::find($userId);
        $this->userRoles = $user ? $user->roles->pluck('name')->toArray() : [];
    }

    public function updateUserRoles()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'Debe seleccionar un usuario');
            return;
        }

        $user = User::find($this->selectedUser);
        $user->syncRoles($this->userRoles);
        
        session()->flash('message', 'Roles del usuario actualizados correctamente');
    }

    public function updatedBusqueda()
    {
        // Livewire volverá a renderizar automáticamente
    }
}
