<div class="task task-single">
    <h5><?php echo holotree_dms_ui()->elements()->task_link(); ?></h5>
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
