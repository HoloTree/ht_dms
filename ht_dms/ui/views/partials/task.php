<div class="task task-single">
    <h5>{{task}}{@name}</h5>
    <ul>
        <li>Responsible Member: {@assigned_user.display_name}</li>
        <li>Description: {@task_description}</li>
        <li>Status: {@task_status}</li>
		<?php
			echo ht_dms_ui_build_elements()->block( $obj->field( 'blockers' ), true, '<li>Task That This Task Is Blocking: <ul>', '</ul></li>' );
			echo ht_dms_ui_build_elements()->block( $obj->field( 'blocking' ), true, '<li>Task That Are Blocked By This Task: <ul>', '</ul></li>' );
		?>
    </ul>

</div>
