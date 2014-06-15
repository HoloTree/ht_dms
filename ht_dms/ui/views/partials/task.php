<?php $id = $obj->id(); ?>
<div class="task task-single">
    <h5><a href="<?php echo get_term_link( $id, HT_DMS_TASK_CT_NAME ); ?>" title="View task {@name}">{@name}</a></h5>
    <ul>
        <li>Responsible Member: {@assigned_user.display_name}</li>
        <li>Description: {@task_description}</li>
        <li>Status: {@task_status}</li>
		<?php
			$blocks = holotree_task_class()->blocking( $id, $obj );
			if ( is_array( $blocks ) ) :
		?>
				<li>Task Blocked By This Task:
					<ul>
						<?php echo holotree_dms_ui()->build_elements()->block( $blocks  ); ?>
					</ul>
				</li>
		<?php
			endif;
			$blocking = holotree_task_class()->blocked_by( $id, $obj );

			if ( is_array( $blocking ) ) :
		?>
			<li>Task That Are Blocked By This Task:
				<ul>
				<?php echo holotree_dms_ui()->build_elements()->block( $blocking );	?>
			</ul>
		</li>
		<?php endif; ?>
    </ul>
	<?php echo holotree_dms_ui()->views()->task_actions( $id, $obj ); ?>
</div>
