-- =====================================================
-- ÍNDICES DE OPTIMIZACIÓN - RENNOVA
-- Ejecutar en PostgreSQL para mejorar rendimiento
-- =====================================================

-- Verificar índices existentes antes de crear
-- SELECT * FROM pg_indexes WHERE schemaname = 'public';

-- =====================================================
-- TABLA: lotes
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_lotes_estado ON lotes(estado);
CREATE INDEX IF NOT EXISTS idx_lotes_ubicacion ON lotes(ubicacion);
CREATE INDEX IF NOT EXISTS idx_lotes_coords ON lotes(latitud, longitud) WHERE latitud IS NOT NULL AND longitud IS NOT NULL;

-- =====================================================
-- TABLA: cargas
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_cargas_fecha ON cargas(fecha_carga DESC);
CREATE INDEX IF NOT EXISTS idx_cargas_lote_fecha ON cargas(id_lote, fecha_carga DESC);
CREATE INDEX IF NOT EXISTS idx_cargas_estado ON cargas(estado);

-- =====================================================
-- TABLA: parte_diarios
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_parte_diarios_lote ON parte_diarios(id_lote);
CREATE INDEX IF NOT EXISTS idx_parte_diarios_fecha ON parte_diarios(fecha DESC);
CREATE INDEX IF NOT EXISTS idx_parte_diarios_lote_fecha ON parte_diarios(id_lote, fecha DESC);

-- =====================================================
-- TABLA: mantenimientos
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_mantenimientos_maquinaria ON mantenimientos(id_maquinaria);
CREATE INDEX IF NOT EXISTS idx_mantenimientos_estado ON mantenimientos(estado);
CREATE INDEX IF NOT EXISTS idx_mantenimientos_maq_estado ON mantenimientos(id_maquinaria, estado);
CREATE INDEX IF NOT EXISTS idx_mantenimientos_fecha_inicio ON mantenimientos(fecha_inicio DESC);

-- =====================================================
-- TABLA: maquinarias
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_maquinarias_estado ON maquinarias(estado);
CREATE INDEX IF NOT EXISTS idx_maquinarias_tipo ON maquinarias(id_tipo_maquinaria);
CREATE INDEX IF NOT EXISTS idx_maquinarias_umbral ON maquinarias(toneladas_acumuladas, umbral_toneladas) WHERE estado = 'operativa';

-- =====================================================
-- TABLA: empleados
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_empleados_activo ON empleados(activo);
CREATE INDEX IF NOT EXISTS idx_empleados_categoria ON empleados(id_categoria_empleado);

-- =====================================================
-- TABLA: notificacion_sistema
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_notif_usuario ON notificacion_sistema(id_usuario);
CREATE INDEX IF NOT EXISTS idx_notif_leido ON notificacion_sistema(leido);
CREATE INDEX IF NOT EXISTS idx_notif_usuario_leido ON notificacion_sistema(id_usuario, leido);
CREATE INDEX IF NOT EXISTS idx_notif_fecha ON notificacion_sistema(created_at DESC);

-- =====================================================
-- TABLA: sessions (para SESSION_DRIVER=database si se usa)
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id) WHERE user_id IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);

-- =====================================================
-- TABLA: cache (para CACHE_STORE=database si se usa)
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_cache_key ON cache(key);
CREATE INDEX IF NOT EXISTS idx_cache_expiration ON cache(expiration);

-- =====================================================
-- TABLA: jobs (para queue)
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_jobs_queue ON jobs(queue, reserved_at);

-- =====================================================
-- TABLA: audits (si está activada auditoría)
-- =====================================================
CREATE INDEX IF NOT EXISTS idx_audits_auditable ON audits(auditable_type, auditable_id);
CREATE INDEX IF NOT EXISTS idx_audits_user ON audits(user_id);
CREATE INDEX IF NOT EXISTS idx_audits_created_at ON audits(created_at DESC);

-- =====================================================
-- VACUUM Y ANALYZE (Optimización física)
-- =====================================================
VACUUM ANALYZE lotes;
VACUUM ANALYZE cargas;
VACUUM ANALYZE parte_diarios;
VACUUM ANALYZE mantenimientos;
VACUUM ANALYZE maquinarias;
VACUUM ANALYZE empleados;
VACUUM ANALYZE notificacion_sistema;
VACUUM ANALYZE sessions;
VACUUM ANALYZE cache;
VACUUM ANALYZE jobs;

-- =====================================================
-- Estadísticas de tablas actualizadas
-- =====================================================
ANALYZE;

-- =====================================================
-- Verificar índices creados
-- =====================================================
SELECT 
    schemaname,
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname = 'public'
ORDER BY tablename, indexname;

-- =====================================================
-- Ver tamaño de tablas e índices
-- =====================================================
SELECT
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS total_size,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) AS table_size,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename) - pg_relation_size(schemaname||'.'||tablename)) AS index_size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
