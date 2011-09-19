
$alert_messages = Array(

"WORKED_HOURS_EXCEED_TOTAL_HOURS" => array(

"title" => "[PSA] Project <project_name> is overworked",
"message" => 
"<view_project_url>
======================================================================
Project Details
======================================================================
Id:           <project_id>
Name:         <project_name>
Start Date:   <project_start_date>
End Date:     <project_end_date>
Status:       <project_status>
Progress:     <project_percent_complete>%

Total Hours:  <total_hours>
Worked Hours: <worked_hours>

======================================================================"
),


"N_DAYS_TO_MILESTONE" => array(

"title" => "[PSA] Task <task_name> is a milestone and lacks <n> days to it finish",
"message" => 
"<view_task_url>
======================================================================
Task Detail
======================================================================
Id:               <task_id>
Name:             <task_name>
Creator:          <task_owner>
Progress:         <task_percent_complete>%
Start Date:       <task_start_date>
End Date:         <task_end_date>

Assigned Users:   
<task_users>

======================================================================"
),


"TODAY_FINISH_MILESTONE" => array(

"title" => "[PSA] Task <task_name> is a milestone and finishes today",
"message" => 
"<view_task_url>
======================================================================
Task Detail
======================================================================
Id:               <task_id>
Name:             <task_name>
Creator:          <task_owner>
Progress:         <task_percent_complete>%
Start Date:       <task_start_date>
End Date:         <task_end_date>

Assigned Users:   
<task_users>

======================================================================"
),




"TODAY_IS_CONSTRAINT_DATE" => array(

"title" => "[PSA] Task <task_name> has a constraint for today",
"message" => 
"<view_task_url>
======================================================================
Task Detail
======================================================================
Id:               <task_id>
Name:             <task_name>
Creator:          <task_owner>
Progress:         <task_percent_complete>%
Start Date:       <task_start_date>
End Date:         <task_end_date>

Constraint Type:  <task_constraint_type>
Constraint Date:  <task_constraint_date>

Assigned Users:   
<task_users>

======================================================================"
),


"N_DAYS_TO_CONSTRAINT_DATE" => array(

"title" => "[PSA] Task <task_name> has a constraint date and lacks <n> days for it",
"message" => 
"<view_task_url>
======================================================================
Task Detail
======================================================================
Id:               <task_id>
Name:             <task_name>
Creator:          <task_owner>
Progress:         <task_percent_complete>%
Start Date:       <task_start_date>
End Date:         <task_end_date>

Constraint Type:  <task_constraint_type>
Constraint Date:  <task_constraint_date>

Assigned Users:   
<task_users>

======================================================================"
),



"TODAY_IS_TARGET_END_DATE" => array(

"title" => "[PSA] Project <project_name> must finish today",
"message" => 
"<view_project_url>
======================================================================
Project Details
======================================================================
Id:           <project_id>
Name:         <project_name>
Start Date:   <project_start_date>
End Date:     <project_end_date>
Status:       <project_status>
Progress:     <project_percent_complete>%

Total Hours:  <total_hours>
Worked Hours: <worked_hours>

======================================================================"
),

"N_DAYS_TO_TARGET_END_DATE" => array(

"title" => "[PSA] Project <project_name> must finish in <n> days",
"message" => 
"<view_project_url>
======================================================================
Project Details
======================================================================
Id:           <project_id>
Name:         <project_name>
Start Date:   <project_start_date>
End Date:     <project_end_date>
Status:       <project_status>
Progress:     <project_percent_complete>%

Total Hours:  <total_hours>
Worked Hours: <worked_hours>

======================================================================"
),
);


