<?php $id = $obj->id(); ?>
<div class="task task-single">
    <h5><a href="<?php echo get_term_link( $id, HT_DMS_TASK_CT_NAME ); ?>" title="View task {@name}">{@name}</a></h5>
    <ul>
        <li>Responsible Member: {@assigned_user.display_name}</li>
        <li>Description: {@task_description}</li>
        <li>Status: {@task_status}</li>
		[if blockers]
			<li>Task That This Task Is Blocking:
				<ul>
					[each blocker]
						<li>{@name}</li>
					[/each]
				</ul>
			</li>
		[/if]

		[if blocking]
		<li>Task That Are Blocked By This Task:
			<ul>
				[each blocking]
				<li>{@name}</li>
				[/each]
			</ul>
		</li>
		[/if]

    </ul>

</div>
