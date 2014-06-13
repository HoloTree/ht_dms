<?php $ui = holotree_dms_ui(); ?>
<div class="messages">
	<div class="row">
		<div class="large-6 small-12 columns" id="new-messages">
			<?php echo $ui->views()->notification_loop( get_current_user_id(), true,  'new', 'pm', false ); ?>
		</div>
		<div class="large-6 small-12 columns" id="new notifications">
			<?php echo $ui->views()->notification_loop( get_current_user_id(), true,  'new', 'messages', false ); ?>
		</div>
	</div>

	<div class="row">
		<div class="large-12 columns" id="create-message">
			<?php echo $ui->add_modify()->new_notification(); ?>
		</div>
	</div>
</div>
