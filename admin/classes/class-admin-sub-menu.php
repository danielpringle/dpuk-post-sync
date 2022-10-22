<?php
// namespace DPUK_Post_Sync;
namespace DPUK_Post_Sync\Admin;
use DPUK_Post_Sync\Admin as Admin;
class AdminSubMenu extends AdminMenu {

	function __construct( $options, Admin\AdminMenu $parent ){
		parent::__construct( $options );

		$this->parent_id = $parent->settings_id;
	}

}