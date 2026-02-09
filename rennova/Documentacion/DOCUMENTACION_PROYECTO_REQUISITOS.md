Iteración –
Equipo
Versión: 00. Equipo
Proyecto
Gestión Rennova
Documento de Requisitos
del Sistema
Versión 00.
Fecha: 01/09/
Realizado por: Rapp Luis
Realizado para: Rennova
Iteración – 1
Equipo
Versión: 00.
Lista de Cambios

Nro Fecha Descripción Autor
0 Dd/mm/aaaa Versión ##,## Autor
1 Dd/mm/aaaa Descripción Cambio Autor

Equipo
Cliente: Rapp Marcelo

Iteración – 1
Equipo
Versión: 00.
Índice de Figuras

Figura 1 - Diagrama de Subsistemas
Figura 2 - Diagrama de Componentes
Figura 3 - Diagrama de Componentes (Detalle por subsistemas)
Figura 4 - Diagrama de Despliegue (Producción)
Figura 5 - Diagrama de Despliegue (Desarrollo)
Figura 6 - Diagrama Caso de Uso del Sistema (Resumen)
Figura 7 - Diagrama Caso Usos Subsistema Producción
Figura 8 - Diagrama Caso Usos Subsistema Maquinaria y Equipos
Figura 9 - Diagrama Caso Usos Subsistema Recursos Humanos
Figura 10 - Diagrama Caso Usos Subsistema Finanzas y Costos
Figura 11 - Diagrama Caso Usos Subsistema Gestión Administrativa

Iteración – 1
Equipo
Versión: 00.
Presentación General
Renova enfrenta la necesidad de crecer en el mercado maderero argentino, en un
contexto donde la industria busca mejorar la eficiencia mediante el control sobre sus
operaciones. Actualmente, la empresa carece de un sistema unificado para gestionar y
auditar sus procesos forestales, logísticos y financieros.
Para responder a estas limitaciones, se desarrollará un sistema de software que
integre las distintas áreas que conforman la empresa, de esta manera se busca que
cubra control sobre aspectos operativos, gestión de personal, estadísticas financieras,
m?tricas financieras derivadas (ingresos, egresos y flujo de caja) y otros conceptos administrativos, para poder lograr centralizar la
información de manera ordenada y útil.

Participantes del Proyecto

Debe contener una lista con todos los participantes en el proyecto:
Desarrolladores: Rapp Luis
Clientes: Rapp Marcelo

Objetivos del sistema

Se debe hacer una lista con los objetivos que se esperan alcanzar con el
software a desarrollar.

OBJ–01 Centralización de registros operativos y administrativos
Descripción El sistema deberá centralizar en una única plataforma los
registros relacionados con la producción, el uso de insumos,
los gastos, los ingresos y el personal, eliminando la dispersión
actual de información en planillas manuales o sistemas
aislados. Esto permitirá mejorar la trazabilidad, reducir errores
y facilitar la toma de decisiones.
Estabilidad Alta
Comentarios Ninguno

OBJ–02 Generación de reportes e indicadores para la gestión
estratégica
Descripción El sistema deberá ser capaz de procesar los datos ingresados ​​y
generar reportes e indicadores visuales basados ​​en KPIs (Key
Performance Indicators) definidos por la empresa. Estos
informes permitirán monitorear el desempeño operativo,
financiero y administrativo, facilitando la toma de decisiones
estratégicas.
Estabilidad Alta
Comentarios Ninguno

Iteración – 1
Equipo
Versión: 00.
OBJ–03 Cálculo automático de pagos al personal
Descripción El sistema deberá calcular de forma automática los pagos
correspondientes al personal, en función de criterios como días
trabajados, tipo de tarea realizada, productividad y otros
factores definidos por la empresa. Este cálculo reducirá
errores manuales y agilizará el proceso administrativo de búsqueda de
líquido.
Estabilidad Alta
Comentarios ninguno

OBJ–04 Cálculo automático de alquiler de maquinaria
Descripción El sistema deberá calcular el costo de alquiler de cada máquina
utilizada en el proceso de extracción, en base a parámetros
como el tipo de máquina, el volumen de producción asociada, y
las tarifas definidas por la empresa. Esto permitirá un
control más preciso de los costos operativos.
Estabilidad Baja
Comentarios ninguno

OBJ–05 Registro completo de auditoría del sistema
Descripción El sistema deberá mantener un registro de auditoría detallado que
registre todas las acciones relevantes realizadas por los
usuarios, tales como ingresos, modificaciones, eliminaciones
de datos, accesos a módulos y cambios en configuraciones.
Este registro deberá incluir información como fecha, hora,
usuario, acción realizada y entidad afectada, con el fin de
garantizar la trazabilidad, seguridad y control de los procesos.
Estabilidad Alta
Comentarios ninguno

Iteración – 1
Equipo
Versión: 00.
Subsistemas del Proyecto

Diagrama de los Subsistemas

Figura 1 - Diagrama de Subsistemas
![Figura 1 - Diagrama de Subsistemas](figuras/req-figura-1-diagrama-de-subsistemas.svg)

Descripción del subsistema

1. Subsistema de Producción
Este subsistema se encarga de administrar y controlar todas las operaciones
relacionadas con la actividad forestal. Integra el registro de lotes de origen,
cargas transportadas y destino final de cada env?o. Adem?s, permite planificar
tareas por lote (tipo y superficie en ha) en un per?odo determinado, asegurar la
trazabilidad de la producción y validar las partes diarias de trabajo.
2. Subsistema de Maquinaria y Equipos
Su propósito es gestionar de manera integral los recursos mecánicos de la
empresa. Permite registrar cada máquina y equipo, controlando su utilización en
el proceso productivo y asociándose a los lotes de extracción correspondientes.
Incorpora el cálculo automático del costo de alquiler en función de los
volúmenes extraídos. Incluye funcionalidades para programar y cerrar órdenes
de mantenimiento preventivo y correctivo, favoreciendo el correcto
funcionamiento de la flota.
3. Subsistema de Recursos Humanos
Este subsistema centraliza la gestión del personal operativo y administrativo de
la empresa. Contempla la administración de empleados, incluyendo datos
personales, historial laboral y asignación de tareas. Automatiza el cálculo de
liquidaciones en base a días trabajados, tipo de tarea realizada, productividad y
posibles adelantos registrados. También permite generar recibos y mantener un
historial detallado de pagos.
4. Subsistema Financiero y de Costos
Su función es consolidar y organizar la información económica de la empresa.
Integra el registro de ingresos, egresos y costos operativos, y calcula el flujo de caja como m?trica derivada. El
subsistema procesa los datos registrados en las operaciones y los transforma en
informes e indicadores estratégicos (KPIs) que permiten analizar la rentabilidad y
eficiencia de las actividades. De esta manera, brinda soporte para la toma de
decisiones en materia de control de gastos, evaluación de inversiones y
planificación de recursos financieros.
5. Subsistema de Gestión Administrativa
Este subsistema articula los procesos de soporte administrativo que
complementan la operación forestal. Incluye la gestión de clientes, proveedores,
asegurando un registro ordenado de las relaciones comerciales de la empresa.

Iteración – 1
Equipo
Versión: 00.
También contempla el control de insumos y stock, garantizando la
disponibilidad de materiales necesarios para la producción y facilitando
auditorías de inventario. Además, incorpora mecanismos de seguridad mediante
la administración de usuarios, roles y permisos, junto con un registro de
auditoría de acciones (log del sistema), que otorgan trazabilidad y control en
cada operación realizada.

Iteración – 1
Equipo
Versión: 00.
Arquitectura General

Descripción general: La solución se organiza en una interfaz web, servicios de aplicación y capa de datos, con integración a servicios externos (clima y correo) y ejecución de tareas programadas.

Figura 2 - Diagrama de Componentes
Descripción: muestra los componentes principales del sistema y sus dependencias (UI, servicios, ORM, base de datos, notificaciones y API de clima).
![Figura 2 - Diagrama de Componentes](figuras/req-figura-2-diagrama-de-componentes.svg)

Figura 3 - Diagrama de Componentes (Detalle por subsistemas)
Descripción: detalla los módulos por subsistema y sus integraciones con reportes/KPIs, notificaciones, scheduler y base de datos.
![Figura 3 - Diagrama de Componentes (Detalle por subsistemas)](figuras/req-figura-3-diagrama-de-componentes-detalle-por-subsistemas.svg)

Figura 4 - Diagrama de Despliegue (Producción)
Descripción: despliegue recomendado para producción con servidor de aplicación, worker/scheduler, base de datos y servicios externos.
![Figura 4 - Diagrama de Despliegue (Producción)](figuras/req-figura-4-diagrama-de-despliegue-produccion.svg)

Figura 5 - Diagrama de Despliegue (Desarrollo)
Descripción: despliegue de desarrollo en un solo equipo con dependencias externas.
![Figura 5 - Diagrama de Despliegue (Desarrollo)](figuras/req-figura-5-diagrama-de-despliegue-desarrollo.svg)

Iteración – 1
Equipo
Versión: 00.
Diagrama de Caso de Uso del Sistema

Figura 6 - Diagrama Caso de Uso del Sistema (Resumen)
![Figura 6 - Diagrama Caso de Uso del Sistema (Resumen)](figuras/req-figura-6-diagrama-caso-de-uso-del-sistema-resumen.svg)

Figura 7 - Diagrama Caso Usos Subsistema Producción
![Figura 7 - Diagrama Caso Usos Subsistema Producción](figuras/req-figura-7-diagrama-caso-usos-subsistema-produccion.svg)

Iteración – 1
Equipo
Versión: 00.
Figura 8 - Diagrama Caso Usos Subsistema Maquinaria y Equipos
![Figura 8 - Diagrama Caso Usos Subsistema Maquinaria y Equipos](figuras/req-figura-8-diagrama-caso-usos-subsistema-maquinaria-y-equipos.svg)


Iteración – 1
Equipo
Versión: 00.
Figura 9 - Diagrama Caso Usos Subsistema Recursos Humanos
![Figura 9 - Diagrama Caso Usos Subsistema Recursos Humanos](figuras/req-figura-9-diagrama-caso-usos-subsistema-recursos-humanos.svg)


Iteración – 1
Equipo
Versión: 00.
Figura 10 - Diagrama Caso Usos Subsistema Finanzas y Costos
![Figura 10 - Diagrama Caso Usos Subsistema Finanzas y Costos](figuras/req-figura-10-diagrama-caso-usos-subsistema-finanzas-y-costos.svg)


Iteración – 1
Equipo
Versión: 00.
Figura 11 - Diagrama Caso Usos Subsistema Gestión Administrativa
![Figura 11 - Diagrama Caso Usos Subsistema Gestión Administrativa](figuras/req-figura-11-diagrama-caso-usos-subsistema-gestion-administrativa.svg)


Equipo
Casos de Usos

65 Planificaci?n de tareas por lote (ha)

        - Iteración –
     - Versión: 00.
Especificación de Requerimientos Índice
Presentación General
Funciones de Sistema Metas
Función Grupo
Atributos del Sistema
Casos de Usos
Fronteras del Sistema

Actores
Casos de Usos
Actor
Diagrama de Casos de Usos
Casos de Usos de Alto Nivel
Casos de Usos Expandidos
Iteración –
Versión: 00.
1 Alta Lote N° Caso de uso Nombre Nivel de Prioridad
2 Baja Lote
3 Modificar Lote
4 Ver lote
5 Alta Insumo
6 Baja Insumo
7 Modificar Insumo
8 Ver insumo
9 Alta Maquinaria
10 Baja Maquinaria
11 Modificar Maquinaria
12 Ver maquinaria
13 Alta Ingreso
14 Ingreso anular
15 Modificar Ingreso
16 Ver Ingreso
20 Ver Egreso
21 Alta Empleado
22 Baja Empleado
23 Modificar Empleado
24 Ver Empleado
25 Alta Cliente
26 Baja Cliente
27 Modificar Cliente
28 Ver Cliente
29 Alta Proveedor
- Iteración –
Versión: 00. Equipo
30 Baja Proveedor
31 Modificar Proveedor
32 Ver Proveedor
33 Alta Cófer
34 Baja Cófer
35 Modificar Chofer
36 Ver Chofer
37 Alta Stock Insumo
38 Baja Stock Insumo
39 Modificar Stock Insumo
40 Ver Stock Insumo
41 Alta Carga
42 Baja Carga
43 Modificar Carga
44 Ver Carga
45 Alta Categoría
46 Baja Categoría
47 Modificar Categoría
48 Ver Categoría
49 Alta Usuario
50 Baja Usuario
51 Modificar Usuario
52 Ver Usuario
53 Alta Adelanto
54 Baja Adelanto
55 Modifica Adelanto
56 Ver Adelanto
57 Generar Reportes
- Iteración –
- Versión: 00. Equipo
58 Generar Recibos
59 Liquidar Pagos
60 Validez Parte diario
61 Cargar Parte diario
62 Cerrar orden de mantenimiento
63 Programar mantenimiento
64 Configurar permisos
65 Planificación de tareas por lote (ha)
66 Gestionar asignaciones y propuestas
67 Configurar notificaciones de mantenimiento
68 Gestionar catálogos y listas de precios
Iteración – 1
Equipo
Versión: 00.
Versión: 00.
Iteración – 1
Equipo
Versión: 00.
Proyecto
Nombre del Proyecto
Documento de Requisitos
del Sistema
Versión 00.
Fecha: 01/09/
Realizado por: Rapp Luis
Realizado para: Rennova
Iteración – 1
Equipo
Versión: 00.
Objetivos de la Iteración

Se debe hacer una lista con los objetivos que se esperan alcanzar con el
software a desarrollar.

OBJ–01 Centralización de registros
Descripción Centralizar los registros operativos y administrativos en un
único sistema, eliminando la dispersión actual en planillas
manuales y mejorando la trazabilidad de la información.
Estabilidad Alta
Comentarios ninguno

OBJ–02 Implementación del Subsistema de Producción
Descripción Incorporar las funcionalidades para registrar lotes, cargas de
madera y categorías de producto, asegurando trazabilidad.
Estabilidad Alta
Comentarios ninguno

OBJ–03 Implementación del Subsistema de Maquinaria y
Equipos
Descripción Desarrollar el registro de máquinas y equipos, cálculo de
costos de alquiler y gestión de mantenimientos preventivos y
correctivos.
Estabilidad Alta
Comentarios ninguno

OBJ–04 Implementación del Subsistema de Recursos Humanos
Descripción Automatizar la liquidación de pagos al personal según
productividad y días trabajados, registrar adelantos y generar
recibos de liquidación.
Estabilidad Alta
Comentarios ninguno

OBJ–05 Implementación del Subsistema Financiero y de Costos
Descripción Integrar el registro de ingresos y egresos, control de costos
operativos e incorporación de reportes e indicadores de
rentabilidad.
Estabilidad Alta
Comentarios ninguno

