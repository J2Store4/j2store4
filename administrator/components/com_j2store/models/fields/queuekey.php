<?php
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
class JFormFieldQueuekey extends JFormField
{
	protected $type = 'queuekey';

	public function getInput() {

		$config = J2Store::config();
		$queue_key = $config->get ( 'queue_key','' );
		$url = 'index.php?option=com_j2store&view=configuration&task=regenerateQueuekey';
		if(empty( $queue_key )){
			$queue_string = JFactory::getConfig ()->get ( 'sitename','' ).time ();
			$queue_key = md5 ( $queue_string );
			$config->saveOne ( 'queue_key', $queue_key );
		}

		$html = '';
		$html .= '<div class="alert alert-block alert-info"><strong id="j2store_queue_key">'.$queue_key.'</strong>&nbsp;&nbsp;&nbsp;<a onclick="regenerateQueueKey()" class="btn btn-danger">'.JText::_ ( 'J2STORE_STORE_REGENERATE' ).'</a>
		<script>
		function regenerateQueueKey(){
			(function($){
				$.ajax({
					url : "'.$url.'",
					type : \'get\',
					cache : false,
					dataType : \'json\',
					success : function(json) {				
						if (json != null && json[\'queue_key\']) {
							$("#j2store_queue_key").html(json["queue_key"]);
						}
					}
		
				});		
			})(jQuery);
		}
		</script>
		<input type="hidden" name="'.$this->name.'" value="'.$queue_key.'"/>
		</div>';
		return  $html;
	}

}