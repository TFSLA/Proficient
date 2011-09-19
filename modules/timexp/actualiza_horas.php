<?php
/*
UPDATE timexp 
SET timexp_start_time = DATE_FORMAT(timexp_date, '%Y-%m-%d 09:00:00'),
timexp_end_time = CONCAT(DATE_FORMAT(timexp_date, '%Y-%m-%d'), SEC_TO_TIME(TIME_TO_SEC(EXTRACT(HOUR_SECOND FROM timexp_start_time))+(timexp_value * 3600)))
WHERE 
timexp_start_time like '%00:00:00%'
AND timexp_end_time like '%00:00:00%'
*/
?>