OBJ–06 Implementación del Subsistema de Gestión
Administrativa
Descripci?n Incorporar la gesti?n de clientes, proveedores y
stock de insumos, asegurando la disponibilidad de materiales
para la producción.
Estabilidad Alta
Comentarios ninguno

Iteración – 1
Equipo
Versión: 00.
OBJ–07 Seguridad y auditoría del sistema
Descripción Configurar usuarios, roles y permisos, incorporando un registro
de auditoría de acciones para garantizar la trazabilidad y control
de los procesos.
Estabilidad Alta
Comentarios ninguno

Iteración – 1
Equipo
Versión: 00.01
Requisitos del Sistema

Requisitos de Información

Debe tener una lista de requisitos de almacenamiento y de restricciones de
información que se haya identificado.

IRQ–01 Información sobre lotes y cargas
Objetivos
asociados
● OBJ–02 Implementación del Subsistema de Producción
Requisitos
asociados
● UC–01 Gestionar lotes
● UC–09 Gestionar cargas
● UC-07 Gestionar Choferes
● UC–19 Registrar carga
● UC–14 Actualizar estado lote
Descripción El sistema deberá almacenar toda la información vinculada
a los lotes y cargas de madera, permitiendo asegurar la
trazabilidad de la producción.
Datos
específicos
● Nombre
● Ubicación
● Especie
● Superficie
● Nro de ticket
● Categoría
● Chofer
● Bruto
● Tara
● Neto
● Destino
Estabilidad Alta
Comentarios
-
Iteración – 1
Equipo
Versión: 00.01
IRQ–02 Información sobre maquinaria y equipos
Objetivos
asociados

● OBJ–03 Implementación del Subsistema de
Maquinaria y Equipos
Requisitos
asociados

● UC–12 Gestionar maquinaria
● UC–20 Cerrar orden de mantenimiento
● UC–21 Programar mantenimiento
Descripción El sistema deberá almacenar los datos relacionados con
las máquinas utilizadas en la producción y su
mantenimiento.

Datos
específicos

● Identificador y tipo de máquina
● Estado operativo
● Fecha de alta en el sistema
● Producción asociada (toneladas extraídas)
● Costo de alquiler por período y por tonelada
● Historial de mantenimientos preventivos y
correctivos
Estabilidad Media

Comentarios

-
Iteración – 1
Equipo
Versión: 00.01
IRQ–03 Información sobre empleados y choferes
Objetivos
asociados

● OBJ–04 Implementación del Subsistema de Recursos
Humanos
Requisitos
asociados

● UC–04 Gestionar empleados
● UC–16 Registrar adelantos
● UC–17 Liquidar pagos
Descripción El sistema deberá almacenar la información del personal
de la empresa y choferes externos, necesaria para la
gestión operativa y de pagos.

Datos
específicos

● Nombre y apellido
● DNI / CUIT
● Categoría laboral o rol
● Historial de asignación de tareas
● Jornales y productividad
● Sueldos liquidados y adelantos
● Datos de contacto
Estabilidad Alta

Comentarios

-
Iteración – 1
Equipo
Versión: 00.01
IRQ–04 Información financiera y de costos
Objetivos
asociados

● OBJ–05 Implementación del Subsistema Financiero y
de Costos
Requisitos
asociados

● UC–03 Gestionar ingresos/egresos
● UC–13 Generar reportes
● UC-15 Generar recibos
Descripción El sistema deberá almacenar información contable y
financiera vinculada a ingresos, egresos y costos de
operación.
Datos
específicos

● Registro de ingresos (ventas de madera,
servicios, etc.)
● Registro de egresos (insumos, sueldos, alquiler de
maquinaria)
● Flujo de caja (métrica derivada)
● Indicadores de costos por lote o período
● Recibos y comprobantes generados
Estabilidad Alta

Comentarios

-
Iteración – 1
Equipo
Versión: 00.01
IRQ–05 Información sobre clientes, proveedores e
insumos
Objetivos
asociados

● OBJ–06 Implementación del Subsistema de Gestión
Administrativa
Requisitos
asociados

● UC–05 Gestionar clientes
● UC–06 Gestionar proveedores
● UC–08 Gestionar stock de insumos
● UC–10 Gestionar categorías
Descripción El sistema deberá almacenar la información de clientes,
proveedores y stock de insumos necesarios para la
operación.

Datos
específicos

● Datos de clientes (razón social, CUIT, dirección,
contacto)
● Datos de proveedores (razón social, CUIT,
dirección, contacto)
● Insumos disponibles en stock
● Consumo de insumos por lote o maquinaria
● Movimientos de inventario
Estabilidad Media

Comentarios

-
Iteración – 1
Equipo
Versión: 00.01
IRQ–06 Información sobre usuarios y permisos
Objetivos
asociados

● OBJ–07 Seguridad y auditoría del sistema
Requisitos
asociados

● UC–11 Gestionar usuarios
● UC–22 Configurar permisos
Descripción El sistema deberá almacenar información relacionada
con la gestión de usuarios y sus niveles de acceso.

Datos
específicos

● Identificador de usuario
● Nombre y apellido
● Rol o perfil asignado
● Permisos y accesos habilitados
● Fecha y hora de creación del usuario
● Estado de la cuenta (activo/inactivo)
Estabilidad Alta

Comentarios

-
Iteración – 1
Equipo
Versión: 00.01
IRQ–07 Información de auditoría del sistema
Objetivos
asociados

● OBJ–07 Seguridad y auditoría del sistema
Requisitos
asociados

● Registro de acciones del sistema
Descripción El sistema deberá mantener un registro detallado de
todas las críticas realizadas por los usuarios.

Datos
específicos

● Usuario responsable de la acción.
● Fecha y hora de la operación.
● Tipo de acción (alta, baja, modificación,
consulta).
● Entidad afectada (lote, empleado, carga, etc.).
● Resultado de la operación (exitosa / fallida).
Estabilidad Alta

Comentarios

Iteración – 1
Equipo
Versión: 00.01
IRQ–08 Información de informes e indicadores
Objetivos
asociados

● OBJ–05 Implementación del Subsistema Financiero y
de Costos
● OBJ–02 Producción
Requisitos
asociados

● UC–13 Generar reportes
UC-24 Planificaci?n de tareas por lote (ha)
Descripción El sistema deberá almacenar y procesar información
necesaria para la generación de informes e indicadores
estratégicos (KPI).

Datos
específicos

● Reportes de producción por lote, categoría y
período
● Indicadores financieros (ingresos, egresos,
rentabilidad)
● Informes de costos operativos por maquinaria o
insumos
● Estadísticas de productividad por empleado
● Comparativas de volúmenes planificados vs.
extraídos
Estabilidad Alta

Comentarios

Iteración – 1
Equipo
Versión: 00.01
IRQ–09 Información de configuraciones y notificaciones
Objetivos
asociados

● OBJ–07 Seguridad y auditoría del sistema
● OBJ–03 Implementación del Subsistema de Maquinaria y Equipos
Requisitos
asociados

● UC–63 Programar mantenimiento
Descripción El sistema deberá almacenar configuraciones globales y de notificaciones.

Datos
específicos

● Umbrales de mantenimiento y parámetros generales del sistema
● Horarios y expresiones de scheduler
● Usuarios suscriptos a notificaciones de mantenimiento
● Historial de notificaciones internas enviadas
Estabilidad Alta

Comentarios

Iteración – 1
Equipo
Versión: 00.01
IRQ–10 Información de asignaciones y propuestas
Objetivos
asociados

● OBJ–02 Implementación del Subsistema de Producción
Requisitos
asociados

● UC-65 Planificación de tareas por lote (ha)
Descripción El sistema deberá almacenar propuestas de asignación y planes de recursos.

Datos
específicos

● Propuestas automáticas por lote/tarea
● Recursos sugeridos (empleados, maquinarias, insumos)
● Estado de propuesta (abierta, aceptada, cerrada)
● Métricas de desempeño asociadas
Estabilidad Media

Comentarios

Iteración – 1
Equipo
Versión: 00.01
Requisitos de Funcionales

RF-01 Gestión de Lotes
Objetivos
Asociados
● OBJ–02 Implementación del Subsistema de
Producción
Requisitos
asociados
● IRQ–01 Información sobre lotes y cargas
Descripción El sistema deberá permitir registrar, modificar, consultar y
eliminar lotes de producción, vinculando datos de origen,
categoría, volúmenes planificados y extraídos.
Estabilidad Alta
Comentarios -
RF-02 Gestión de Cargas
Objetivos
Asociados
● OBJ–02 Implementación del Subsistema de
Producción
Requisitos
asociados
● IRQ–01 Información sobre lotes y cargas
Descripción El sistema deberá registrar cada carga de madera,
incluyendo número de ticket, peso bruto, tara, peso neto,
destino y chofer responsable.
Estabilidad Alta
Comentarios -
RF-03 Planificación de Producción
Objetivos
Asociados
● OBJ–02 Implementación del Subsistema de
Producción
Requisitos
asociados
● IRQ–08 Información de reportes e indicadores
Descripci?n El sistema deber? permitir planificar tareas por lote (tipo de tarea
y superficie en ha) y comparar lo planificado con lo ejecutado
(partes diarios/cargas).
Estabilidad Media
Comentarios -
Iteración – 1
Equipo
Versión: 00.01
RF-04 Gestión de Maquinaria y Equipos

Objetivos
Asociados

● OBJ–03 Implementación del Subsistema de
Maquinaria y Equipos
Requisitos
asociados

● IRQ–02 Información sobre maquinaria y equipos
Descripción El sistema deberá permitir registrar, consultar y modificar
la información de cada máquina o equipo utilizado en la
producción.

Estabilidad Alta

Comentarios -

RF-05 Cálculo de costos de maquinaria

Objetivos
Asociados

● OBJ–03 Implementación del Subsistema de
Maquinaria y Equipos
Requisitos
asociados

● IRQ–02 Información sobre maquinaria y equipos
● IRQ–04 Información financiera y de costos
Descripción El sistema deberá calcular de manera automática el costo
de alquiler de la maquinaria en función de los parámetros
definidos (toneladas extraídas, tarifas por hora o día).

Estabilidad Alta

Comentarios -

RF-06 Gestión de mantenimientos

Objetivos
Asociados

● OBJ–03 Implementación del Subsistema de
Maquinaria y Equipos
Requisitos
asociados

● IRQ–02 Información sobre maquinaria y equipos
Descripción El sistema deberá permitir programar mantenimientos
preventivos y registradores correctivos,
incluyendo fechas de inicio, cierre de orden y costo
asociado.

Estabilidad Baja

Comentarios -

Iteración – 1
Equipo
Versión: 00.01
RF-07 Gestión de empleados

Objetivos
Asociados

● OBJ–04 Implementación del Subsistema de
Recursos Humanos
Requisitos
asociados

● IRQ–03 Información sobre empleados
Descripción El sistema deberá administrar la información del personal
(empleados y choferes), permitiendo su alta, baja,
consulta y modificación de datos.

Estabilidad Alta

Comentarios -

RF-08 Liquidación de pagos

Objetivos
Asociados

● OBJ–04 Implementación del Subsistema de
Recursos Humanos
Requisitos
asociados

● IRQ–03 Información sobre empleados
● IRQ–04 Información financiera y de costos
Descripción El sistema deberá calcular automáticamente los haberes
del personal en base a días trabajados, productividad y
adelantos registrados.

Estabilidad Alta

Comentarios -

RF-09 Emisión de recibos y adelantos

Objetivos
Asociados

● OBJ–04 Implementación del Subsistema de
Recursos Humanos
Requisitos
asociados

● IRQ–03 Información sobre empleados
Descripción El sistema deberá emitir recibos de pago y registrador
adelantados entregados al personal, manteniendo un
historial de liquidaciones.

Estabilidad Alta

Comentarios -

Iteración – 1
Equipo
Versión: 00.01
RF-10 Gestión financiera (ingresos/egresos)

Objetivos
Asociados

● OBJ–05 Implementación del Subsistema Financiero
y de Costos
Requisitos
asociados

● IRQ–04 Información financiera y de costos
Descripci?n El sistema deber? registrar ingresos y egresos, y calcular
indicadores financieros. El flujo de caja se obtiene como m?trica derivada.

Estabilidad Alta

Comentarios -

RF-11 Generación de informes financieros

Objetivos
Asociados

● OBJ–05 Implementación del Subsistema Financiero
y de Costos
Requisitos
asociados

● IRQ–08 Información de reportes e indicadores
Descripción El sistema deberá generar reportes PDF con estadísticas
forestales y productivas (por lote, período y categoría), además
de indicadores de costos y productividad.

Estabilidad Media

Comentarios Informes financieros avanzados quedan pendientes.

RF-12 Gestión de clientes y proveedores

Objetivos
Asociados

● OBJ–06 Implementación del Subsistema de Gestión
Administrativa
Requisitos
asociados

● IRQ–05 Información sobre clientes, proveedores e
insumos
Descripción El sistema deberá administrar la información de clientes y
proveedores, incluyendo sus datos comerciales b?sicos y contactos.

Estabilidad Media

Comentarios -

Iteración – 1
Equipo
Versión: 00.01
RF-13 Gestión de insumos y stock

Objetivos
Asociados

● OBJ–06 Implementación del Subsistema de Gestión
Administrativa
Requisitos
asociados

● IRQ–05 Información sobre clientes, proveedores e
insumos
Descripción El sistema deberá permitir controlar el stock de insumos,
registrando ingresos, egresos y consumos por lote o
maquinaria.

Estabilidad Alta

Comentarios -

RF-14 Gestión de usuarios y roles

Objetivos
Asociados

● OBJ–07 Seguridad y auditoría del sistema
Requisitos
asociados

● IRQ–06 Información sobre usuarios y permisos
Descripción El sistema deberá permitir crear, modificar y eliminar
usuarios, asignándoles roles y permisos de acceso según
su perfil.

Estabilidad Alta

Comentarios -

RF-15 Configuración de permisos

Objetivos
Asociados

● OBJ–07 Seguridad y auditoría del sistema
Requisitos
asociados

● IRQ–06 Información sobre usuarios y permisos
Descripción El sistema deberá definir y aplicar permisos específicos
para cada usuario o rol, controlando el acceso a módulos
y operaciones críticas.

Estabilidad Alta

Comentarios -

Iteración – 1
Equipo
Versión: 00.01
RF-16 Registro de auditoría

Objetivos
Asociados

● OBJ–07 Seguridad y auditoría del sistema
Requisitos
asociados

● IRQ–07 Información de auditoría del sistema
Descripción El sistema deberá registrar en un registro de auditoría todas
las relevantes realizadas por los usuarios,
incluyendo fecha, hora, acción y acciones afectadas.

Estabilidad Alta

Comentarios -

