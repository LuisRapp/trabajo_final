# Gestion Rennova
## Fase II - Analisis del Modelo Conceptual
- Plan de Iteracion: [Codificacion]

## Indice
- Modelo Conceptual
- Lista de conceptos idoneos
- Relaciones entre los conceptos
- Descripcion de los atributos
- Diagrama conceptual
- Comportamiento del sistema
- Diagrama de secuencias a nivel de sistema
- Diagrama de sistema para el caso de uso: Registrar Parte Diario
- Glosario

## Modelo Conceptual

### Lista de conceptos idoneos
Es la lista de conceptos obtenidos de la lista de categorias de conceptos adecuados para incluirlos en la aplicacion, la misma esta sujeta a la restriccion de los requisitos.

**Lote**
- Simbolo: LOT
- Intencion: Representar la unidad de produccion forestal, con ubicacion, especie,
superficie y estado.
- Extension: Incluye datos de propietario, condicion de compra, estado (activo,
cerrado, baja), relacion con cargas y partes diarias.

**Carga**
- Simbolo: CAR
- Intencion: Representar el transporte de madera desde un lote a un destino.
- Extension: Incluye ticket, peso bruto, tara, peso neto, categoria de madera, chofer y
destino.

**Categoria de Madera**
- Simbolo: CAT
- Intencion: Clasificar la madera segun caracteristicas biologicas.
- Extension: Asociada a cargas y reportes de produccion.

**Insumo**
- Simbolo: INS
- Intencion: Representar los materiales necesarios para la operacion.
- Extension: Incluye nombre, descripcion, unidad de medida, proveedor asociado (FK a
Proveedor, opcional), stock (calculado dinamicamente desde
Movimiento_Stock), consumo por maquinaria (via
Mantenimiento_Insumo), consumo por lote (FK a Lote, opcional, si aplica).

**Maquinaria/Equipo**
- Simbolo: MAQ
- Intencion: Representar los equipos productivos utilizados en la extraccion.
- Extension: Incluye identificador, tipo (Categoria_Tipo), estado operativo (Condicion),
Es_Alquilada, Fecha_Inicio_Actividades, relacion con partes diarias (via
Maquinaria_Parte_Diario), costos de alquiler (via
Historico_Costos_Maquinaria), mantenimientos (via Mantenimiento).

**Mantenimiento**
- Simbolo: MAN
- Intencion: Representar las operaciones de mantenimiento de maquinaria.
- Extension: Incluye ordenes programadas, preventivas o correctivas, con fechas,
costos y estado.

**Empleado**
- Simbolo: EMP
- Intencion: Representar al personal de la empresa (operativo o administrativo).
- Extension: Incluye datos personales, historial laboral, rol laboral (FK a Rol_Laboral),
jornales, productividad (via Asignacion y Parte_Diario), sueldos (via
RolValorHistorico), adelantos (FK a Adelanto).

**Chofer**
- Simbolo: CHO
- Intencion: Representar a la persona ajena a la empresa encargada del transporte de
cargas.
- Extension: Asociado a clientes y cargas. Incluye datos personales y laborales.

**Cliente**
- Simbolo: CLI
- Intencion: Representar a quienes compran productos.
- Extension: Incluye razon social, CUIT, direccion y contacto.

**Proveedor**
- Simbolo: PRO
- Intencion: Representar a quienes suministran insumos o servicios.
- Extension: Incluye razon social, CUIT, direccion y contacto.

**Recibo**
- Simbolo: REC
- Intencion: Documento de liquidacion generado para empleados o transacciones.
- Extension: Incluye monto, fecha, empleado/cliente, detalle de conceptos.

**Adelanto**
- Simbolo: ADE
- Intencion: Registrar pagos parciales realizados a empleados.
- Extension: Asociados a empleados y descontados en liquidaciones posteriores.

**Rol**
- Simbolo: ROL
- Intencion: Definir niveles de acceso al sistema.
- Extension: Asociados a usuarios para restringir o habilitar acciones.

