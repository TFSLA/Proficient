
$alert_messages = Array(

"WORKED_HOURS_EXCEED_TOTAL_HOURS" => array(

"title" => "[PSA] Proyecto <project_name> est� con exceso de trabajo",
"message" => 
"<view_project_url>
======================================================================
Detalle del Proyecto
======================================================================
Id:               <project_id>
Nombre:           <project_name>
Fecha Inicio:     <project_start_date>
Fecha Fin:        <project_end_date>
Estado:           <project_status>
Progreso:         <project_percent_complete>%

Horas Totales:    <total_hours>
Horas Trabajadas: <worked_hours>

======================================================================"
),



"N_DAYS_TO_MILESTONE" => array(

"title" => "[PSA] Tarea <task_name> es un hito y faltan <n> d�as para que finalice",
"message" => 
"<view_task_url>
======================================================================
Detalle de Tarea
======================================================================
Id:                 <task_id>
Nombre:             <task_name>
Responsable:        <task_owner>
Progreso:           <task_percent_complete>%
Fecha Inicio:       <task_start_date>
Fecha Fin:          <task_end_date>

Usuarios Asignados: 
<task_users>

======================================================================"
),



"TODAY_FINISH_MILESTONE" => array(

"title" => "[PSA] Tarea <task_name> es un hito y finaliza hoy",
"message" => 
"<view_task_url>
======================================================================
Detalle de Tarea
======================================================================
Id:                 <task_id>
Nombre:             <task_name>
Responsable:        <task_owner>
Progreso:           <task_percent_complete>%
Fecha Inicio:       <task_start_date>
Fecha Fin:          <task_end_date>

Usuarios Asignados: 
<task_users>

======================================================================"
),


"TODAY_IS_CONSTRAINT_DATE" => array(

"title" => "[PSA] Tarea <task_name> tiene una delimitaci�n para el d�a de hoy",
"message" => 
"<view_task_url>
======================================================================
Detalle de Tarea
======================================================================
Id:                 <task_id>
Nombre:             <task_name>
Responsable:        <task_owner>
Progreso:           <task_percent_complete>%
Fecha Inicio:       <task_start_date>
Fecha Fin:          <task_end_date>

Tipo Delimitaci�n:  <task_constraint_type>
Fecha Delimitaci�n: <task_constraint_date>

Usuarios Asignados: 
<task_users>

======================================================================"
),

"N_DAYS_TO_CONSTRAINT_DATE" => array(

"title" => "[PSA] Tarea <task_name> tiene una delimitaci�n y faltan <n> d�as para la misma",
"message" => 
"<view_task_url>
======================================================================
Detalle de Tarea
======================================================================
Id:                 <task_id>
Nombre:             <task_name>
Responsable:        <task_owner>
Progreso:           <task_percent_complete>%
Fecha Inicio:       <task_start_date>
Fecha Fin:          <task_end_date>

Tipo Delimitaci�n:  <task_constraint_type>
Fecha Delimitaci�n: <task_constraint_date>

Usuarios Asignados: 
<task_users>

======================================================================"
),



"TODAY_IS_TARGET_END_DATE" => array(

"title" => "[PSA] Proyecto <project_name> debe finalizar hoy",
"message" => 
"<view_project_url>
======================================================================
Detalle del Proyecto
======================================================================
Id:               <project_id>
Nombre:           <project_name>
Fecha Inicio:     <project_start_date>
Fecha Fin:        <project_end_date>
Estado:           <project_status>
Progreso:         <project_percent_complete>%

Horas Totales:    <total_hours>
Horas Trabajadas: <worked_hours>

======================================================================"
),

"N_DAYS_TO_TARGET_END_DATE" => array(

"title" => "[PSA] Proyecto <project_name> debe finalizar dentro de <n> d�as",
"message" => 
"<view_project_url>
======================================================================
Detalle del Proyecto
======================================================================
Id:               <project_id>
Nombre:           <project_name>
Fecha Inicio:     <project_start_date>
Fecha Fin:        <project_end_date>
Estado:           <project_status>
Progreso:         <project_percent_complete>%

Horas Totales:    <total_hours>
Horas Trabajadas: <worked_hours>

======================================================================"
),
);
