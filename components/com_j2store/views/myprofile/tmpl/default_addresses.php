<?php
/**
 * @package J2Store
* @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
* @license GNU GPL v3 or later
*/
// No direct access to this file
defined('_JEXEC') or die;
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$config = J2Store::config();
$J2gridRow = ($config->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($config->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>

<h3 class="myprofile-address-list-heading"><?php echo JText::_('J2STORE_ADDRESS_LIST');?></h3>
	<div class="myprofile-address-addnew">
		<?php //echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=myprofile&task=editAddress&layout=address&tmpl=component&address_id=', JText::_('J2STORE_ADD') ,array('update'=>true,'class'=>'btn btn-success','width'=>800 , 'height'=>600));?>
		<a href="<?php echo J2Store::platform()->getMyprofileUrl(array('task' => 'editAddress','layout' => 'address','address_id' => 0));?>"><?php echo JText::_('J2STORE_ADD');?></a>

	</div>
<hr>
<ul class="j2store-myprofile-address-list">
    <div id="j2store-address-alert"></div>
	<?php
	if($this->orderinfos && !empty($this->orderinfos ) ):
	foreach($this->orderinfos as $orderinfo):?>
	<?php
			$addressTable = F0FTable::getInstance('Address', 'J2StoreTable');
			$addressTable->load($orderinfo->j2store_address_id);

			$fields =  $this->fieldClass->getFields($addressTable->type,$addressTable,'address');

			$html = $config->get('store_'.strtolower($addressTable->type).'_layout', '');

			if(empty($html) || strlen($html) < 5) {
				//we dont have a profile set in the store profile. So use the default one.
				$html = '<div class="'.$J2gridRow.'">
					<div class="'.$J2gridCol.'6">[first_name] [last_name] [email] [phone_1] [phone_2] [country_id] [zone_id] </div>
					<div class="'.$J2gridCol.'6">[company] [tax_number] [address_1] [address_2] [city] [zip] </div>
					</div>';
				}
			//first find all the checkout fields
			preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

			//var_dump($fields);
		?>
		<li id="j2store-address-tr-<?php echo $orderinfo->j2store_address_id;?>" class="j2store-myprofile-address-single-list well" >
			<ul class="j2store-myprofile-address-controls inline pull-right">
				<li class="myprofile-address-control-edit">
					<?php //echo J2StorePopup::popup('index.php?option=com_j2store&view=myprofile&task=editAddress&layout=address&tmpl=component&address_id='.$orderinfo->j2store_address_id, JText::_('J2STORE_EDIT') ,array('update'=>true,'width'=>800 , 'height'=>500));?>
					<a href="<?php echo J2Store::platform()->getMyprofileUrl(array('task' => 'editAddress','layout' => 'address','address_id' => $orderinfo->j2store_address_id));?>"><?php echo JText::_('J2STORE_EDIT');?></a>
				</li>
				<li class="myprofile-address-control-delete">
					<a onclick="deleteAddress('<?php echo $orderinfo->j2store_address_id;?>')" href="#" >
						<?php echo JText::_('J2STORE_DELETE');?>
					</a>
				</li>
			</ul>
			<?php foreach ($fields as $fieldName => $oneExtraField):?>
					<?php if(property_exists($addressTable, $fieldName)):?>
					<?php
						$label = '<strong>'.JText::_($oneExtraField->field_name).'</strong> : ';
						if($fieldName == 'country_id') {
							$value = $orderinfo->country_name;
						}elseif($fieldName == 'zone_id') {
							$value = $orderinfo->zone_name;
						}else {
							$value =$addressTable->$fieldName;
						}
						$html = str_replace('['.$fieldName.']',$label.$value.'</br>', $html);
					?>
					<?php endif;?>
				<?php endforeach;?>

				<?php

				//check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
				$unprocessedFields = array();
				foreach($fields as $fieldName => $oneExtraField) {
					if(!in_array($fieldName, $checkoutFields[1])) {
						$unprocessedFields[$fieldName] = $oneExtraField;
					}
				}

				//now we have unprocessed fields. remove any other square brackets found.
				preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
				foreach($removeFields[1] as $fieldName) {
					$html = str_replace('['.$fieldName.']', '', $html);
				}
				?>
			<?php echo $html; ?>
			 <?php if(count($unprocessedFields)): ?>
				 <div class="<?php echo $J2gridRow; ?>">
				  <div class="<?php echo $J2gridCol; ?>12">
				  <?php $uhtml = '';?>
				 <?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
										<?php

										if(property_exists($addressTable, $fieldName)) {
											$label = '<strong>'.JText::_($oneExtraField->field_name).'</strong> : ';
											if($fieldName == 'country_id') {
												$value = JText::_($orderinfo->country_name);
											}elseif($fieldName == 'zone_id') {
												$value = JText::_($orderinfo->zone_name);
											}else {
												$value = $addressTable->$fieldName;
											}
										 	$uhtml .= str_replace('['.$fieldName.']',$label.$value.'</br>', $uhtml);
										}
										?>
				  <?php endforeach; ?>
				  <?php echo $uhtml; ?>
				  </div>
				</div>
				<?php endif; ?>
		</li>
	<?php endforeach;?>
	<?php endif;?>
</ul>
<div class="before-profile">
<?php if(isset($this->beforedisplayprofile)): ?>
	<?php echo $this->beforedisplayprofile;?>
<?php endif; ?>
</div>
<script>
	if(typeof(j2store) == 'undefined') {
		var j2store = {};
	}
	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}

	function deleteAddress(id) {
		(function ($) {
			$('#system-message-container').html('');
			var c=confirm('<?php echo addslashes(JText::_("J2STORE_MYPROFILE_DELETE_CONFIRM_MESSAGE"));?>');
			if (c){
                var data = {
                    option: 'com_j2store',
                    view: 'myprofile',
                    task: 'deleteAddress',
                    address_id: id
                };
                $.ajax({
                    url : '<?php echo JRoute::_('index.php');?>',
                    type: 'post',
                    data :data,
                    dataType: 'json',
                    beforeSend: function() {
                        $('.j2error').remove();
                    },
                    success: function(json){
                        if(json['success']){
                            if(json['url']){
                                $('#j2store-address-tr-'+id).remove();
                                var html ='';
                                html +='<div class="alert alert-success">';
                                html +=json['message'];
                                html +='</div>';
                                jQuery('#j2store-address-alert').html(html);
                            }
                        }else {
                            if(json['message']){
                                var html ='';
                                html +='<div class="alert alert-danger">';
                                html += json['message'];
                                html +='</div>';
                                jQuery('#j2store-address-alert').html(html);
                            }
                        }

                    }

                });
			}
		})(j2store.jQuery);
	}
</script>
