#!/bin/bash
php artisan tinker << 'EOF'
use App\Models\Lote;
$lotes = Lote::where('estado', 'activo')->get();
foreach($lotes as $lote) {
    echo "ID: {$lote->id_lote}, Propietario: {$lote->propietario}, Lat: {$lote->latitud}, Long: {$lote->longitud}\n";
}
exit;
EOF