RF-17 Generación de indicadores de gestión

Objetivos
Asociados

● OBJ–05 Implementación del Subsistema Financiero
y de Costos
● OBJ–02 Producción
Requisitos
asociados

● IRQ–08 Información de reportes e indicadores
Descripción El sistema deberá generar indicadores de productividad,
eficiencia operativa y rentabilidad, basados ​​en los datos
registrados en los diferentes módulos.

Estabilidad Media

Comentarios -

Iteración – 1
Equipo
Versión: 00.01
RF-18 Gestión de asignaciones y propuestas automáticas

Objetivos
Asociados

● OBJ–02 Implementación del Subsistema de Producción
Requisitos
asociados

● IRQ–10 Información de asignaciones y propuestas
Descripción El sistema deberá generar propuestas automáticas de asignación
de recursos por lote/tarea y permitir su revisión, aceptación o
cierre, registrando recursos asociados.

Estabilidad Media

Comentarios -

RF-19 Notificaciones internas y recordatorios

Objetivos
Asociados

● OBJ–07 Seguridad y auditoría del sistema
● OBJ–03 Implementación del Subsistema de Maquinaria y Equipos
Requisitos
asociados

● IRQ–09 Información de configuraciones y notificaciones
Descripción El sistema deberá generar notificaciones internas y recordatorios
relacionados con mantenimientos y eventos relevantes, y permitir
marcar su estado (leída/accionada).

Estabilidad Alta

Comentarios -

RF-20 Configuración del sistema

Objetivos
Asociados

● OBJ–07 Seguridad y auditoría del sistema
● OBJ–03 Implementación del Subsistema de Maquinaria y Equipos
Requisitos
asociados

● IRQ–09 Información de configuraciones y notificaciones
Descripción El sistema deberá permitir configurar parámetros generales
(umbrales de mantenimiento, horarios de verificación y reglas
de notificación).

Estabilidad Alta

Comentarios -

RF-21 Gestión de catálogos y listas de precios

Objetivos
Asociados

● OBJ–06 Implementación del Subsistema de Gestión Administrativa
● OBJ–03 Implementación del Subsistema de Maquinaria y Equipos
Requisitos
asociados

● IRQ–02 Información sobre maquinaria y equipos
● IRQ–05 Información sobre clientes, proveedores e insumos
Descripción El sistema deberá administrar catálogos maestros
(tipos de maquinaria, tipos de mantenimiento, unidades de medida)
y listas de precios por cliente/categoría de madera.

Estabilidad Media

Comentarios -

Iteración – 1
Equipo
Versión: 00.01
Diagrama de Casos de Usos
Ver diagramas actualizados en la secci?n "Diagrama de Caso de Uso del Sistema".
Fronteras del Sistema
![Fronteras del Sistema](figuras/req-fronteras-del-sistema.svg)

Definición de actores

ACT–01 Personal Administrativo
Descripción
Este actor representa al personal de la empresa
encargado de gestionar tareas administrativas, tales
como la carga de datos, gestión de clientes,
proveedores, insumos y emisión de reportes.
Comentarios -
ACT–02 Capataz
Descripción
Este actor representa al encargado de supervisar y
validar las tareas operativas realizadas en campo,
incluyendo la extracción, el registro de cargas y la
verificación de partes diarios.
Comentarios -
ACT–03 Administrador
Descripción
Este actor representa a la persona responsable de la
configuración y mantenimiento del sistema, incluyendo
la gestión de usuarios, asignación de roles y permisos,
y control de la seguridad.
Comentarios -
ACT–04 API clima
Descripción
Este actor representa un servicio externo de terceros
que provee informaci?n meteorol?gica, utilizada como
insumo para el an?lisis clim?tico y recomendaciones
operativas.
Comentarios -
Iteración – 1
Equipo
Versión: 00.01
Casos de uso del Sistema

UC-01 Alta Lote
Actores Personal Administrativo
Descripción Permite registrar un nuevo lote con su identificación y
características básicas para ser utilizado en planificación,
cargas y partes diarios.
Precondición -
Secuencia
Normal
1- El usuario navega a Gestión de Lotes > Registrar
Lote.
2- El sistema muestra formulario con campos: Código
de Lote (único), propietario, ubicación, especie,
superficie (ha) , condición de compra (vuelo forestal,
tn), precio por lote, fecha de compra y
observaciones.
3- El usuario completa los datos obligatorios y
selecciona Guardar.
4- El sistema valida: presencia de obligatorios,formatos
(superficie numérica > 0, fecha válida).
5- El sistema registra el nuevo lote, asigna id interno ,
establece estado inicial = adquirido , guarda
fecha/hora y usuario creador, y genera entrada de
auditoría.
6- El sistema confirma el alta y muestra el detalle del
lote creado.
Postcondición Lote creado y disponible para Planificación , Cargas y
Partes Diarios.
Excepciones 3a. Cancelación : si el usuario cancela, no se guardan
cambios y se vuelve a Gestión de Lotes.
4a. Datos inválidos/incompletos : el sistema marca
campos con error y solicita corrección.
5a. Error de persistencia/BD : el sistema informa el
error, revierte la operación y sugiere reintentar.
Iteración – 1
Equipo
Versión: 00.01
UC-02 Baja Lote

Actores Personales Administrativos

Descripción Este caso de uso permite dar de baja un lote que ya no
está disponible para su explotación. La baja es lógica
(cambio de estado) y no física, para mantener
trazabilidad en históricos de producción e informes.

Precondición El lote existe en el sistema.
El lote se encuentra en estado Inactivo y no tiene cargas
ni partes diarias pendientes.

Secuencia
normal

1- El usuario navega a Gestión de Lotes>Baja de Lote.
2- El sistema muestra el listado de lotes disponibles para
baja.
3- El usuario selecciona el lote a dar de baja.
4- El usuario confirma la baja.
5- El sistema cambia el estado del lote, guarda la fecha
y usuario responsable y registra la operación en la
auditoría.
6- El sistema confirma al usuario que el lote ha sido
dado de baja correctamente.
Postcondición Lote dado de baja y ya no disponible para
Planificación , Cargas y Partes Diarios .

Excepciones 2a- No hay lotes en estado Inactivo: el sistema informa
que no hay lotes disponibles para dar de baja.
5a- Si el usuario cancela no se realizan cambios y se
regresa al menú de Gestión de Lotes.
6a- Si ocurre un error en el sistema informa la falla y
sugiere reintentar

Iteración – 1
Equipo
Versión: 00.01
UC-03 Modificar Lote

Actores Personales Administrativos

Descripción Permite modificar datos de un lote existente para
mantener la información actualizada.

Precondición El lote existe en el sistema, el lote no está dado de baja.

Secuencia
normal

1- El usuario navega a Gestión de Lotes > Modificar
Lote.
2- El sistema muestra listado de lotes disponibles.
3- El usuario selecciona el lote a modificar.
4- El sistema muestra los datos actuales en un
formulario editable.
5- El usuario puede modificar: superficie, condición de
compra, especie, propietario, observaciones.
6- El sistema verifica los cambios: formatos correctos
(superficie numérica > 0).
7- El usuario confirma la modificación.
8- El sistema actualiza los datos del lote, registra fecha
y usuario responsable y genera entrada de auditoria.
9- El sistema confirma al usuario que la modificación se
realizó correctamente.
Postcondición Los datos del lote quedan actualizados en el sistema.

Excepciones 2a- No hay lotes disponibles: el sistema informa que no
existen lotes en estado válido para modificar.
3a- Selección inválida: si el lote no existe o ya está dado
de baja, el sistema rechaza la operación.
6a- Datos inválidos: si el usuario ingresa datos
incompletos o incorrectos, el sistema marca los errores
y solicita corrección.
7a- Cancelación: si el usuario cancela la operación, no
se guardan cambios y se regresa al menú de Gestión de
Lotes.
8a- Si ocurre un error al guardar, el sistema informa y
sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-04 Ver lote

Actores Personales administrativos

Descripción Este caso de uso permite consultar y listar los lotes
existentes en el sistema, ya sea filtrados por estado
(Activo, Cerrado, Baja), propietario, ubicación u otros
criterios. El sistema debe mostrar información básica de
cada lote y permitir acceder al detalle completo de uno
en particular.

Precondición Debe existir al menos un lote registrado en el sistema.

Secuencia
normal

1- El usuario navega a Gestión de Lotes>Consultar
Lotes.
2- El sistema muestra filtros de búsqueda: estado,
propietario, ubicación, fecha de inicio, especie.
3- El usuario completa uno o más filtros y selecciona
Buscar.
4- El sistema procesa la búsqueda y despliega un listado
de lotes coincidentes, mostrando: Código/Nombre del
lote, Propietario, Ubicación, Estado, Superficie.
5- El usuario selecciona un lote específico.
6- El sistema despliega la información detallada del lote.
Postcondición -

Excepciones 2a- Sin filtros aplicados: el sistema devuelve el listado
completo de lotes.
3a- Si no se encuentra ningún lote con los criterios
ingresados ​​el sistema muestra un mensaje “No se
encontraron lotes”.
6a- Si ocurre un error en la búsqueda el sistema informa
la falla y sugiere reintentar.
7a- Si el usuario cancela, el sistema retorna al menú
principal de Gestión de Lotes.

Iteración – 1
Equipo
Versión: 00.01
UC-05 Alta Ingesta

Actores Personales administrativos

Descripción Permite registrar un nuevo insumo en el sistema para
que pueda ser utilizado.

Precondición -

Secuencia
normal

1- El usuario navega a Gestión de Insumos>Registrar
Insumo.
2- El sistema muestra el formulario con los campos:
nombre, descripción, unidad de medida, categoría, stock
inicial, proveedor asociado.
3- El usuario completa los campos requeridos.
4- El sistema valida los formatos (stock inicial>0) y
existencia del proveedor.
5- El usuario confirma la operación.
6- El sistema registra el insumo, asigna identificador
único, registra usuario y fecha y genera un registro en la
auditoría.
7- El sistema confirma que el alta se realizó con éxito.
Postcondición El insumo queda registrado en el sistema.

Excepciones 3a- Si los campos obligatorios están incompletos el
sistema solicita completarlos.
4a- Si el proveedor es inexistente el sistema le informa
al usuario.
5a- Si el usuario cancela la operación se descartan los
cambios y se vuelve al menú Gestionar Insumos.
6a- Si ocurre algún error en el sistema se le notifica al
usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-06 Baja Insumo

Actores Personales Administrativos

Descripción Permite dar de baja un insumo que ya no se utiliza. La
baja es lógica para mantener la trazabilidad.

Precondición El insumo debe estar registrado en el sistema.

Secuencia
normal

1- El usuario navega a Gestión de Insumos>Baja de
Insumo.
2- El sistema muestra un listado de insumos.
3- El usuario selecciona el insumo.
4- El usuario confirma la operación.
5- El sistema cambia el estado del insumo, registra
usuario y fecha y confirma la baja.
Postcondición El insumo es dado de baja.

Excepciones 2a- Si no hay insumos el sistema notifica al usuario.
4a- Si el usuario cancela la baja se descartan los
cambios y se retorna el menú de Gestionar Insumos.
5a- En caso de un error de sistema se le notifica al
usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-07 Modificar Insumo

Actores Personales administrativos

Descripción Permite modificar datos de un insumo existente para
mantener la información actualizada.

Precondición El insumo existe en el sistema y no esté dado de baja.

Secuencia
normal

1- El usuario navega a Gestión de Insumos>Modificar
Insumo.
2- El sistema muestra un listado de los insumos
disponibles.
3- El usuario selecciona el lote a modificar.
4- El sistema muestra los datos actuales en un
formulario editable.
5- El usuario puede modificar: nombre, descripción,
unidad de medida, categoría, stock inicial, proveedor
asociado.
6- El sistema verifica los cambios y que los campos no
estén nulos.
7- El usuario confirma la modificación.
8- El sistema actualiza los datos del insumo, registra la
fecha y el usuario responsable y genera un registro en el
log de auditoría.
9- El sistema confirma al usuario que la modificación se
realizó correctamente.
Postcondición Los datos del insumo quedan actualizados en el sistema.

Excepciones 2a- No hay insumos disponibles: el sistema informa que
no existen insumos para modificar.
3a- Selección inválida: si el insumo no existe o ya está
dado de baja, el sistema rechaza la operación.
6a- Datos inválidos: si el usuario ingresa datos
incompletos o incorrectos, el sistema marca los errores
y solicita corrección.
7a- Cancelación: si el usuario cancela la operación, no
se guardan cambios y se regresa al menú de Gestión de
Insumos.
8a- Si ocurre un error al guardar, el sistema informa y
sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-08 Ver insumo

Actores Personales administrativos

Descripción Este caso de uso permite consultar y listar los insumos
existentes en el sistema, ya sea filtrados por estado
(Activo, Inactivo), proveedor u otros criterios. El sistema
debe mostrar información básica de cada insumo y
permitir acceder al detalle completo de uno en
particular.

Precondición Debe existir al menos un insumo registrado en el
sistema.

Secuencia
normal

1- El usuario navega a Gestión de Insumos>Consultar
Insumos.
2- El sistema muestra filtros de búsqueda: categoría,
proveedor.
3- El usuario completa uno o más filtros y selecciona
Buscar.
4- El sistema procesa la búsqueda y despliega un listado
de los insumos.
5- El usuario selecciona un insumo específico.
6- El sistema despliega la información detallada del
insumo.
Postcondición

Excepciones 2a- Sin filtros aplicados: el sistema devuelve el listado
completo de los insumos.
3a- Si no se encuentra ningún lote con los criterios
ingresados ​​el sistema muestra un mensaje “No se
encontraron Insumos”.
6a- Si ocurre un error en la búsqueda el sistema informa
la falla y sugiere reintentar.
7a- Si el usuario cancela, el sistema retorna al menú
principal de Gestión de Insumos.

Iteración – 1
Equipo
Versión: 00.01
UC-09 Alta Maquinaria

Actores Personales Administrativos.

Descripción Permite registrar una nueva maquinaria con sus
características básicas para ser utilizada.

Precondición -

Secuencia
normal

1- El usuario navega a Gestión de Lotes>Registrar
Maquinaria.
2- El sistema muestra formulario con campos: modelo,
año, categoría, condición, costo alquiler, fecha incio de
actividades.
3- El usuario completa los datos obligatorios y
selecciona guardar.
4- El sistema valida: presencia de campos obligatorios,
formatos.
5- El sistema registra la nueva maquinaria y asigna un
identificador único, genera entra en el log de auditoría.
6- El sistema confirma el alta de la maquinaria y
muestra los detalles
Postcondición Maquinaria creada y disponible para distintas
operaciones.

Excepciones 3a- Cancelación: si el usuario cancela, no se guardan
cambios y se vuelve a Gestión de Lotes.
4a- Datos inválidos/incompletos: el sistema marca
campos con error y solicita corrección.
5a-En caso de error del sistema el sistema informa el
error, revierte la operación y sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-10 Baja Maquinaria

