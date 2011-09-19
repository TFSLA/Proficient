<template><do type="prev" label="back"><prev/></do></template>
<card title="PSA - Projects">
<p> 
Project: 
   <select name="ptype">
    <option value="All">All</option>
    <option value="In_Progress">In Progress</option>
    <option value="In_planning">In Planning</option>
    <option value="Proposed">Proposed</option>
    <option value="On_Hold">On Hold</option>
    <option value="Complete">Complete</option>
    <option value="Archived">Archived</option>
    <option value="Not_Defined">Not Defined</option>
   </select>
<br/>
	<anchor>[<?php echo $AppUI->_('Go');?>]<go method="post" href="wap.php?sid=<?=$sid?>&amp;m=projects">
	<postfield name="ptype" value="$(ptype)"/>
	</go></anchor>


</p>
</card>
