<?php
/**
 * --------------------------------------------------------------------------------
 * User plugin - j2store address field
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Unauthorized Access');
$app = JFactory::getApplication();
$platform = J2Store::platform();
$J2gridRow = 'row';
$J2gridCol = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $J2gridRow = 'row-fluid';
    $J2gridCol = 'span';
}
if($platform->isClient('administrator')) {
	$vars->params->set('bootstrap_version', 4);
}
?>
<style>
    #billing-new  label {
        display: block;
    }
    #billing-new select {
        width: 220px;
    }
</style>
<div id="billing-new">
	<?php
	$html = $vars->field_html;
//	$J2gridRow = ($vars->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
//	$J2gridCol = ($vars->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
	if(empty($html) || strlen($html) < 5) {
		//we dont have a profile set in the store profile. So use the default one.
		$html = '<div class="'. $J2gridRow .'">
		<div class="'. $J2gridCol .'6">[first_name] [last_name] [phone_1] [phone_2] [company] [tax_number]</div>
		<div class="'. $J2gridCol .'6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';
	}

	//first find all the checkout fields
	preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

	$allFields = $vars->fields;
    $disable_name = $vars->plugin_params->get('disable_name',0);
	foreach ($vars->fields as $fieldName => $oneExtraField):
	    if($disable_name){
            if($fieldName == 'first_name' || $fieldName == 'last_name'){
                $html = str_replace('['.$fieldName.']','',$html);
                continue;
            }
        }

		$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
		if(property_exists($vars->address, $fieldName) && $fieldName != 'email') {
			if(isset( $vars->address_default[$fieldName] ) && !empty( $vars->address_default[$fieldName] )){
				$vars->address->$fieldName = $vars->address_default[$fieldName];
			}
			$html = str_replace('['.$fieldName.']',$vars->selectableBase->getFormatedDisplay($oneExtraField,$vars->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null).'</br />',$html);
		}
	endforeach;
	//check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
	$unprocessedFields = array();
	foreach($vars->fields as $fieldName => $oneExtraField) {
		if(!in_array($fieldName, $checkoutFields[1]) &&  $fieldName != 'email') {
			$unprocessedFields[$fieldName] = $oneExtraField;
		}
	}

	//now we have unprocessed fields. remove any other square brackets found.
	preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
	foreach($removeFields[1] as $fieldName) {
		$html = str_replace('['.$fieldName.']', '', $html);
	}
	echo $html;
	?>
	<?php
	if(count($unprocessedFields)): ?>
		<div class="<?php echo $J2gridRow;?>">
			<div class="<?php echo $J2gridCol;?>12">
				<?php
				$uhtml = '';
				foreach ($unprocessedFields as $fieldName => $oneExtraField):
					$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
					if(property_exists($vars->address, $fieldName)) {
						if(isset( $vars->address_default[$fieldName] ) && !empty( $vars->address_default[$fieldName] )){
							$vars->address->$fieldName = $vars->address_default[$fieldName];
						}
						$uhtml .= $vars->selectableBase->getFormatedDisplay($oneExtraField,$vars->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
						$uhtml .='<br />';
					}
				endforeach;
				echo $uhtml;
				?>

			</div>
		</div>
		<?php
	endif;
	?>

	<?php
	if($platform->isClient('administrator')): ?>
		<input name="j2reg[j2store_address_id]" value="<?php echo $vars->address->j2store_address_id; ?>" type="hidden" />
	<?php endif; ?>
</div>
<style>
	.j2error{
		color: #ff0000;
		font-style: italic;
	}
</style>

