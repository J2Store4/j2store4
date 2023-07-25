<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="j2store-modal">
<div class="modal">
	<div class="j2store">
		<div class="modal-header">
		<a class="close" data-dismiss="modal">&times;</a>
		</div>
		<div class="modal-body">
		<?php echo $this->html; ?>
		</div>
		<div class="modal-footer">
		<a class="btn" data-dismiss="modal"><?php echo JText::_('J2STORE_CLOSE')?></a>
		</div>
	</div>
</div>
</div>