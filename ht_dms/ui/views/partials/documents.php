<div class="documents documents">
   <?php
   if ( is_array( $docs ) && count( $docs ) > 0 ) {
	   echo '<ul>';
	   foreach ( $docs as $doc ) {
		   $link = get_attached_file( $doc['ID'] );
		   $link = holotree_link( $link, 'permalink', $doc['post_title'] );
			echo '<li>'.$link.'</li>';
	   }
	   echo '</ul>';
   }
   else {

	   echo '<div class="no-docs">';
	   __( sprintf( 'This %1s has no documents.', ucwords( $type ) ), 'holotree' );

   }
   ?>
</div>