**Auditoria**
- Simbolo: AUD
- Intencion: Registrar todas las acciones relevantes del sistema.
- Extension: Incluye usuario, fecha/hora, accion, entidad afectada, resultado.

**Reporte/Indicador (KPI)**
- Simbolo: REP
- Intencion: Representar salidas analiticas para gestion estrategica.
- Extension: Incluye informes financieros, de produccion, productividad y comparativas
planificadas vs. reales.

**Venta**
- Simbolo: VEN
- Intencion: Transaccion comercial de cargas hacia clientes.
- Extension: Incluye cliente, cargas asociadas, fecha, precio, condiciones de pago y
estado.

**Parte diario**
- Simbolo: PD
- Intencion: Registro de trabajo operativo de cada jornada.
- Extension: Incluye lotes intervenidos (FK a Lote), toneladas extraidas (calculadas
desde Carga), empleados utilizados (via Asignacion), maquinaria utilizada
(via Maquinaria_Parte_Diario), Es_Dia_Caido, observaciones.

**Usuario**
- Simbolo: USU
- Intencion: Representar a las personas que acceden al sistema.
- Extension: Incluye identificador, nombre, email, roles y permisos, estado de cuenta.
Nota: La entidad principal de autenticacion es Usuario; el modelo User de Laravel se considera legado y no se utiliza en la aplicacion.

**Historico_Costos_Maquinaria**
- Simbolo: HCM
- Intencion: Registrar los costos historicos de alquiler por tonelada para maquinas
alquiladas.
- Extension: Incluye identificador, maquina asociada (FK a Maquinaria), costo por
tonelada, fechas de vigencia (Fecha_Inicio_Vigencia,
Fecha_Fin_Vigencia).

**Rol_Laboral**
- Simbolo: ROL_LAB
- Intencion: Definir los roles laborales de los empleados (por ejemplo, Motosierrista,
Tractorista) para gestionar pagos.
- Extension: Incluye nombre del rol, relacion con empleados (FK en Empleado), costos
historicos (via RolValorHistorico).

**RolValorHistorico**
- Simbolo: RVH
- Intencion: Definir los roles laborales de los empleados (por ejemplo, Motosierrista,
Tractorista) para gestionar pagos.
- Extension: Incluye nombre del rol, relacion con empleados (FK en Empleado), costos
historicos (via RolValorHistorico).

**Movimiento_Stock**
- Simbolo: MST
- Intencion: Registrar entradas (compras) y salidas (consumo) de insumos para
gestionar el stock.
- Extension: Incluye identificador, insumo (FK a Insumo), tipo de movimiento
(entrada/salida), cantidad, precio unitario (para entradas), precio total
(para entradas), proveedor (FK a Proveedor, opcional), fecha, usuario (FK
a Usuario), mantenimiento (FK a Mantenimiento, opcional), lote (FK a
Lote, opcional), descripcion.

### Conceptos adicionales implementados

**Lote_Tarea**
- Simbolo: LTA
- Intencion: Representar la planificacion de tareas por lote (tipo de tarea y superficie en ha).
- Extension: Incluye id_lote, tipo_tarea, superficie_afectada_ha, estado y observaciones.

**Lote_Inventario**
- Simbolo: LIN
- Intencion: Representar lotes FIFO de stock de insumos.
- Extension: Incluye insumo, proveedor, cantidad, cantidad_disponible, precio_unitario, fecha_compra y estado de agotado.

**Notificacion_Sistema**
- Simbolo: NOT
- Intencion: Registrar notificaciones internas (por ejemplo, mantenimientos).
- Extension: Incluye usuario destino, mensaje, estado de lectura/accion y fecha limite.

**Allocation_Proposal (Propuesta_Asignacion)**
- Simbolo: AP
- Intencion: Sugerir asignacion de recursos por lote o tarea en base a historico.
- Extension: Incluye lote, tarea, empleados, maquinarias, insumos propuestos, estado y metricas.

