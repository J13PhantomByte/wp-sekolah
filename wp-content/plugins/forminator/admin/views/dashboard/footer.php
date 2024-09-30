<?php
/**
 * Template admin/views/dashboard/footer.php
 *
 * @package Forminator
 */

// Free version footer.
if ( ! FORMINATOR_PRO ) {
	$this->template( 'dashboard/footer-free' );
}