Actores Personales Administrativos

Descripción Permite dar de baja una maquinaria que ya no está
disponible para su uso. La baja se realiza de manera
lógica, para mantener trazabilidad en históricos de
producción e informes.

Precondición La maquinaria existe en el sistema.

Secuencia
normal

1- El usuario navega a Gestión de Maquinaria>Baja de
Maquinaria.
2- El sistema muestra el listado de maquinaria
disponible para baja.
3- El usuario selecciona la maquinaria a dar de baja.
4- El usuario confirma la baja.
5- El sistema cambia el estado de la maquinaria, guarda
la fecha y usuario responsable y registra la operación en
la auditoría.
6- El sistema confirma al usuario que el lote ha sido
dado de baja correctamente
Postcondición Maquinaria dada de baja y ya no está disponible para las
operaciones.

Excepciones 2a- No hay maquinaria disponible para dar de baja y el
sistema notifica al usuario.
5a- Si el usuario cancela la operación no se realizan
cambios y se regresa al menú de Gestión de Maquinaria.
6a- Si ocurre un error en el sistema se informa la falla y
se sugiere reintentar

Iteración – 1
Equipo
Versión: 00.01
UC-11 Modificar Maquinaria

Actores Personales Administrativos

Descripción Permite modificar datos de un lote existente para
mantener la información actualizada.

Precondición La maquinaria debe estar registrada en el sistema, la
maquinaria no está dada de baja.

Secuencia
normal

1- El usuario navega a Gestión de Maquinaria>Modificar
Maquinaria.
2- El sistema muestra listado de maquinaria disponible.
3- El sistema muestra los datos actuales en un
formulario editable.
4- El usuario puede modificar: modelo, año, categoría,
condición, costo alquiler, fecha incio de actividades.
6- El sistema verifica el formato de los datos ingresados.
7- El usuario confirma la modificación.
8- El sistema actualiza los datos de la maquinaria,
registra fecha y usuario responsable y genera una
entrada en el log de auditoría.
9- El sistema confirma al usuario que la modificación se
realizó correctamente.
Postcondición Los datos de la maquinaria quedan actualizados en el
sistema.

Excepciones

Iteración – 1
Equipo
Versión: 00.01
UC-12 Ver maquinaria

Actores Personales administrativos

Descripción Este caso de uso permite consultar y listar la maquinaria
existente en el sistema ya sea filtrada por estado,
propietario u otros criterios. El sistema debe mostrar
información básica de cada maquinaria y permitir
acceder al detalle completo de cada una en particular.

Precondición Debe existir al menos un lote registrado en el sistema.

Secuencia
normal

1- El usuario navega a Gestión de Maquinaria>Consultar
Maquinaria.
2- El sistema muestra filtros de búsqueda.
3- El usuario completa uno o más filtros y selecciona
Buscar.
4- El sistema procesa la búsqueda y despliega un listado
coincidente.
5- El usuario selecciona una maquinaria específica.
6- El sistema despliega la información detallada de la
maquinaria.
Postcondición -

Excepciones 2a- Sin filtros aplicados: el sistema devuelve el listado
completo de maquinaria.
3a- Si no se encuentra ningún lote con los criterios
ingresados ​​el sistema muestra un mensaje “No se
encontró maquinaria”.
6a- Si ocurre un error en la búsqueda el sistema informa
la falla y sugiere reintentar.
7a- Si el usuario cancela, el sistema retorna al menú
principal de Gestión de Maquinaria.

Iteración – 1
Equipo
Versión: 00.01
UC-13 Alta^ Venta^

Actores Personales^ Administrativos^

Descripción Este^ caso^ de^ uso^ permite^ registrar^ en^ el^ sistema^ una^ nueva^
venta. Una venta incluye: cliente, fecha de la operación,
precio y condiciones de pago, relación con una o varias cargas
despachadas.
De esta forma, cada ingreso queda asociado a las cargas que
lo originaron, asegurando trazabilidad desde la producción
hasta la venta final.

Precondición Debe^ existir^ al^ menos^ un^ Cliente^ registrado^ en^ el^ sistema.^

Secuencia
normal

1- El usuario ingresa a Ventas > Alta Venta.
2- El sistema muestra un formulario con campos:
Cliente
Fecha de venta
Condiciones de pago
Selección de cargas asociadas (una o varias).
Observaciones (opcional).
3- El usuario selecciona un Cliente.
4- El usuario selecciona las cargas que forman parte de la
venta.
5- El sistema valida que esas cargas: estén registradas,
no hayan sido previamente asignadas a otra venta,
tengan información de categoría, destino y chofer.
6- El sistema calcula automáticamente el total de la venta
(sumando cargas, aplicando precios según categoría/especie).
7- El usuario confirma el alta.
8- El sistema registra la nueva venta, genera un identificador
único y vincula las cargas seleccionadas.
9- El sistema guarda fecha/hora, usuario responsable.
10- El sistema confirma al usuario que la venta fue registrada
exitosamente.
Postcondición La^ venta^ queda^ registrada^ en^ el^ sistema^ con^ relación^ al^ Cliente^
y las Cargas.

Excepciones 3a.^ Cliente^ inexistente^ →^ el^ sistema^ no^ permite^ continuar.^
4a. No hay cargas registradas → el sistema informa que no
hay cargas disponibles.
5a. Carga ya asociada → si una carga ya pertenece a otra
venta, el sistema bloquea la operación.
6a. Inconsistencias de precios → si falta precio de una
categoría, el sistema alerta al usuario.
7a. Cancelación → si el usuario cancela, no se guarda la
venta.
8a. Error de persistencia/BD → si falla el guardado, el sistema
informa al usuario y reintenta.

Iteración – 1
Equipo
Versión: 00.01
UC-14 Baja^ Venta^

Actores Personales administrativos

Descripción Este caso de uso permite dar de baja una venta
registrada previamente en el sistema.

Precondición Debe existir una Venta registrada.

Secuencia
normal

1- El usuario ingresa a Ventas > Baja Venta.
2- El sistema despliega listado de ventas.
3- El usuario selecciona la venta que desea dar de baja.
4- El sistema muestra el detalle de la venta: cliente,
cargas asociadas, precio total, fecha y condiciones.
5- El usuario confirma la baja.
6- El sistema marca la venta como Anulada (no se
elimina físicamente).
7- Las cargas asociadas a la venta vuelven a quedar
disponibles en el sistema para asignación a otra venta.
8- El sistema guarda fecha/hora y usuario responsable.
9- El sistema confirma al usuario que la venta fue dada
de baja.
Postcondición La venta queda en estado Anulada y no se refleja más
en los ingresos financieros.
Las cargas asociadas quedan nuevamente disponibles
para futuras ventas.

Excepciones 2a. No hay ventas registradas → el sistema informa que
no existen ventas para dar de baja.
5a. Cancelación → el usuario cancela la operación y no
se realizan cambios.
9a. Error de persistencia/BD → el sistema informa la
falla y solicita que se reintente.

Iteración – 1
Equipo
Versión: 00.01
UC-15 Modificar^ Venta^

Actores Personales Administrativos

Descripción Este caso de uso permite modificar los datos de una
venta previamente registrada, siempre que la misma se
encuentre en estado Activa.
Las modificaciones pueden incluir: cambio de cliente,
ajuste de condiciones de pago, actualización de cargas
asociadas o corrección de precios.

Precondición Debe existir una venta registrada en el sistema.

Secuencia
normal

1- El usuario ingresa a Ventas > Modificar Venta.
2- El sistema despliega un listado de ventas disponibles
para modificación.
3- El usuario selecciona la venta a modificar.
4- El sistema muestra el detalle de la venta (cliente,
fecha, condiciones de pago, cargas asociadas, precio
total).
5- El usuario actualiza uno o varios campos: cliente,
condiciones de pago, cargas asociadas (añadir o quitar
cargas disponibles), precios y observaciones.
6- El sistema valida que: el cliente exista y esté activo,
las cargas nuevas seleccionadas no estén asociadas a
otra venta, las cargas quitadas vuelvan a quedar
disponibles.
7- El usuario confirma la modificación.
8- El sistema actualiza la información de la venta,
recalcula el total si corresponde, guarda fecha/hora,
usuario responsable.
9- El sistema confirma que la venta fue modificada
exitosamente.
Postcondición La venta queda actualizada en el sistema.

Excepciones 2a. No existen ventas registradas → el sistema informa
que no hay ventas para modificar.
6a. Carga ya asociada a otra venta → el sistema bloquea
la modificación hasta que se elija otra carga.
6b. Cliente inexistente/inactivo → el sistema no permite
asignar un cliente inválido.
7a. Cancelación → el usuario cancela y no se aplican
cambios.
8a. Error de persistencia/BD → el sistema informa la
falla.

Iteración – 1
Equipo
Versión: 00.01
UC-16 Ver^ Venta^

Actores Personales Administrativos

Descripción Este caso de uso permite consultar el detalle de una o
varias ventas registradas en el sistema.
La visualización incluye: cliente, cargas asociadas,
fecha, precio total, condiciones de pago y estado
(abierta, cerrada, anulada).

Precondición Debe existir al menos una venta registrada en el
sistema.

Secuencia
normal

1- El usuario ingresa a Ventas > Ver Venta.
2- El sistema muestra opciones de búsqueda y filtrado:
cliente, rango de fechas, estado de la venta, carga
asociada.
3- El usuario aplica filtros o solicita listar todas las
ventas.
4- El sistema despliega una lista con las ventas que
cumplen los criterios.
5- El usuario selecciona una venta para ver el detalle.
6- El sistema muestra información completa de la venta:
Postcondición -

Excepciones 2a. No hay ventas registradas → el sistema informa que
no existen ventas.
3a. Filtros inválidos → el sistema alerta al usuario y
solicita corrección.
4a. Sin resultados → el sistema informa que no se
encontraron ventas con los criterios aplicados.

Iteración – 1
Equipo
Versión: 00.01
UC-21 Alto Empleado

Actores Personales Administrativos

Descripción Permite registrar un nuevo empleado en la nómina con
sus datos personales y laborales básicos, para luego ser
utilizado en liquidaciones y asignación de tareas.

Precondición No debe existir otro empleado con el mismo DNI en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestion de Personal > Alta de
Empleado.
2- El sistema despliega un formulario con campos
obligatorios: nombre, apellido, DNI, fecha de
nacimiento, cargo/puesto, fecha de ingreso, precio por
tn, domicilio, teléfono, email.
3- El usuario completa los campos requeridos.
4- El sistema valida: unicidad del dni, formato correcto
de datos, nulidad en campos obligatorios.
5- El usuario confirma la operación.
6- El sistema registra el nuevo empleado en la nómina,
asigna identificador único, guarda fecha/hora y usuario
responsable y genera registro de auditoría.
7- El sistema confirma la operación mostrando el detalle
del empleado creado.
Postcondición El empleado queda registrado en el sistema y disponible
para asignación en partes diarias, liquidaciones y
adelantos.

Excepciones 2a- Si el usuario cancela, no se guardan cambios y se
vuelve al menú de Gestión de Personal.
4a- El sistema marca los campos incorrectos y solicita
corrección.
6a- Si ocurre un error en el registro el sistema informa y
sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-22 Baja Empleado

Actores Personales Administrativos

Descripción Este caso de uso permite dar de baja a un empleado que
ya no pertenece a la empresa. La baja es lógica de
forma que se conserva el historial para consultas,
informes y auditorías.

Precondición El empleado existe en la nómina y no debe tener
liquidaciones pendientes.

Secuencia
normal

1- El usuario selecciona Gestión de Personal>Baja de
Empleado.
2- El sistema despliega el listado de empleados activos.
3- El usuario selecciona el empleado a dar de baja.
4- El sistema valida que el empleado no tenga
liquidaciones, adelantos o asignaciones abiertas.
5- El usuario confirma la baja.
6- El sistema cambia el estado del empleado a Inactivo,
guarda fecha/hora y usuario responsable.
7- El sistema confirma la operación.
Postcondición El empleado queda en estado inactivo, ya no disponible
para liquidaciones, asignación de tareas o partes diarias.

Excepciones 2a- Si no hay empleados activos, el sistema informa que
no existen empleados disponibles para dar de baja.
4a- Si el empleado tiene operaciones pendientes el
sistema rechaza la baja e informa que debe cerrar
liquidaciones antes de proceder.
5a- Si el usuario cancela, no se realizan cambios y se
vuelve al menú.
6a- Si ocurre un error en el sistema informa al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-23 Modificar Empleado

Actores Personales Administrativos

Descripción Permite modificar los datos de un empleado ya
registrado en la nómina. Solo pueden modificarse ciertos
campos y los cambios quedan registrados.

Precondición El empleado existe en la nómina y no está dado de baja.

Secuencia
normal

1- El usuario selecciona Gestion de Personal>Modificar
Empleado.
2- El sistema muestra un listado de empleados activos.
3- El usuario selecciona un empleado activo.
4- El sistema despliega los datos actuales del empleado
en un formulario editable.
5- El usuario modifica los campos editables.
6- El sistema valida que los cambios cumplan con los
formatos obligatorios.
7- El usuario confirma la modificación.
8- El sistema actualiza la información del empleado,
registra usuario y fecha.
9- El sistema confirma que la modificación se realizó
correctamente.
Postcondición Los datos del empleado quedan actualizados en la
nómina.

Excepciones 2a- Si no existen empleados registrados el sistema lo
informa.
6a- Si el formato de los datos es incorrecto, el sistema
marca los errores y solicita corrección.
7a- Si el usuario cancela la operación no se guardan los
cambios y se retorna al menú.
8a- Si ocurre un error en el sistema se informa al
usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-24 Ver Empleado

Actores Personales Administrativos

Descripción al usuario permite consultar la información de los
empleados registrados en la nómina aplicando filtros de
búsqueda y accediendo al detalle de un empleado en
particular.

Precondición Debe existir al menos un empleado registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Personal > Consultar
Empleado.
2- El sistema despliega un formulario de búsqueda con
filtros.
3- El usuario aplica uno o varios filtros y selecciona
Buscar.
4- El sistema muestra un listado de empleados
coincidentes, mostrando los datos básicos: nombre,
apellido, DNI, puesto, estado.
5- El usuario selecciona un empleado específico.
6- El sistema muestra la información detallada: datos
personales, laborales, fecha de ingreso, precio por
producción, liquidaciones asociadas e historial de
modificaciones.
Postcondición -

Excepciones 2a- Sin filtros aplicables el sistema devuelve la lista
completa de empleados activos.
3a- Si no se encuentran coincidencias el sistema informa
al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-25 Alta Cliente

Actores Personales Administrativos