**Categoria_Cliente_Precio**
- Simbolo: CCP
- Intencion: Definir precios por cliente y categoria de madera.
- Extension: Incluye cliente, categoria_madera, precio_unitario y vigencia.

**Tipo_Maquinaria**
- Simbolo: TMAQ
- Intencion: Clasificar maquinaria y definir umbrales/precios de alquiler.

**Tipo_Mantenimiento**
- Simbolo: TMAN
- Intencion: Clasificar mantenimientos (preventivo/correctivo).

**Unidad_Medida**
- Simbolo: UM
- Intencion: Definir unidades de medida para insumos.

**Mantenimiento_Insumo**
- Simbolo: MIN
- Intencion: Vincular mantenimiento con insumos utilizados y su salida de stock.

**Maquinaria_Parte_Diario**
- Simbolo: MPD
- Intencion: Vincular maquinaria utilizada con partes diarios.

**Asignacion**
- Simbolo: ASN
- Intencion: Vincular empleados con partes diarios (y cargas).

**Configuracion_Sistema**
- Simbolo: CFG
- Intencion: Parametros generales del sistema.
- Extension: Incluye configuracion de horarios y reglas del sistema.

**Configuracion_Notificaciones_Mantenimiento**
- Simbolo: CNM
- Intencion: Configurar usuarios y reglas para notificaciones de mantenimiento.
- Extension: Incluye usuario, canal, preferencias y vigencia.

### Relaciones entre los conceptos
- Carga se asocia con Lote: cada carga referencia un lote de origen (FK ID_Lote).
- Categoria de Madera clasifica Carga: cada carga referencia una categoria (FK ID_Categoria_Madera).
- Chofer se asocia con Carga: cada carga tiene un chofer asignado.
- Insumo se asocia con Movimiento_Stock: las entradas y salidas determinan el stock (FK ID_Insumo).
- Proveedor abastece Insumo: proveedores aparecen en movimientos de entrada.
- Maquinaria se asocia con Parte_Diario, Mantenimiento y Historico_Costos_Maquinaria.
- Mantenimiento se aplica a Maquinaria: cada orden corresponde a una maquina.
- Empleado se asocia con Rol_Laboral, Parte_Diario (via Asignacion), Adelanto y Recibo.
- Adelanto se asocia con Empleado.
- Recibo se asocia con Empleado.
- Venta se asocia con Carga: una venta agrupa una o mas cargas.
- Cliente es parte de una Venta.
- Usuario se asocia con Rol y Auditoria.
- Rol se compone de Permisos.
- Auditoria se asocia con Usuario.
- Reporte se genera con datos de Categoria de Madera, Carga, Lote, Empleado y Maquinaria.
- Parte_Diario vincula Lote, Empleado y Maquinaria.
- Historico_Costos_Maquinaria registra costos para Maquinaria.
- Rol_Laboral define roles para Empleado y costos en RolValorHistorico.
- RolValorHistorico registra costos historicos de Rol_Laboral.
- Movimiento_Stock puede asociarse a Mantenimiento o Lote y registra Usuario.
- Mantenimiento_Insumo vincula Mantenimiento e Insumo y genera Movimiento_Stock.
- Maquinaria_Parte_Diario vincula Maquinaria a Parte_Diario.
- Asignacion vincula Empleado a Parte_Diario.
- Lote se asocia con Lote_Tarea.
- Categoria_Cliente_Precio vincula Cliente y Categoria de Madera.
- Notificacion_Sistema se asocia con Usuario y Mantenimiento.
- Lote_Inventario se asocia con Insumo y Proveedor.
- Allocation_Proposal se asocia con Lote y Lote_Tarea y propone recursos.

### Descripcion de los atributos

