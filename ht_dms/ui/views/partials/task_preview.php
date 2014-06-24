<div class="task task-preview">
    <h5><?php echo holotree_dms_ui()->elements()->task_link( $obj->id ); ?></h5>
    <ul>
        <li>Responsible Member: {@assigned_user.display_name}</li>
        <li>Description: {@task_description}</li>
        <li>Status: {@task_status}</li>
    </ul>
</div>