Descripción Permite registrar un nuevo cliente con sus datos para
luego ser asociado a ventas y choferes.

Precondición -

Secuencia
normal

1- El usuario selecciona Gestión de Clientes > Alta de
Cliente.
2- El sistema despliega un formulario con campos
obligatorios: razón social, CUIT, domicilio, teléfono.
3- El usuario completa los campos requeridos.
4- El sistema valida que los campos obligatorios estén
completos y el formato de los datos.
5- El usuario confirma la operación.
6- El sistema registra el nuevo cliente y le asigna un
identificador único.
7- El sistema confirma la operación mostrando el detalle
del cliente.
Postcondición El cliente queda registrado en el sistema y disponible
para las operaciones.

Excepciones 2a- Si el usuario cancela no se guardan cambios y se
vuelve al menú de Gestión de Clientes.
4a- Si los datos no cumplen con los requisitos el sistema
marca el error y solicita su corrección.
6a- Si ocurre algún error el sistema notifica al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-26 Baja Cliente

Actores Personales administrativos

Descripción Este caso de uso permite dar de baja a un cliente. La
baja es lógica, de forma que se conserva el historial
para consultas, informes y auditorías.

Precondición El cliente debe estar registrado en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Cliente > Baja de
Cliente.
2- El sistema despliega el listado de clientes activos.
3- El usuario selecciona el cliente a dar de baja.
4- El usuario confirma la baja.
5- El sistema cambia el estado del cliente a inactivo,
guarda fecha/hora y usuario responsable.
6- El sistema confirma la operación mostrando un
mensaje de éxito.
Postcondición El cliente queda en estado inactivo, ya no disponible
para las operaciones.

Excepciones 2a- Si no hay clientes activos el sistema informa que no
hay clientes para dar de baja.
4a- Si el usuario cancela, no se realizan los cambios y se
vuelve al menú.
6a- En caso de error el sistema notifica al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-27 Modificar Cliente

Actores Personales Administrativos

Descripción Este caso de uso permite modificar los datos de un
cliente ya registrado.

Precondición El cliente debe estar registrado en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Clientes > Modificar
Cliente.
2- El sistema muestra un listado de los clientes activos.
3- El usuario selecciona un cliente.
4- El sistema despliega los datos actuales del cliente en
un formulario editable.
5- El usuario modifica los campos deseados.
6- El sistema valida los cambios.
7- El usuario confirma la modificación.
8- El sistema actualiza la información del cliente,
registra el usuario y fecha.
9- El sistema confirma que la modificación se realizó
correctamente.
Postcondición Los datos del cliente quedan actualizados en la nómina.

Excepciones 2a- Si no hay clientes activos el sistema informa que no
existen registros para modificar.
6a- Si los datos tienen errores el sistema los marca y
solicita corrección.
7a- Si el usuario cancela, no se guardarán los cambios y
se retornará al menú.
8a- Si ocurre un error en el sistema notifica

Iteración – 1
Equipo
Versión: 00.01
UC-28 Ver Cliente

Actores Personales Administrativos

Descripción Permite al usuario consultar la información de los
clientes registrados aplicando filtros de búsqueda y
accediendo al detalle de un empleado en particular

Precondición Debe existir al menos un cliente registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Clientes > Consultar
Cliente.
2- El sistema despliega un formulario de búsqueda con
filtros.
3- El usuario aplica uno o varios filtros y selecciona
Buscar.
4- El sistema muestra un listado de clientes
coincidentes, indicando datos básicos.
5- El usuario selecciona un cliente específico.
6- El sistema muestra la información detallada del
cliente.
Postcondición -

Excepciones 2a- Sin filtros aplicables el sistema devuelve la lista
completa de empleados activos.
3a- Si no se encuentran coincidencias el sistema informa
al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-29 Alta Proveedor

Actores Personales Administrativos

Descripción Permite registrar un nuevo proveedor en el sistema para
luego ser utilizado en el sistema.

Precondición -

Secuencia
normal

1- El usuario selecciona Gestión de Proveedores > Alta
de Proveedor.
2- El sistema despliega un formulario con campos
obligatorios: razón social, CUIT, domicilio, número de
teléfono.
3- El usuario completa los campos requeridos.
4- El sistema valida el formato de los datos y la nulidad
en campos obligatorios.
5- El usuario confirma la operación.
6- El sistema registra el nuevo proveedor, asigna un
identificador único y fecha de inicio de actividades.
7- El sistema confirma la operación mostrando el detalle
del proveedor creado.
Postcondición El proveedor queda registrado y disponible para
operaciones.

Excepciones 2a- Si el usuario cancela, no se guardan cambios y se
vuelve al menú de Gestión de Proveedores.
4a- El sistema marca los campos incorrectos y solicita
corrección.
6a- Si ocurre un error en el registro el sistema informa y
sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-30 Baja Proveedor

Actores Personales Administrativos

Descripción Este caso de uso permite dar de baja a un proveedor
que ya no pertenece a la empresa. La baja es lógica de
forma que se conserva el historial para consultas,
informes y auditorías.

Precondición El proveedor debe estar registrado en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Proveedores>Baja de
Proveedor.
2- El sistema despliega el listado de proveedores
activos.
3- El usuario selecciona el proveedor a dar de baja.
4- El usuario confirma la baja.
5- El sistema cambia el estado del proveedor a Inactivo,
guarda fecha/hora y usuario responsable.
6- El sistema confirma la operación.
Postcondición El proveedor queda en estado inactivo, ya no disponible
para asignarlo a un insumo.

Excepciones 2a- Si no hay proveedores activos, el sistema informa
que no existen proveedores disponibles para dar de
baja.
4a- Si el usuario cancela, no se realizan cambios y se
vuelve al menú.
6a- Si ocurre un error en el sistema informa al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-31 Modificar Proveedor

Actores Personales Administrativos

Descripción Permite modificar los datos de un proveedor ya
registrado. Solo pueden modificarse ciertos campos y los
cambios quedan registrados.

Precondición El proveedor ya está registrado en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Proveedor>Modificar
Proveedor.
2- El sistema muestra un listado de proveedores activos.
3- El usuario selecciona un proveedor activo.
4- El sistema despliega los datos actuales del proveedor
en un formulario editable.
5- El usuario modifica los campos editables.
6- El sistema valida que los cambios cumplan con los
formatos obligatorios.
7- El usuario confirma la modificación.
8- El sistema actualiza la información del proveedor,
registra usuario y fecha.
9- El sistema confirma que la modificación se realizó
correctamente.
Postcondición Los datos del proveedor quedan actualizados.

Excepciones 2a- Si no existen proveedores registrados el sistema lo
informa.
6a- Si el formato de los datos es incorrecto, el sistema
marca los errores y solicita corrección.
7a- Si el usuario cancela la operación no se guardan los
cambios y se retorna al menú.
8a- Si ocurre un error en el sistema se informa al
usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-32 Ver Proveedor

Actores Personales Administrativos

Descripción Permite al usuario consultar la información de los
proveedores registrados en el sistema aplicando filtros
de búsqueda y accediendo al detalle de un empleado en
particular.

Precondición Debe existir al menos un proveedor registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Proveedores>
Consultar Proveedor.
2- El sistema despliega un formulario de búsqueda con
filtros.
3- El usuario aplica uno o varios filtros y selecciona
Buscar.
4- El sistema muestra un listado de proveedores
coincidentes, mostrando los datos básicos
5- El usuario selecciona un empleado específico.
6- El sistema muestra la información detallada.
Postcondición -

Excepciones 2a- Sin filtros aplicables el sistema devuelve la lista
completa de proveedores activos.
3a- Si no se encuentran coincidencias el sistema informa
al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-33 Alta Cófer

Actores Personales Administrativos

Descripción Este caso de uso permite registrar un nuevo chofer en el
sistema. Cada chofer debe estar vinculado
obligatoriamente a un Cliente y contar con sus propios
datos identificatorios y laborales.

Precondición Debe existir un Cliente registrado en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Choferes > Alta de
Chofer.
2- El sistema muestra un formulario con campos:
● Datos personales: nombre, apellido, DNI,
teléfono, dirección.
● Datos laborales: cliente asociado.
3- El usuario completa los campos y selecciona el cliente
correspondiente.
4- El sistema valida el formato de los datos, la existencia
del cliente seleccionado y la nulidad en campos
obligatorios.
5- El usuario confirma la operación.
6- El sistema registra el nuevo chofer vinculado al
cliente, asigna un identificador único y guarda
fecha/hora.
7- El sistema confirma que el chofer fue registrado
exitosamente y muestra el detalle.
Postcondición El chofer queda registrado en el sistema, vinculado a un
cliente y disponible para asignación de cargas.

Excepciones 2a- Si no hay clientes registrados el sistema informa que
no se puede dar de alta un chofer.
5a- Si el usuario cancela la operación, no se guardan
cambios y se vuelve al menú.
6a- Si ocurre algún error en el sistema se le notifica al
usuario y sugiere seleccionar otro.

Iteración – 1
Equipo
Versión: 00.01
UC-34 Baja Chofer

Actores Personales Administrativos

Descripción Este caso de uso permite dar de baja a un chofer
previamente registrado en el sistema. La baja es lógica,
conservando la relación con el cliente y el historial de
cargas asociadas para mantener la trazabilidad.

Precondición El chofer tiene que estar registrado en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Choferes > Baja de
Chofer.
2- El sistema despliega un listado de choferes activos
con su cliente asociado.
3- El usuario selecciona el chofer a dar de baja.
4- El usuario confirma la operación.
5- El sistema cambia el estado del chofer a Inactivo,
guarda fecha/hora y usuario responsable, y genera
registro de auditoría.
6- El sistema confirma que la baja se realizó
exitosamente.
Postcondición El chofer queda en estado Inactivo, no disponible para
nuevas asignaciones de cargas.

Excepciones 2a- Si no hay choferes activos el sistema informa al
usuario.
4a- Si el usuario cancela la operación, no se realizan
cambios y se vuelve al menú principal.
5a- Si ocurre un error el sistema notifica al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-35 Modificar Chofer

Actores Personales Administrativos

Descripción Este caso de uso permite modificar los datos de un
chofer registrado, ya sea en información personal,
laboral o en su relación con un cliente. Todas las
modificaciones quedan registradas en el sistema para
mantener la trazabilidad.

Precondición El chofer debe existir en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Choferes > Modificar
Chofer.
2- El sistema muestra un listado de choferes con su
cliente asociado.
3- El usuario selecciona un chofer.
4- El sistema despliega los datos actuales del chofer en
un formulario editable.
5- El usuario modifica los campos deseados.
6- El sistema valida: obligatoriedad de campos y
formatos.
7- El usuario confirma la operación.
8- El sistema actualiza los datos del chofer, guarda
usuario responsable, fecha y valores modificados.
9- El sistema confirma que la modificación se realizó
correctamente.
Postcondición Los datos del chofer quedan actualizados en el sistema.

Excepciones 2a- Si no hay choferes registrados el sistema informa al
usuario.
6a- Si los datos tienen errores el sistema informa al
usuario.
7a- Si el usuario cancela la operación no se guardan los
cambios.
8a- Si ocurre un error el sistema notifica al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-36 Ver Chofer

Actores Personales Administrativos

Descripción Este caso de uso permite consultar la información de los
choferes registrados en el sistema, visualizando tanto
sus datos propios como la relación con el cliente
correspondiente. Incluye opciones filtradas por
nombre, DNI, licencia, cliente o estado (Activo/Inactivo).

Precondición Debe existir al menos un chofer registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Choferes > Consultar
Chofer.
2- El sistema muestra filtros de búsqueda (nombre, DNI,
licencia, cliente, estado).
3- El usuario aplica uno o varios filtros y selecciona
Buscar.
4- El sistema lista los choferes coincidentes, mostrando
datos básicos: nombre, apellido, DNI, cliente asociado y
estado.
5- El usuario selecciona un chofer.
6- El sistema muestra información detallada del chofer
Postcondición -

Excepciones 2a. Sin filtros aplicados: el sistema devuelve todos los
choferes registrados.
6a- Si ocurre un error el sistema notifica al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-37 Alta^ Stock^ Insumo^

Actores Personales Administrativos

Descripción Este caso de uso permite registrar el ingreso de un
insumo al stock, normalmente asociado a una compra.
Se registra la cantidad, proveedor y costo de
adquisición, actualizando automáticamente el inventario
disponible.

Precondición El insumo debe estar registrado en el sistema

Secuencia
normal

1- El usuario selecciona Gestión de Stock > Alta de
Stock.
2- El sistema despliega un formulario con campos:
insumo, proveedor, cantidad, precio unitario, precio
total, fecha de compra.
3- El usuario completa los campos y selecciona el
insumo y proveedor correspondiente.
4- El sistema valida: obligatoriedad de campos, formatos
correctos (cantidad numérica positiva, precios válidos),
existencia del insumo y del proveedor.
5- El usuario confirma la operación.
6- El sistema registra el movimiento de ingreso de stock,
actualiza la cantidad disponible del insumo, calcula el
costo total, guarda fecha y usuario responsable, y
genera registro de auditoría.
7- El sistema confirma que la compra fue registrada
exitosamente.
Postcondición Se registró el movimiento de stock y se actualiza la
cantidad de stock disponible de ese insumo

Excepciones 2a- Si el insumo no está registrado el sistema informa
que no se puede dar de alta stock de un insumo no
existente.
4a- El sistema marca los errores y solicita corrección.
5a- Si el usuario cancela no se registran cambios
6a- Si ocurre un error el sistema notifica al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-38 Baja^ Stock^ Insumo^

Actores Personales Administrativos

Descripción Este caso de uso permite registrar la salida de un
insumo del stock, ya sea por uso en producción, entrega
a personal, devolución a proveedor o descarte por
vencimiento.

Precondición El insumo debe existir en el sistema y tener stock
suficiente disponible.

Secuencia
normal

1- El usuario selecciona Gestión de Stock > Baja de
Stock.
2- El sistema despliega un formulario con campos:
insumo, cantidad a egresar, motivo de la baja
(producción, descarte, devolución, otro), fecha.
3- El usuario completa los campos y selecciona el
insumo correspondiente.
4- El sistema valida: obligatoriedad de campos, formato
correcto de cantidad (número positivo), existencia del
insumo, disponibilidad de stock suficiente.
5- El usuario confirma la operación.
6- El sistema registra el movimiento de egreso,
descuenta la cantidad correspondiente del stock
disponible.
7- El sistema confirma que la baja se registró
exitosamente.
Postcondición El sistema registra un movimiento de salida de stock.
La cantidad disponible del insumo se reduce en la
cantidad indicada.

Excepciones 2a. No hay insumos registrados: el sistema informa que
no se puede dar de baja stock sin insumos existentes.
4a. Datos incompletos o inválidos: el sistema marca los
errores y solicita corrección.
4b. Cantidad solicitada mayor al stock disponible: el
sistema rechaza la operación e informa el error.
5a. Cancelación: si el usuario cancela, no se registrarán
cambios.
6a- Si ocurre un error el sistema informa al usuario

