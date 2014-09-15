<div class="decision-preview">
	<h5><?php echo holotree_link( $obj->id(), 'permalink', null, null, true ); ?></h5>
	<div class="details">
		<p class="description">{@decision_description}</p>
		<ul>
			<li>{{group}} Group: {@group}</li>
			<li>Status: <span id="decision-status">{@decision_status}</span></li>
			<li>Manager: {@manager}</li>
			<li>Proposed By: {@proposed_by}</li>
		</ul>
	</div>
</div>
