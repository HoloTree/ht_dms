<div class="organization organization-single">
	<h3>{{organization}}{@post_title}</h3>
	<p class=""description">Description: {@description}</p>
	[if groups]
	<div class="organizations-groups">
		<h5>Groups</h5>
		<ul>
			[each groups]
			<li>{@post_title}</li>
			[/each]
		</ul>
	</div>
	[/if]
	[if decisions]
	<div class="organizations-decisions">
		<h5>Decisions</h5>
		<ul>
			[each decisions]
			<li>{@post_title}
				<ul>
					<li>{@decision_status}</li>
				</ul>
			</li>
			[/each]
		</ul>
	</div>
	[if members]
	<div class="organizations-members">
		<h5>Members</h5>
		<ul>
			[each members]
			<li>{@display_name}</li>
			[/each]
		</ul>
	</div>
	[/if]


</div>