**Lote**
- Codigo de lote (ID unico): Identificador interno.
- Propietario (texto): Nombre o razon social del dueno.
- Ubicacion (texto): Localizacion del lote.
- Especie (texto): Tipo de arbol/madera.
- Superficie (ha) (numero decimal): Tamano en hectareas.
- Condicion de compra (enum: vuelo forestal, tn, etc.): Modalidad de adquisicion.
- Precio por lote (decimal): Valor pactado.
- Estado (enum: activo, en_proceso, inactivo, cerrado, baja): Situacion del lote.
- Fecha de compra (fecha): Momento de adquisicion.

**Carga**
- Numero de ticket (texto): Identificador del viaje.
- Fecha_Carga (fecha): Fecha en que se realizo la carga.
- Peso bruto (decimal): Peso total cargado.
- Tara (decimal): Peso del camion vacio.
- Peso neto (decimal): Diferencia entre bruto y tara.
- Categoria de madera (FK a Categoria): Clasificacion de la carga.
- Destino (FK Cliente): Lugar donde se transporta la carga.
- Chofer asignado (FK a Chofer): Responsable del transporte.
- Lote de origen (FK a Lote): Relacion con el lote del cual proviene.
- ID_ParteDiario (FK Parte_Diario): Parte diario asociado.
- ID_Venta (FK Venta, opcional): Puede ser NULL inicialmente y se actualiza al vincular la venta.

**Categoria de Madera**
- ID Categoria (numerico): Identificador unico.
- Nombre (texto): Ej. Fino, Mediano, Grueso.
- Descripcion (texto): Detalle adicional de clasificacion.

**Insumo**
- ID Insumo (numerico): Identificador unico.
- Nombre (texto): Denominacion del insumo.
- Descripcion (texto): Detalles de uso.
- Unidad de medida (texto): Ej. litros, kg, piezas.
- Stock real (decimal): Cantidad disponible.
- Proveedor asociado (FK a Proveedor): Relacion con proveedor.

**Maquinaria**
- ID_Maquinaria (numerico): Identificador unico de la maquina.
- Modelo (texto): Modelo de la maquina.
- Ano (numerico): Ano de fabricacion de la maquina.
- Tipo (texto): Tipo de maquina.
- Condicion (enum: activo, inactivo, en reparacion): Estado actual de la maquina.
- Fecha_Inicio_Actividades (fecha): Fecha en que la maquina comenzo a usarse.
- Es_Alquilada (booleano): Indica si la maquina es alquilada (TRUE) o propia (FALSE).
- Costos_Alquiler (relacion con Historico_Costos_Maquinaria): Costos de alquiler por tonelada con fechas de vigencia.
- Historial_Mantenimientos (relacion con Mantenimiento): Ordenes asociadas a la maquinaria.

**Historico_Costos_Maquinaria**
- ID_Costo (numerico): Identificador unico del registro de costo.
- ID_Maquinaria (FK): Referencia a la maquinaria.
- Costo_Por_Tonelada (decimal): Costo de alquiler por tonelada (si Es_Alquilada = TRUE).
- Fecha_Inicio_Vigencia (fecha): Inicio de vigencia.
- Fecha_Fin_Vigencia (fecha, opcional): Fin de vigencia.

**Maquinaria_Parte_Diario**
- ID_Maquinaria_Parte (numerico): Identificador unico de la relacion.
- ID_Maquinaria (FK): Referencia a Maquinaria.
- ID_Parte_Diario (FK): Referencia a Parte_Diario.

**Empleado**
- ID_Empleado (numerico): Identificador unico del empleado.
- Nombre (texto): Nombre completo del empleado.
- Apellido (texto): Apellido del empleado.
- DNI (texto): Documento de identidad.
- Fecha de nacimiento (fecha): Fecha de nacimiento del empleado.
- ID_Rol (FK): Referencia a RolLaboral.
- Fecha de ingreso (fecha): Fecha en que el empleado comenzo a trabajar.
- Estado (activo/inactivo): Estado del empleado.
- Historial de asignaciones (relacion con Asignaciones): Vinculos con partes diarios.