Iteración – 1
Equipo
Versión: 00.01
UC-39 Modificar Stock Insumo

Actores Personales Administrativos

Descripción Este caso de uso permite modificar un movimiento de
stock previamente registrado (alta o baja). Se utiliza en
casos de errores de carga, cambios en cantidades,
precios o motivos. El sistema actualiza las cantidades de
stock disponibles según corresponda.

Precondición El movimiento de stock debe existir en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Stock > Modificar
Stock.
2- El sistema despliega un listado de movimientos de
stock registrados (con filtros: insumo, fecha, tipo de
movimiento, proveedor).
3- El usuario selecciona el movimiento a modificar.
4- El sistema muestra los datos actuales en un
formulario editable (ej.: cantidad, precio unitario,
motivo, proveedor).
5- El usuario edita los campos requeridos.
6- El sistema valida: obligatoriedad de campos, formatos
correctos (cantidades positivas, precios válidos), que la
modificación no deje stock en negativo.
7- El usuario confirma la operación.
8- El sistema actualiza el movimiento, recalcula el stock
disponible.
9- El sistema confirma que la modificación fue exitosa.
Postcondición El movimiento de stock queda actualizado.

Excepciones 6b. La modificación dejaría stock negativo: el sistema
rechaza la operación.
7a. Si el usuario cancela, no se guardarán cambios.

Iteración – 1
Equipo
Versión: 00.01
UC-40 Ver Stock Insumo

Actores Personales Administrativos

Descripción Este caso de uso permite consultar el estado actual de
los insumos en stock, visualizando cantidades
disponibles, movimientos históricos (altas, bajas,
modificaciones), costos asociados y proveedores.
Incluye opciones de búsqueda y filtros.

Precondición Debe existir al menos un insumo registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Stock > Consultar
Stock.
2- El sistema despliega filtros de búsqueda (ej.: insumo,
proveedor, estado, rango de fechas).
3- El usuario aplica uno o varios filtros (opcional) y
selecciona Buscar.
4- El sistema muestra el listado de insumos coincidentes
con: Código y nombre del insumo ,Cantidad disponible,
Unidad de medida, Proveedor principal, Costo promedio.
5- El usuario selecciona un insumo específico.
6- El sistema despliega información detallada:
Postcondición -

Excepciones 2a- Sin filtros aplicados: el sistema devuelve el listado
completo de insumos con stock.
6a- Si ocurre un error en la consulta, el sistema informa
y sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-41 Alta Carga

Actores Personal Administrativo, Capataz

Descripción Este caso de uso permite registrar una nueva carga en
el sistema, incluyendo datos del origen (lote), destino,
categoría de producto, chofer responsable y pesos
(bruto, tara y neto). El sistema calcula el peso neto y
actualiza los registros de producción asociados.

Precondición - Debe existir al menos un lote registrado y activo.

Debe existir un chofer activo vinculado a un
cliente.
La categoría de producto debe estar registrada en
el sistema.
Secuencia
normal

1- El usuario selecciona Gestión de Producción > Alta
de Carga.
2- El sistema despliega un formulario con los campos:
lote de origen, destino de la carga, categoría (fino,
mediano, grueso), chofer asignado, peso bruto, tara,
fecha.
3- El usuario completa los datos requeridos.
4- El sistema valida: obligatoriedad de campos, formatos
correctos (pesos numéricos y positivos, fechas válidas),
existencia y estado válido del lote, categoría y chofer.
5- El usuario confirma la operación.
6- El sistema registra la carga, actualiza estadísticas de
producción y genera registro de auditoría.
7- El sistema confirma que la carga fue registrada
exitosamente y muestra el detalle.
Postcondición La carga queda registrada en el sistema con todos sus
datos.

Excepciones 2a- No hay lotes/categorías/choferes registrados: el
sistema informa que no se puede registrar la carga.
4a- El sistema marca los errores y solicita corrección.
5- Si el usuario cancela, no se registran cambios y se
regresa al menú.

UC-42 Baja Carga

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite dar de baja una carga
previamente registrada. La baja es lógica para preservar
la trazabilidad y evitar la pérdida de información histórica.

Precondición La carga debe existir en el sistema y estar en estado
Activo.

Secuencia
normal

1- El usuario selecciona Gestión de Producción >
Baja de Carga.
2- El sistema despliega un listado de cargas activas con
datos básicos: lote de origen, destino, chofer, categoría,
peso neto, fecha.
3- El usuario selecciona la carga a dar de baja.
4- El usuario confirma la operación.
5- El sistema cambia el estado de la carga a Inactiva ,
registra fecha/hora y usuario responsable, ajusta
estadísticas de producción asociadas y genera registro
de auditoría.
6- El sistema confirma que la baja se realizó
exitosamente.
Postcondición La carga es dada de baja en el sistema.

Excepciones 2a- No hay cargas activas: el sistema informa que no
existen cargas disponibles para dar de baja.
4a- Si el usuario cancela no se realizan cambios y se
regresa al menú.
6a- Si ocurre un error el sistema notifica al usuario.

UC-43 Modificar Carga

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite modificar los datos de una
carga previamente registrada, como el destino, la
categoría, el chofer o los valores de peso.

Precondición La carga debe existir en el sistema y estar en estado
Activo.

Secuencia
normal

1- El usuario selecciona Gestión de Producción >
Modificar Carga.
2- El sistema despliega un listado de cargas activas con
información resumida: lote, destino, chofer, categoría,
peso neto, fecha.
3- El usuario selecciona la carga a modificar.
4- El sistema muestra un formulario con los datos
actuales de la carga en campos editables.
5- El usuario edita los campos que desea modificar.
6- El sistema valida: obligatoriedad de los
campos,formatos correctos (pesos positivos, fechas
válidas), coherencia de datos (tara < bruto, chofer
activo, categoría válida, lote activo).
7- El usuario confirma la modificación.
8- El sistema actualiza la carga en la base de datos.
9- El sistema confirma que la modificación se realizó
exitosamente y muestra el detalle actualizado.
Postcondición La carga queda registrada con los datos actualizados.

Excepciones 2a. No hay cargas activas: el sistema informa que no
existen registros disponibles para modificar.
6a- Si los datos tienen errores el sistema informa al
usuario.
7a- Si el usuario cancela, no se guardan cambios y se
vuelve al menú.

UC-44 Ver Carga

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite consultar las cargas
registradas en el sistema, aplicando filtros de búsqueda
y accediendo al detalle de cada carga, incluyendo pesos,
origen, destino y trazabilidad.

Precondición Debe existir al menos una carga registrada en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Producción >
Consultar Cargas.
2- El sistema muestra filtros de búsqueda (lote, chofer,
categoría, destino, rango de fechas, estado).
3- El usuario aplica uno o varios filtros (opcional) y
selecciona Buscar.
4- El sistema lista las cargas coincidentes mostrando
datos básicos: lote de origen, destino, categoría, chofer,
peso neto, fecha, estado.
5- El usuario selecciona una carga del listado.
6- El sistema muestra la información detallada:
● Lote de origen.
● Destino de la carga.
● Categoría del producto.
● Chofer y cliente asociado.
● Pesos: bruto, tara y neto.
● Fecha de carga.
● Estado (Activo/Inactivo).
● Auditoría de modificaciones.
Postcondición -

Excepciones 2a. Sin filtros aplicados: el sistema devuelve todas las
cargas registradas
6a- Si ocurre un error el sistema notifica al usuario.

UC-45 Alta Categoría

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Permite registrar una nueva categoría de producto,
asociada a un diámetro, cliente, especie y precio, para
ser utilizada en cargas y planificación de producción.

Precondición El cliente y la especie deben estar previamente
registrados.

Secuencia
normal

1- El usuario selecciona Gestión de Categorías > Alta
de Categoría.
2- El sistema muestra un formulario con campos:
nombre de categoría, diámetro, especie, cliente, precio.
3- El usuario completa los campos requeridos.
4- El sistema valida formatos y existencia de cliente y
especie.
5- El usuario confirma la operación.
6- El sistema registra la categoría, asigna identificador
único.
7- El sistema confirma la operación mostrando el detalle
de la categoría.
Postcondición La categoría queda registrada y disponible para
asignación en cargas y planificación.

Excepciones 4a- El sistema marca los errores y pide al usuario que
los corrija.
5a- Si el usuario cancela la operación se regresa al
menú.
6a- Si ocurre un error el sistema notifica al usuario.

UC-46 Baja Categoría

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Permite dar de baja una categoría registrada. La baja es
lógica (estado = Inactiva) para preservar la trazabilidad en
cargas históricas.

Precondición La categoría existe y está activa.

Secuencia
normal

1- El usuario selecciona Gestión de Categorías > Baja
de Categoría.
2- El sistema muestra un listado de categorías activas.
3- El usuario selecciona una categoría.
4- El usuario confirma.
5- El sistema marca la categoría como inactiva.
6- El sistema confirma la operación.
Postcondición La categoría queda Inactiva.

Excepciones 2a- En caso de que no existan categorías, se activa el
sistema informa al usuario.
5a- Si el usuario cancela la operación no se registran los
cambios y se regresa al menú principal.

UC-47 Modificar Categoría

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos.

Descripción Permite modificar los datos de una categoría ya
registrada en el sistema.

Precondición La categoría debe estar registrada en el sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Categorías >
Modificar Categoría.
2- El sistema muestra un listado de categorías.
3- El usuario selecciona una.
4- El sistema muestra datos actuales en formulario
editable.
5- El usuario modifica los campos necesarios.
6- El sistema valida consistencia y unicidad.
7- El usuario confirma.
8- El sistema actualiza la categoría.
9- El sistema confirma la modificación.
Postcondición Los datos de la categoría quedan actualizados.

Excepciones 2a- Si no hay categorías registradas el sistema informa
al usuario.
6a- Si hay errores en los datos el sistema informa al
usuario y solicita que se corrijan.
7a- Si el usuario cancela la operación se regresa al
menú principal.

UC-48 Ver Categoría

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos.

Descripción Permite consultar las categorías registradas, aplicando
filtros por cliente, especie, diámetro o estado.

Precondición Debe existir al menos una categoría registrada.

Secuencia
normal

1- El usuario selecciona Gestión de Categorías >
Consultar Categorías.
2- El sistema muestra filtros (cliente, especie, diámetro,
estado).
3- El usuario aplica filtros y selecciona Buscar.
4- El sistema lista las categorías coincidentes con:
nombre, diámetro, cliente, especie, precio, estado.
5- El usuario selecciona una.
6- El sistema muestra la información detallada de la
categoría.
7- El sistema registra la consulta en auditoría.
Postcondición -

Excepciones 3a- Si el usuario no aplica filtros el sistema devuelve la
lista completa de categorías.

UC-49 Alta Usuario

Iteración – 1
Equipo
Versión: 00.01
Actores Administrador

Descripción Este caso de uso permite registrar un nuevo usuario del
sistema. El puede estar vinculado opcionalmente
a un empleado, pero también el usuario puede ser un perfil
independiente. Se deben definir sus credenciales y los
roles/permisos asociados.

Precondición El nombre de usuario no debe estar registrado
previamente.

Secuencia
normal

El Administrador selecciona Gestión de Usuarios >
Alta de Usuario.
El sistema despliega un formulario con los campos
requeridos: nombre de usuario, contraseña, permisos
opción de vincular con un empleado existente.
El Administrador completa los campos requeridos.
El sistema valida: unicidad del nombre de usuario,
cumplimiento de políticas de contraseña, validez de los
roles asignados, existencia del empleado.
El Administrador confirma la operación.
El sistema registra el nuevo usuario, asigna identificador
único, guarda fecha/hora y usuario responsable, y
genera registro de auditoría.
El sistema confirma la operación mostrando el detalle
del usuario creado.
Postcondición El nuevo usuario queda registrado en el sistema y puede
autenticarse con las credenciales asignadas.

Excepciones 4a- Si el nombre de usuario ya está en uso el sistema
informa al usuario y solicita uno nuevo.
4b- Contraseña inválida: si no cumple con las políticas
de seguridad (longitud, complejidad), el sistema solicita
corrección.
4c- Rol no válido: si se intenta asignar un rol
inexistente, se rechaza la operación.
5a- Si el usuario cancela la operación no se registran
cambios y se regresa al menú.
6a- Si ocurre un error el sistema notifica al usuario.

UC-50 Baja Usuario

Iteración – 1
Equipo
Versión: 00.01
Actores Administrador

Descripción Este caso de uso permite dar de baja un usuario del
sistema. La baja es lógica (estado = Inactivo), para
mantener histórico de accesos y auditoría. El
dado de baja no podrá autenticarse ni operar en el
sistema.

Precondición El usuario a dar de baja debe existir en el sistema y
estar en estado Activo.

Secuencia
normal

1- El Administrador selecciona Gestión de Usuarios >
Baja de Usuario.
2- El sistema despliega un listado de usuarios activos
con datos básicos: nombre de usuario, rol, estado, si
está o no vinculado a un empleado.
3- El Administrador selecciona el usuario a dar de baja.
4- El sistema valida que el usuario no sea el propio
Administrador autenticado ni el último usuario con rol de
Administrador (para evitar bloqueo total del sistema).
5- El Administrador confirma la operación.
6- El sistema cambia el estado del usuario a Inactivo,
guarda fecha/hora y administrador responsable, y
genera registro de auditoría.
7- El sistema confirma que la baja fue realizada
exitosamente.
Postcondición El usuario queda en estado Inactivo y ya no puede
acceder al sistema.

Excepciones 2a- El sistema informa que no existen usuarios
disponibles para dar de baja.
5a- Si el Administrador cancela, no se realizarán cambios.
6a- Si ocurre un error, el sistema informa y sugiere
reintentar.

UC-51 Modificar Usuario

Iteración – 1
Equipo
Versión: 00.01
Actores Administrador

Descripción Este caso de uso permite modificar los datos de un
usuario registrado en el sistema. Entre las
modificaciones posibles se incluyen: cambio de
contraseña, ajuste de rol/permisos, actualización de
datos de contacto o vinculación/desvinculación con un
empleado.

Precondición El usuario a modificar debe estar registrado en el
sistema.

Secuencia
normal

1- El Administrador selecciona Gestión de Usuarios >
Modificar Usuario.
2- El sistema muestra un listado de usuarios registrados
(activos e inactivos).
3- El Administrador selecciona un usuario para modificar.
4- El sistema muestra un formulario con los datos
actuales.
5- El Administrador modifica los campos necesarios.
6- El sistema valida: formato de los datos ingresados,
cumplimiento de políticas de seguridad.
7- El Administrador confirma la operación.
8- El sistema actualiza los datos del usuario.
9- El sistema confirma la operación mostrando los datos
actualizados.
Postcondición El usuario queda actualizado con los nuevos datos.

