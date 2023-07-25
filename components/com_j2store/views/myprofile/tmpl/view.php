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


//no direct access
defined('_JEXEC') or die('Restricted access');
$task = JFactory::getApplication()->input->getString('task');
?>
<?php if ($task == 'printOrder') : ?>
    <style type="text/css" media="print">
        @page
        {
            font-family: s;
            size:  auto;   /* auto is the initial value */
            margin: 0mm;  /* this affects the margin in the printer settings */
        }
    </style>
    <script type="text/javascript">
           window.print();
    </script>
<?php endif; ?>

<div class="j2store-order">
	<div class="j2store-invoice-template">
	<?php if(isset($this->order) && $this->error == false): ?>
		<?php echo J2Store::invoice()->getFormatedInvoice($this->order,array()); ?>
		
	<?php else: ?>
		<div class="alert alert-block alert-warning">
			<?php echo $this->errormsg; ?>
		</div>
	<?php endif; ?>
	</div>
</div>