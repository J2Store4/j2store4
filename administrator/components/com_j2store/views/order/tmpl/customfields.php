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
defined( '_JEXEC' ) or die( 'Restricted access' );

if($type=='billing') {
	$field = 'all_billing';
}elseif($type=='shipping') {
	$field = 'all_shipping';
}elseif($type=='payment') {
	$field = 'all_payment';
}
$platform = J2Store::platform();
$registry = $platform->getRegistry('{}');
$fields = array();
if(!empty($row->$field) && strlen($row->$field) > 0) {
	//$registry->loadString(stripslashes($row->$field), 'JSON');
	$custom_fields = json_decode(str_replace('\/','/', $row->$field));
	if(is_object($custom_fields)){
        $custom_fields = $platform->fromObject($custom_fields,false);
    }
	if(isset($custom_fields) && count($custom_fields)) {
		foreach($custom_fields as $namekey=>$field) {
			if(!property_exists($row, $type.'_'.$namekey) && !property_exists($row, 'user_'.$namekey) && $namekey !='country_id' && $namekey != 'zone_id' && $namekey != 'option' && $namekey !='task' && $namekey != 'view' && $namekey !='email' ) {
				$fields[$namekey] = $field;
			}
		}

	}
}
?>

<?php if(isset($fields) && count($fields)) :?>
<?php foreach($fields as $namekey=>$field) : ?>
	<?php if(is_object($field)): ?>
		<dt><?php echo JText::_($field->label); ?>:</dt>
		<dd>
		<?php
		if(is_array($field->value)) {
			echo '<br />';
			foreach($field->value as $value) {
				echo '- '.JText::_($value).'<br/>';
			}

		}elseif(is_object($field->value)) {
                //convert the object into an array
            $obj_array = $platform->fromObject($field->value);
            echo '<br />';
            foreach($obj_array as $value) {
                echo '- '.JText::_($value).'<br/>';
            }
		
		}elseif(is_string($field->value) && J2Store::utilities()->isJson(stripslashes($field->value))) {
			$json_values = json_decode(stripslashes($field->value));

		if(is_array($json_values)) {
			foreach($json_values as $value){
				echo '- '.JText::_($value).'<br/>';
			}
		} else {
				echo JText::_(nl2br($field->value));
			}

		} else {
			echo JText::_(nl2br($field->value));
		}
		?>
		</dd>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>