Excepciones 2a- Si no hay usuarios registrados el sistema informa al
usuario.
6a- Si los datos son inválidos el sistema rechaza los
cambios y pide corrección.

UC-52 Ver Usuario

Iteración – 1
Equipo
Versión: 00.01
Actores Administrador

Descripción Este caso de uso permite consultar la información de los
usuarios registrados en el sistema, tanto activos como
inactivos. Se pueden aplicar filtros de búsqueda y
acceder al detalle de cada usuario, incluyendo roles,
estado y auditoría de cambios.

Precondición Debe existir al menos un usuario registrado en el
sistema.

Secuencia
normal

1- El Administrador selecciona Gestión de Usuarios >
Consultar Usuario.
2- El sistema muestra filtros de búsqueda:
3- El Administrador aplica uno o varios filtros (opcional)
y selecciona Buscar.
4- El sistema muestra listado de usuarios coincidentes.
5- El Administrador selecciona un usuario del listado.
6- El sistema despliega información detallada del
usuario.
Postcondición -

Excepciones 3a- Si el usuario no aplica filtros el sistema devuelve la
lista completa de usuarios.

UC-53 Alta Adelanto

Iteración – 1
Equipo
Versión: 00.01
Actores Personales administrativos

Descripción Este caso de uso permite registrar un nuevo adelanto de
dinero entregado a un empleado. El adelanto queda
asociado al empleado y se descuenta automáticamente
en la próxima liquidación de pagos.

Precondición Debe existir al menos un empleado registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Recursos
Humanos > Adelantos > Alta de Adelanto.
2- El sistema muestra un formulario con campos:
empleado, monto del adelanto, fecha de entrega,
observaciones
3- El usuario completa los datos requeridos.
4- El sistema valida: que el empleado exista y esté
activo, que el monto sea positivo, que la fecha sea
válida.
5- El usuario confirma la operación.
6- El sistema registra el adelanto, lo asocia al empleado,
guarda fecha/usuario responsable y genera registro de
auditoría.
7- El sistema confirma la operación mostrando el detalle
del adelanto.
Postcondición El adelanto queda registrado y asociado al empleado
correspondiente.

Excepciones 2a- Si no hay empleados registrados el sistema informa
al usuario.
4a- Si el monto excede al límite el sistema rechaza el
adelanto y solicita un nuevo monto.
6a- Si ocurre un error el sistema notifica al usuario.

UC-54 Baja Adelanto

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite dar de baja un adelanto
registrado. La baja se implementa como anulación
lógica (estado = Anulado), preservando la trazabilidad
del movimiento.

Precondición El adelanto debe existir en el sistema y estar en estado
pendiente.

Secuencia
normal

1- El usuario selecciona Gestión de Recursos Humanos >
Adelantos > Baja de Adelanto.
2- El sistema despliega listado de adelantos pendientes
con datos: empleado, monto, fecha, estado.
3- El usuario selecciona el adelanto a dar de baja.
4- El sistema valida que el adelanto esté en estado
pendiente.
5- El usuario confirma la operación.
6- El sistema cambia el estado del adelanto a Anulado,
guarda fecha/hora y usuario responsable.
7- El sistema confirma la anulación mostrando detalle
actualizado.
Postcondición El adelanto queda en estado Anulado y no será
considerado en próximas liquidaciones.

Excepciones 2a- Si no hay adelantos pendientes el sistema informa la
situación.
6a- Si ocurre un error el sistema notifica al usuario.

UC-55 Modificar Adelanto

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite modificar los datos de un
adelanto previamente registrado. Solo se pueden
modificar adelantos en estado pendiente.

Precondición El adelanto debe existir en el sistema y estar en estado
pendiente.

Secuencia
normal

1- El usuario selecciona Gestión de Recursos
Humanos > Adelantos > Modificar Adelanto.
2- El sistema muestra listado de adelantos pendientes
con datos básicos.
3- El usuario selecciona el adelanto a modificar.
4- El sistema despliega un formulario con los datos
actuales: empleado asociado, monto del adelanto, fecha
de entrega, observaciones.
5- El usuario modifica los campos necesarios.
6- El sistema valida: que el monto sea positivo, que la
fecha sea válida, que no se supere el límite de adelantos
definido por la empresa.
7- El usuario confirma la operación.
8- El sistema actualiza el adelanto, guarda fecha/hora y
usuario responsable, y genera auditoría con valores
anteriores y nuevos.
9- El sistema confirma la modificación mostrando el
detalle actualizado.
Postcondición El adelanto queda actualizado en el sistema con los
nuevos valores.

Excepciones 2a- Si no hay adelantos pendientes el sistema informa
que no hay registros disponibles para modificar.
8a- Si ocurre un error en el sistema informa al usuario.

UC-56 Ver Adelanto

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite consultar los adelantos
registrados en el sistema, filtrando por empleado, fecha,
estado o monto. El usuario puede acceder al detalle de
cada adelanto, incluyendo su estado actual y la
trazabilidad de cambios realizados.

Precondición Debe existir al menos un adelanto registrado en el
sistema.

Secuencia
normal

1- El usuario selecciona Gestión de Recursos
Humanos > Adelantos > Consultar Adelantos.
2- El sistema muestra filtros de búsqueda.
3- El usuario aplica filtros y selecciona Buscar.
4- El sistema lista los adelantos coincidentes mostrando:
empleado, monto, fecha, estado.
5- El usuario selecciona un adelanto del listado.
6- El sistema muestra la información detallada.
7- El sistema registra la consulta en auditoría.
Postcondición -

Excepciones -

UC-57 Informes generales

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite generar informes a partir de la
información almacenada en los diferentes módulos del
sistema. Los informes pueden ser filtrados por período,
módulo, cliente o categoría, y pueden exportarse en
diferentes formatos.

Precondición Debe existir información registrada en el sistema para el
período solicitado.

Secuencia
normal

1- El usuario selecciona Gestión Administrativa >
Reportes> Generar Reportes.
2- El sistema muestra opciones de reportes disponibles
3- El usuario selecciona el tipo de reporte.
4- El sistema muestra filtros de generación para el
reporte.
5- El usuario define los filtros y confirma la generación.
6- El sistema procesa la información y genera el reporte
solicitado.
7- El sistema muestra el reporte en pantalla y ofrece la
opción para exportarlo.
8- El usuario descarga el reporte
9- El sistema registra la generación del reporte en
auditoría.
Postcondición El informe queda disponible para consulta y exportación.

Excepciones 6a. No hay datos para el período o filtros seleccionados
→ el sistema muestra el mensaje “No se encontraron
resultados” .
6b. Error de procesamiento → si ocurre un error al
generar, el sistema informa y sugiere reintentar.
7a. Error de exportación → si no se puede generar el
archivo (PDF/Excel), el sistema informa la situación.

UC-58 Generar^ Recibos^

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este caso de uso permite generar un recibo de pago
para un empleado, reflejando el detalle de su liquidación
(sueldos, jornales, horas trabajadas, bonificaciones,
adelantos descontados). Los recibos pueden ser
visualizados en pantalla o exportados en PDF.

Precondición Debe existir al menos una liquidación o adelanto
registrado para el empleado.

Secuencia
normal

1- El usuario selecciona Recursos Humanos > Recibos >
Generar Recibo.
2- El sistema despliega filtros de búsqueda: empleado,
período.
3- El usuario selecciona el empleado y el período.
4- El sistema recupera los datos de liquidaciones,
jornales y adelantos correspondientes al empleado.
5- El sistema genera un borrador del recibo con:
Datos del empleado (nombre, DNI, puesto).
Conceptos liquidados (sueldo básico, horas
trabajadas, bonificaciones, descuentos).
Adelantos descontados.
Total neto a cobrar.
Fecha de emisión.
6- El usuario revisa el recibo y confirma la emisión.
7- El sistema genera el recibo definitivo, asigna número
único, guarda fecha/hora y usuario responsable, y
registra auditoría.
8- El sistema ofrece opciones de exportación en formato
PDF
Postcondición El recibo queda registrado en el sistema y disponible
para consultas posteriores.

Excepciones 2a. Sin empleados registrados → el sistema informa que
no existen registros disponibles.
4a. Sin datos de liquidación o adelantos en el período →
el sistema informa “No se encontraron registros para
generar recibo” .
6a. Cancelación → si el usuario cancela, no se genera
recibo.
7a. Error de persistencia/BD → el sistema informa la
falla y sugiere reintentar.
8a. Error de exportación → si no se puede generar el
PDF/Excel, el sistema muestra mensaje de error.

UC-59 Liquidar^ Pagos^

Iteración – 1
Equipo
Versión: 00.01
Actores Personales Administrativos

Descripción Este^ caso^ de^ uso^ permite^ realizar^ la^ liquidación^ de^ pagos^ de^
los empleados en función de los jornales trabajados, sueldos
pactados, bonificaciones y descuentos, incluyendo el
descuento automático de adelantos otorgados. El proceso
genera un registro de liquidación por empleado y deja
disponible la información para la emisión de recibos.

Precondición Deben existir^ registros^ de^ jornales^ y/o^ adelantos^ para^ el^
período a liquidar.

Secuencia
normal

1- El usuario selecciona Recursos Humanos > Liquidación
> Liquidar Pagos.
2- El sistema muestra filtros de liquidación: período de tiempo
y empleados a incluir.
3- El usuario define los parámetros y confirma la operación.
4- El sistema recopila la información del período para cada
empleado:
● Jornales o días trabajados.
● Sueldo pactado o valor por jornal.
● Bonificaciones adicionales (si existen).
● Adelantos otorgados (pendientes de descuento).
5- El sistema calcula el monto bruto, descuentos aplicables y
neto a pagar por empleado.
6- El sistema genera registros de liquidación por cada
empleado, vinculando: empleado, período, monto bruto,
adelantos descontados, monto neto.
7- El sistema guarda los registros de liquidación, asigna
identificadores únicos, guarda fecha/hora y usuario
responsable, y genera auditoría.
8- El sistema muestra un resumen de liquidación con totales
por empleado y totales generales de la nómina.
9 -El usuario confirma y procede a generar recibos (UC-58).
Postcondición Quedan registradas las liquidaciones de pago por
empleado para el período definido.

Excepciones 2a.^ Período^ inválido^ →^ el^ sistema^ solicita^ corrección.^
3a. Cancelación → si el usuario cancela, no se genera ninguna
liquidación.
4a. Falta de datos → si un empleado no tiene sueldo pactado
ni jornales registrados, el sistema lo marca como
inconsistente y no genera su liquidación.
5a. Cálculo inválido → si hay incoherencias (ej.: neto
negativo), el sistema alerta al usuario.
7a. Error de persistencia/BD → el sistema informa la falla y
sugiere reintentar.
8a. Usuario rechaza resultados → puede volver atrás, revisar
datos y recalcular antes de confirmar.

Iteración – 1
Equipo
Versión: 00.01
UC-61 Cargar^ Parte^ diario^

Actores Capataz^

Descripción Este^ caso^ de^ uso^ permite^ registrar^ el^ Parte^ Diario^ de^
producción, que incluye las condiciones meteorológicas, la
asistencia de empleados, roturas o incidencias de maquinaria
y las cargas de madera producidas en la jornada. Los datos
cargados se utilizarán para validar producción, liquidaciones y
planificación.

Diagrama de Actividad - Cargar Parte Diario
![Diagrama de Actividad - Cargar Parte Diario](figuras/req-diagrama-de-actividad-cargar-parte-diario.svg)

Precondición Debe^ existir^ al^ menos^ un^ lote^ en^ producción^ activo.^
Debe existir una tarea planificada o en ejecución asociada al lote.
La fecha no puede ser futura y debe estar dentro de los últimos 7 días.

Secuencia
normal

1- El usuario selecciona Producción > Parte Diario > Cargar
Parte Diario.
2- El sistema despliega un formulario dividido en secciones:
Condiciones Meteorológicas: temperatura, lluvias,
humedad (el sistema puede precargar desde el API
Clima, editable por el usuario).
Asistencia de Empleados: lista de empleados asignados
con opción de marcar presentes/ausentes.
Roturas/Incidencias de Maquinaria: selección de
máquinas con campos para describir fallas, tiempos de
parada.
Producción del Día: uso del caso de uso Registrar
Carga (UC-41), donde se registran destino, chofer,
categoría, bruto, tara y neto de cada carga.
3- El usuario completa o ajusta los campos de cada sección.
4- El sistema válido:
obligatoriedad de campos mínimos.
consistencia de datos.
5- El usuario confirma la operación.
6- El sistema registra el Parte Diario completo, guarda
fecha/hora y usuario responsable, y genera auditoría.
7- El sistema muestra confirmación y el resumen del Parte
Diario cargado.
Postcondición El^ parte^ diario^ queda^ registrado^ como^ documento^ único^ del^
día.

Excepciones 2a.^ No^ hay^ empleados^ asignados^ al^ lote^ →^ el^ sistema^ muestra^
advertencia pero permite continuar sin asistencia.
2c. API Clima no disponible → el usuario debe cargar
manualmente las condiciones.
4a. Datos inválidos → el sistema marca errores y solicita
corrección.
5a. Cancelación → el usuario cancela y se descarta la parte
diaria en curso.
6a. Error de persistencia/BD → el sistema informa la falla y
sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-62 Cerrar^ orden^ de^ Mantenimiento^

Actores Personales^ Administrativos^

Descripción Este^ caso^ de^ uso^ permite^ cerrar^ una^ orden^ de^ mantenimiento^
programada una vez que la intervención fue realizada. En el
cierre se registran detalles de la tarea ejecutada, insumos
utilizados, costos y observaciones. El cierre actualiza el
historial de la máquina.

Precondición Debe^ existir^ una^ orden^ de^ mantenimiento^ programada^ y^ en^
estado programado o en curso.

Secuencia
normal

1- El usuario selecciona Maquinaria > Mantenimiento > Cerrar
Orden de Mantenimiento.
2- El sistema muestra un listado de órdenes de
mantenimiento pendientes o en ejecución.
3- El usuario selecciona una orden para cerrar.
4- El sistema despliega un formulario con campos para el
cierre:
Tipo de mantenimiento (preventivo, correctivo,
servicio)
Observaciones
Insumos utilizados
Costos asociados
Observaciones
5- El usuario completa la información y confirma.
6- El sistema valida la consistencia de datos
7- El sistema actualiza el estado de la orden a Completada,
guarda los datos ingresados, registra fecha/hora y usuario
responsable.
8- El sistema actualiza el historial de la máquina con la
información del mantenimiento realizado.
9- El sistema confirma que la orden fue cerrada exitosamente.
Postcondición Orden^ de^ mantenimiento^ cambia^ su^ estado^ a^ completado.^ El^
historial de la máquina se actualiza con el registro del
mantenimiento realizado. Los insumos utilizados (si se
informa) se descuentan del stock.