**Rol Laboral**
- ID_Rol (numerico): Identificador unico del rol laboral.
- Nombre (texto): Nombre del rol laboral.
- Descripcion (texto, opcional): Detalles adicionales del rol.

**RolValorHistorico**
- ID_Valor (numerico): Identificador unico del registro de precio.
- ID_Rol (FK): Referencia a RolLaboral.
- Valor_Por_Ton (decimal): Precio por tonelada para el rol en un periodo.
- Valor_Por_Jornal (decimal, opcional): Precio por jornal (si aplica).
- Fecha_Inicio_Vigencia (fecha): Inicio de vigencia.
- Fecha_Fin_Vigencia (fecha, opcional): Fin de vigencia.

**Asignacion**
- ID_Asignacion (numerico): Identificador unico de la asignacion.
- ID_Empleado (FK): Referencia a Empleado.
- ID_Parte_Diario (FK): Referencia a Parte_Diario.
- Fecha_Asignacion (fecha, opcional): Fecha de asignacion.

**Chofer**
- ID_Chofer (numerico).
- Nombre (texto).
- Apellido (texto).
- DNI (texto numerico).
- Telefono (texto).
- Direccion (texto).
- Cliente asociado (FK a Cliente).

**Cliente**
- ID_Cliente (numerico).
- Razon social (texto).
- CUIT (texto numerico).
- Direccion (texto).
- Telefono (texto).
- Correo electronico (texto).

**Proveedor**
- ID_Proveedor (numerico).
- Razon social (texto).
- CUIT (texto numerico).
- Direccion (texto).
- Telefono (texto).
- Correo electronico (texto).

**Venta**
- ID_Venta (numerico).
- Cliente (FK).
- Fecha de venta (fecha).
- Condiciones de pago (texto).
- Monto total (decimal).
- Estado (activa, cerrada, anulada).

**Recibo**
- ID_Recibo (numerico).
- Empleado (FK).
- Fecha emision (fecha).
- Monto (decimal).
- Detalle de conceptos (texto).

**Adelanto**
- ID_Adelanto (numerico).
- Empleado (FK).
- Fecha (fecha).
- Monto (decimal).

**Mantenimiento**
- ID_Mantenimiento (numerico): Identificador unico.
- ID_Maquinaria (FK): Referencia a la maquina.
- Tipo (enum: preventivo, correctivo, externo): Clasificacion.
- Fecha inicio (fecha).
- Fecha cierre (fecha).
- Descripcion de la tarea (texto).
- Costo_mano_obra (decimal).
- Costo_total (decimal, calculado): Insumos usados + mano de obra.
- Estado (enum: programado, en curso, vencido, completado).

**Mantenimiento_Insumo**
- ID_MantenimientoInsumo (numerico): Identificador unico.
- ID_Mantenimiento (FK): Referencia a Mantenimiento.
- ID_Insumo (FK): Insumo utilizado.
- ID_Movimiento (FK Movimiento_Stock, opcional): Movimiento de salida asociado.
- Cantidad utilizada (decimal).
- Costo_unitario (decimal).
- Subtotal (decimal): Cantidad x costo_unitario.

**Movimiento_Stock**
- ID_Movimiento (numerico): Identificador unico.
- ID_Insumo (FK).
- Tipo_movimiento (entrada/salida).
- Cantidad (decimal).
- Precio_unitario (decimal, para entradas).
- Precio_total (decimal, para entradas).
- ID_Proveedor (FK, opcional).
- Fecha (fecha).
- ID_Usuario (FK).
- ID_Mantenimiento (FK, opcional).
- ID_Lote (FK, opcional).
- Descripcion (texto).

**Parte Diario**
- ID_Parte_Diario (numerico): Identificador unico.
- Fecha (fecha).
- Lote_Asociado (FK): Lote trabajado.
- Maquinas_Utilizadas (relacion con Maquinaria_Parte_Diario).
- Toneladas_Extraidas (decimal, calculada).
- Cargas_Asociadas (relacion con Carga).
- Es_Dia_Caido (booleano).
- Observaciones (texto, opcional).

