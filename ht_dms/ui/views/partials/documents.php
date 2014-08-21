<div class="documents documents">
   <?php
   if ( is_array( $docs ) && count( $docs ) > 0 ) {
	   echo '<ul>';
	   foreach ( $docs as $doc ) {
		   $link = get_attached_file( $doc['ID'] );
		   $link = holotree_link( $link, 'permalink', $doc['post_title'] );
			echo sprintf( '<li>%1s</li>', $link);
	   }
	   echo '</ul>';
   }
   else {

	   echo __(
		   sprintf( '<div class="no-docs">%2s</div>',
			   sprintf( 'This %1s has no documents.', ucwords( $type ) ), 'holotree' )
	   );

   }
   ?>
</div>