Excepciones 2a.^ No^ hay^ órdenes^ pendientes^ →^ el^ sistema^ informa^ que^ no^
existen órdenes para cerrar.
3a. Selección inválida → si la orden ya está completada o anulada,
el sistema lo informa.
6a. Inconsistencia de datos → el sistema marca errores y
solicita corrección.
5a. Cancelación → si el usuario cancela, no se realizan
cambios.
7a. Error de persistencia/BD → el sistema informa la falla y
sugiere reintentar.

Iteración – 1
Equipo
Versión: 00.01
UC-63 Programar^ mantenimiento^

Actores Primario:^ Personal^ Administrativo^
Secundario: Sistema

Descripción Este^ caso^ de^ uso^ permite^ programar^ una^ orden^ de^
mantenimiento para una máquina o equipo. La programación
se puede realizar de dos maneras:

Manual: un usuario del sistema programa el
mantenimiento seleccionando máquina, fecha y tipo de
intervención.
Automática: el sistema invoca este caso de uso cuando
detecta, mediante reglas predefinidas, que una
máquina alcanzó un umbral de toneladas producidas o
fecha de servicio.

Diagrama de Actividad - Programar mantenimiento
![Diagrama de Actividad - Programar mantenimiento](figuras/req-diagrama-de-actividad-programar-mantenimiento.svg)

Precondición La^ máquina^ debe^ estar^ registrada^ y^ en^ estado^ activo.^

Secuencia
normal

1- El usuario selecciona Maquinaria > Mantenimiento >
Programar Mantenimiento.
2- El sistema despliega un formulario con los campos:
Máquina o equipo.
Tipo de mantenimiento (preventivo, correctivo,
servicio).
Fecha programada.
Observaciones (opcional).
3- El usuario completa los campos requeridos.
4- El sistema valida que:
la máquina existe y está activa
la fecha programada sea válida
5- El usuario confirma la operación.
6- El sistema genera la orden de mantenimiento programada,
asigna identificador único, guarda fecha/hora y usuario
responsable, y genera registro de auditoría.
7- El sistema confirma la operación mostrando el detalle de la
orden programada.
Postcondición Una^ orden^ de^ mantenimiento^ queda^ registrada^ y^ programada^

Excepciones 2a-^ No^ hay^ máquinas^ registradas^ →^ el^ sistema^ informa^ que^ no^
se pueden programar mantenimientos.
4a- Datos inválidos → fecha incorrecta, máquina inactiva, tipo
no válido → el sistema rechaza la operación.
4b- Mantenimiento duplicado → si ya existe una orden en la
misma fecha y máquina, el sistema alerta al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-64 Configurar permisos

Actores Administradores del sistema

Descripción Este caso de uso permite al Administrador configurar
permisos de acceso a los distintos módulos y
funcionalidades del sistema. Los permisos pueden
asignarse a roles o directamente a usuarios específicos.

Precondición Deben existir usuarios y/o roles previamente
registrados.

Secuencia
normal

1- El Administrador selecciona Gestión de Seguridad >
Configurar Permisos.
2- El sistema muestra las opciones disponibles:
Asignar permisos a roles.
Modificar permisos de un usuario en particular.
El Administrador selecciona si desea trabajar a
nivel Rol o Usuario.
El sistema despliega un listado de roles o usuarios
según corresponda.
El Administrador selecciona un rol/usuario.
3- El sistema muestra una matriz de permisos con los
módulos y funcionalidades del sistema.
4- El Administrador marca o desmarca los permisos
correspondientes.
5- El sistema valida que siempre existe al menos un
usuario con permisos de Administrador.
6- El Administrador confirma la operación.
7- El sistema guarda los cambios, registra fecha/hora y
usuario responsable, y genera auditoría del cambio de
permisos.
8- El sistema confirma la modificación mostrando el
detalle actualizado.
Postcondición Los roles y/o usuarios seleccionados tienen actualizados
sus permisos de acceso.2a. No hay ventas registradas →
el sistema informa que no existen ventas.

3a. Filtros inválidos → el sistema alerta al usuario y
solicita corrección.
4a. Sin resultados → el sistema informa que no se
encontraron ventas con los criterios aplicados.
Excepciones 2a. No existen roles o usuarios registrados → el sistema
informa la situación.
5a. Selección inválida → si el rol o usuario no existe, el
sistema rechaza la acción.
8a. Intento de eliminar último Administrador → el

Iteración – 1
Equipo
Versión: 00.01
sistema bloquea la operación y muestra advertencia.
9a. Cancelación → si el Administrador cancela, no se
guardan cambios.
10a. Error de persistencia/BD → si ocurre un error al
guardar los permisos, el sistema informa la falla.
UC-65 Planificaci?n de tareas por lote (ha)

Actores Primario: Administrador
Secundario: API clima (opcional)

Descripci?n Este caso de uso permite registrar la planificaci?n de tareas por lote
(tipo de tarea y superficie en ha) para un per?odo de producci?n. El sistema
muestra informaci?n hist?rica y recomendaciones clim?ticas como apoyo, y valida
que la suma de superficies no supere la superficie del lote.
Diagrama de Actividad - Planificación de tareas por lote (ha)
![Diagrama de Actividad - Planificación de tareas por lote (ha)](figuras/req-diagrama-de-actividad-planificar-tareas-por-lote-ha.svg)


Precondici?n Lote registrado y activo. (Opcional) API de clima disponible.

Secuencia
normal

1- El Administrador ingresa a Planificaci?n de tareas del Lote.
2- El sistema muestra el lote y las tareas planificadas existentes.
3- El Administrador agrega o edita tareas (tipo de tarea, superficie, observaciones).
4- El sistema valida que la suma de superficies no supere la superficie del lote.
5- El Administrador confirma la planificaci?n.
Iteraci?n ? 1
Equipo
Versi?n: 00.01
6- El sistema guarda la planificaci?n e inicia la generaci?n de recomendaciones.
7- El sistema notifica que la planificaci?n qued? disponible para revisi?n.

Postcondici?n Planificaci?n de tareas por lote registrada en el sistema.

Excepciones 2a. Lote no encontrado -> el sistema informa el error al usuario.
4a. Superficie total supera la superficie del lote -> el sistema informa el error.

2c. Precio de mercado no disponible → el sistema notifica del
error al usuario.

Iteración – 1
Equipo
Versión: 00.01
UC-66 Gestionar asignaciones y propuestas

Actores Primario: Administrador
Secundario: Sistema

Descripción Este caso de uso permite revisar y gestionar propuestas automáticas
de asignación de recursos por lote/tarea (empleados, maquinarias e insumos),
aceptarlas o cerrarlas y registrar asignaciones efectivas.

Precondición Deben existir lotes activos con tareas planificadas.

Secuencia
normal

1- El Administrador ingresa a Asignaciones > Propuestas.
2- El sistema muestra las propuestas disponibles con sus recursos sugeridos.
3- El Administrador revisa la propuesta y decide:
   - Aceptar (aplicar recursos).
   - Cerrar (descartar propuesta).
4- El sistema registra la decisión, actualiza el estado y genera auditoría.
5- El sistema confirma la operación.

Postcondición Propuesta actualizada y, en caso de aceptación, asignaciones
registradas para el lote/tarea.

Excepciones 2a. No hay propuestas disponibles → el sistema informa.
4a. Error de persistencia/BD → el sistema informa la falla.

Iteración – 1
Equipo
Versión: 00.01
UC-67 Configurar notificaciones de mantenimiento

Actores Administrador

Descripción Permite configurar usuarios suscriptos, canales y parámetros
de notificaciones de mantenimiento.

Precondición Deben existir usuarios registrados.

Secuencia
normal

1- El Administrador ingresa a Configuración > Notificaciones de mantenimiento.
2- El sistema muestra usuarios y preferencias actuales.
3- El Administrador selecciona usuarios y define parámetros.
4- El sistema valida la información y guarda la configuración.
5- El sistema confirma la operación.

Postcondición Configuración de notificaciones actualizada.

Excepciones 2a. No existen usuarios → el sistema informa.
4a. Error de persistencia/BD → el sistema informa la falla.

Iteración – 1
Equipo
Versión: 00.01
UC-68 Gestionar catálogos y listas de precios

Actores Administrador, Personal Administrativo

Descripción Permite administrar catálogos maestros (tipos de maquinaria,
tipos de mantenimiento, unidades de medida) y listas de precios por cliente
y categoría de madera.

Precondición -

Secuencia
normal

1- El usuario ingresa a Configuración > Catálogos y Precios.
2- El sistema muestra los catálogos y listas existentes.
3- El usuario crea, modifica o elimina registros.
4- El sistema valida duplicados y consistencia.
5- El sistema guarda los cambios y registra auditoría.

Postcondición Catálogos y listas de precios actualizados.

Excepciones 4a. Datos inválidos o duplicados → el sistema informa.
5a. Error de persistencia/BD → el sistema informa la falla.

Iteración – 1
Equipo
Versión: 00.01
Diagramas de Secuencia de Diseño:

Diagrama de Secuencia - Registrar Parte Diario
![Diagrama de Secuencia - Registrar Parte Diario](figuras/req-diagrama-de-secuencia-registrar-parte-diario.svg)

Diagrama de Secuencia - Planificar tareas por lote (ha)
![Diagrama de Secuencia - Planificar tareas por lote (ha)](figuras/req-diagrama-de-secuencia-planificar-tareas-por-lote-ha.svg)

Diagrama de Secuencia - Programar mantenimiento
![Diagrama de Secuencia - Programar mantenimiento](figuras/req-diagrama-de-secuencia-programar-mantenimiento.svg)

Iteración – 1
Equipo
Versión: 00.01
Requisitos No funcionales

NFR01 Copias de seguridad
Objetivos asociados
OBJ07 Seguridad y auditor?a del sistema
Requisitos asociados
RF-16 Registro de auditor?a
Descripci?n El sistema deber? incorporar un mecanismo que permita realizar copias de seguridad autom?ticas
de la base de datos al menos una vez por mes, con opci?n de restauraci?n.
Comentarios ninguno

NFR02 Control de acceso
Objetivos asociados
OBJ07 Seguridad y auditor?a del sistema
Requisitos asociados
RF-14 Gesti?n de usuarios y roles
RF-15 Configuraci?n de permisos
Descripci?n El sistema deber? implementar control de acceso basado en roles y permisos.
Comentarios ninguno

NFR03 Registros de auditor?a
Objetivos asociados
OBJ07 Seguridad y auditor?a del sistema
Requisitos asociados
RF-16 Registro de auditor?a
Descripci?n Toda acci?n de alta, baja, modificaci?n y consulta debe generar un registro de auditor?a.
Comentarios ninguno

NFR04 Manejo de errores de persistencia
Objetivos asociados
OBJ01 Centralizaci?n de registros operativos y administrativos
Requisitos asociados
RF-01, RF-02, RF-03, RF-04, RF-05, RF-06, RF-07, RF-08, RF-09, RF-10, RF-12, RF-13, RF-18, RF-19, RF-20, RF-21
Descripci?n En caso de error de persistencia en la base de datos, el sistema debe informar al usuario
y mantener la consistencia de los datos.
Comentarios ninguno

NFR05 Exportaci?n de archivos
Objetivos asociados
OBJ02 Generaci?n de reportes e indicadores para la gesti?n estrat?gica
Requisitos asociados
RF-11 Generaci?n de informes financieros
RF-17 Generaci?n de indicadores de gesti?n
Descripci?n El sistema debe permitir la exportaci?n de reportes en formato PDF.
Comentarios La exportaci?n a Excel no est? incluida en esta versi?n.
Matriz de Rastreabilidad Objetivo/Requisitos

| Objetivo | IRQ (Info) | RF (Funcionales) | NFR |
| --- | --- | --- | --- |
| OBJ-01 Centralización de registros | IRQ-01..IRQ-06, IRQ-09, IRQ-10 | RF-01..RF-13, RF-18..RF-21 | NFR04 |
| OBJ-02 Subsistema de Producción | IRQ-01, IRQ-08, IRQ-10 | RF-01, RF-02, RF-03, RF-17, RF-18 | NFR05 |
| OBJ-03 Subsistema de Maquinaria y Equipos | IRQ-02, IRQ-09 | RF-04, RF-05, RF-06, RF-20, RF-21 | NFR04 |
| OBJ-04 Subsistema de Recursos Humanos | IRQ-03 | RF-07, RF-08, RF-09 | NFR04 |
| OBJ-05 Subsistema Financiero y de Costos | IRQ-04, IRQ-08 | RF-10, RF-11, RF-17 | NFR05 |
| OBJ-06 Subsistema de Gestión Administrativa | IRQ-05 | RF-12, RF-13, RF-21 | NFR04 |
| OBJ-07 Seguridad y auditoría del sistema | IRQ-06, IRQ-07, IRQ-09 | RF-14, RF-15, RF-16, RF-19, RF-20 | NFR01, NFR02, NFR03 |
Iteración – 1
Equipo
Versión: 00.01
Glosario de Términos

Lote: unidad productiva forestal con superficie, ubicación, especie y estado.
LoteTarea: planificación por lote (tipo de tarea, superficie en ha y estado).
Parte Diario: registro operativo diario con empleados, maquinaria, cargas e insumos.
Carga: transporte de madera desde un lote hacia un destino (bruto, tara y neto).
Categoría de Madera: clasificación usada en cargas y precios.
Maquinaria: equipo productivo registrado para explotación y mantenimiento.
Mantenimiento: orden de servicio (programado, en curso, vencido o completado).
Insumo: material consumible; su stock se calcula por movimientos.
Movimiento de Stock: entrada o salida de insumos con referencia operativa.
Lote Inventario: lote FIFO de stock con cantidad disponible y costo.
Empleado: personal de la empresa vinculado a roles laborales y liquidaciones.
Chofer: transportista externo asociado a cargas.
Cliente: comprador de productos/servicios.
Proveedor: suministrador de insumos/servicios.
Venta: transacción comercial asociada a cargas y cliente.
Usuario: identidad de acceso al sistema (modelo principal de autenticación).
Rol/Permiso: control de acceso por funciones y módulos.
KPI: indicador de gestión generado a partir de datos operativos.
API Clima: servicio externo para análisis y recomendaciones climáticas.
Propuesta de Asignación: sugerencia automática de recursos por lote/tarea.
Asignación de recursos: vinculación efectiva de empleados, maquinarias e insumos a un lote/tarea.
Notificación de mantenimiento: alerta interna sobre órdenes programadas, vencidas o pendientes.
Configuración del sistema: parámetros globales de umbrales, horarios y reglas.
Catálogo maestro: tablas de referencia (tipos, unidades, listas de precios).