**Usuario**
- ID_Usuario (numerico).
- Nombre (texto).
- Apellido (texto).
- Email (texto).
- Rol asignado (FK a Rol).
- Estado (activo/inactivo).
- Fecha creacion (fecha).

**Rol**
- ID_Rol (numerico).
- Nombre Rol (texto).
- Permisos asociados (lista de operaciones permitidas).

**Auditoria**
- ID_Accion (numerico).
- Usuario responsable (FK).
- Fecha y hora (timestamp).
- Entidad afectada (texto).
- Accion realizada (alta, baja, modificacion, consulta).
- Resultado (exito/error).

### Diagrama conceptual
Figura 1 - Diagrama conceptual
![Figura 1 - Diagrama conceptual](figuras/analisis-diagrama-conceptual.svg)

## Comportamiento del sistema

### Diagrama de secuencias a nivel de sistema
Figura 2 - Diagrama de Secuencias a Nivel de Sistema
![Figura 2 - Diagrama de Secuencias a Nivel de Sistema](figuras/analisis-diagrama-de-secuencias-a-nivel-de-sistema.svg)

Figura 3 - Diagrama de Secuencias a Nivel de Sistema (Planificar tareas por lote)
![Figura 3 - Diagrama de Secuencias a Nivel de Sistema (Planificar tareas por lote)](figuras/analisis-diagrama-de-secuencias-a-nivel-de-sistema-planificar-tareas-por-lote.svg)

### Diagrama de sistema para el caso de uso: Registrar Parte Diario
Figura 4 - Diagrama de Sistema para el caso de uso: Registrar Parte Diario
![Figura 4 - Diagrama de Sistema para el caso de uso: Registrar Parte Diario](figuras/analisis-diagrama-de-sistema-para-el-caso-de-uso-registrar-parte-diario.svg)

### Diagramas de estados
Figura 5 - Diagrama de Estados - Lote
![Figura 5 - Diagrama de Estados - Lote](figuras/analisis-diagrama-de-estados-lote.svg)

Figura 6 - Diagrama de Estados - Mantenimiento
![Figura 6 - Diagrama de Estados - Mantenimiento](figuras/analisis-diagrama-de-estados-mantenimiento.svg)

Figura 7 - Diagrama de Estados - LoteTarea
![Figura 7 - Diagrama de Estados - LoteTarea](figuras/analisis-diagrama-de-estados-lotetarea.svg)

## Glosario

Lote: unidad productiva forestal con superficie, ubicacion, especie y estado.
Lote_Tarea: planificacion por lote (tipo de tarea, superficie en ha y estado).
Parte Diario: registro operativo diario con empleados, maquinaria, cargas e insumos.
Carga: transporte de madera desde un lote hacia un destino (bruto, tara y neto).
Categoria de Madera: clasificacion usada en cargas y precios.
Maquinaria: equipo productivo registrado para explotacion y mantenimiento.
Mantenimiento: orden de servicio (programado, en curso, vencido o completado).
Insumo: material consumible; su stock se calcula por movimientos.
Movimiento_Stock: entrada o salida de insumos con referencia operativa.
Lote_Inventario: lote FIFO de stock con cantidad disponible y costo.
Empleado: personal de la empresa vinculado a roles laborales y liquidaciones.
Chofer: transportista externo asociado a cargas.
Cliente: comprador de productos/servicios.
Proveedor: suministrador de insumos/servicios.
Venta: transaccion comercial asociada a cargas y cliente.
Usuario: identidad de acceso al sistema (modelo principal de autenticacion).
Rol/Permiso: control de acceso por funciones y modulos.
KPI: indicador de gestion generado a partir de datos operativos.
Notificacion de mantenimiento: alerta interna sobre ordenes programadas, vencidas o pendientes.
Configuracion del sistema: parametros globales de umbrales, horarios y reglas.
Catalogo maestro: tablas de referencia (tipos, unidades, listas de precios).
