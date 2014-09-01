<div class="task task-single">
    <h5>{@name}</h5>
    <ul>
        <li>Responsible Member: {@assigned_user.display_name}</li>
        <li>Description: {@task_description}</li>
        <li>Status: {@task_status}</li>
		<?php
			echo holotree_dms_ui_build_elements()->block( $obj->field( 'blockers' ), true, '<li>Task That This Task Is Blocking: <ul>', '</ul></li>' );
			echo holotree_dms_ui_build_elements()->block( $obj->field( 'blocking' ), true, '<li>Task That Are Blocked By This Task: <ul>', '</ul></li>' );
		?>
    </ul>

</div>
