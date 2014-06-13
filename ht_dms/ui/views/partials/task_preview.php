<div class="task task-preview">
    <h5><a href="<?php echo get_term_link( $obj->id(), HT_DMS_TASK_CT_NAME ); ?>" title="View task {@name}">{@name}</a></h5>
    <ul>
        <li>Responsible Member: {@assigned_user.display_name}</li>
        <li>Description: {@task_description}</li>
        <li>Status: {@task_status}</li>
    </ul>
</div>
