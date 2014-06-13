<?php
/**
 * @TODO What this does.
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

htdms_theme_header(); ?>

	<div id="primary" class="content-area <?php htdms_theme_primary_class(); ?>">
		<main id="main" class="site-main <?php htdms_theme_main_class(); ?>" role="main">

			<?php

			$t = holotree_task_class();
			$obj = $t->single_task_object( get_queried_object_id() );
			$id = $obj->id();



			$dID = $obj->field( 'decision' );
			$dID = $dID[ 'ID' ];

			$ui = holotree_dms_ui();

			echo '<h2>'.$ui->elements()->title( $id, $obj, true  ).'</h2>';

			$tabs = array(
				array(
					'label' 	=> __( 'Task Details', 'holotree'),
					'content'	=> $ui->views()->task( $id, $preview = false, $obj ),
				),
				array(
					'label' 	=> __( 'Task Documents', 'holotree'),
					'content'	=> $ui->views()->task_docs( $id, $obj ),
				),
				array(
					'label'		=> __( 'Decision', 'holtree' ),
					'content'	=> $ui->views()->decision( $dID ),
				),
				array(
					'label'		=> __( 'Edit Task', 'holotree' ),
					'content'	=> $ui->add_modify()->edit_task( $dID, $obj->id() ),
				),
			);

			echo $ui->elements()->tab_maker( $tabs );

			?>

		</main><!-- #main -->
		<?php htdms_theme_sidebar( 'task' ); ?>
	</div><!-- #primary -->

<?php htdms_theme_footer(); ?>
