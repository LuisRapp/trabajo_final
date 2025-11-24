<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConfiguracionNotificacionesMantenimiento extends Component
{
    public $usuariosUmbral = [];
    public $usuariosRecordatorio = [];
    public $usuariosStock = [];
    public $usuarios;

    public function mount()
    {
        $this->usuarios = User::orderBy('name')->get();
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        // Cargar usuarios configurados para cada tipo de notificación
        $config = DB::table('configuracion_notificaciones_mantenimiento')->get();
        
        $this->usuariosUmbral = $config->where('tipo_notificacion', 'umbral')->pluck('user_id')->toArray();
        $this->usuariosRecordatorio = $config->where('tipo_notificacion', 'recordatorio')->pluck('user_id')->toArray();
        $this->usuariosStock = $config->where('tipo_notificacion', 'stock')->pluck('user_id')->toArray();
    }

    public function guardarConfiguracion()
    {
        try {
            DB::beginTransaction();

            // Limpiar configuración anterior
            DB::table('configuracion_notificaciones_mantenimiento')->delete();

            // Insertar nuevas configuraciones
            foreach ($this->usuariosUmbral as $userId) {
                DB::table('configuracion_notificaciones_mantenimiento')->insert([
                    'user_id' => $userId,
                    'tipo_notificacion' => 'umbral',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($this->usuariosRecordatorio as $userId) {
                DB::table('configuracion_notificaciones_mantenimiento')->insert([
                    'user_id' => $userId,
                    'tipo_notificacion' => 'recordatorio',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($this->usuariosStock as $userId) {
                DB::table('configuracion_notificaciones_mantenimiento')->insert([
                    'user_id' => $userId,
                    'tipo_notificacion' => 'stock',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            session()->flash('message', 'Configuración de notificaciones guardada correctamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar configuración: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.configuracion-notificaciones-mantenimiento');
    }
}
