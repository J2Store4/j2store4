<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die ();

/**
 * J2Html class provides Form Inputs
 */
class J2Html
{

    public static function label($text, $name = '', $options = array())
    {
        $options['class'] = isset($options['label_class']) ? $options['label_class'] : (isset($options['class']) ? $options['class'] : "");
        $options['for'] = isset($options['for']) ? $options['for'] : $name;
        $attribs = J2Store::platform()->toString($options);
        $html = '<label ' . $attribs . '>' . $text . '</label>';
        return $html;
    }

    /**
     * Create a text input field.
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @return string
     */
    public static function text($name, $value = null, $options = array())
    {
        $value = isset($options['field_type']) && !empty($options['field_type'] && $options['field_type']=='integer' ) ? str_replace(str_split('\\/:*?"<>|+-'),'',$value) : $value ;
        return self::input('text', $name, $value, $options);
    }


    /**
     * Create a price input field.
     *
     * @param string $name
     * @param string $currency symbol
     * @param string $value
     * @param array $options
     * @return string
     */
    public static function price($name, $value = null, $options = array())
    {
        $optionvalue = J2Store::platform()->toString($options);
        $symbol = J2Store::currency()->getSymbol();
        $value = str_replace(str_split('\\/:*?"<>|+-'),'',$value);

        // return price input
        $html = '';
        $html .= '<div class="input-prepend">';
        if (!empty($symbol)) {
            $html .= '<span class="add-on">' . $symbol . '</span>';
        }
        $html .= '<input type="text" name="' . $name . '" value="' . $value . '"  ' . $optionvalue . '    />';
        $html .= '</div>';
        J2Store::plugin()->event('PriceInput', array($name, $value, $options, &$html));
        return $html;
    }


    /**
     * Create a price input field with dynamic data
     */
    public static function price_with_data($prefix, $primary_key, $name, $value, $options, $data)
    {
        $optionvalue = J2Store::platform()->toString($options);
        $symbol = J2Store::currency()->getSymbol();
        $value = str_replace(str_split('\\/:*?"<>|+-'),'',$value);
        // return price input
        $html = '';
        $html .= '<div class="input-prepend">';
        if (!empty($symbol)) {
            $html .= '<span class="add-on">' . $symbol . '</span>';
        }
        $html .= '<input type="text" name="' . $prefix . $name . '" value="' . $value . '"  ' . $optionvalue . '    />';
        $html .= '</div>';
        J2Store::plugin()->event('PriceInputWithData', array($prefix, $primary_key, $name, $value, $options, &$html, $data));
        return $html;
    }


    /**
     * Creates Checkbox list field
     * @param string $value
     * @param array $data
     * @param array $options
     * @result html with list of checkbox
     */
    /* public static function checkboxList($value,$data,$options=array()){
        $html ='';
        $html .= '<div class="controls">';
        foreach($data as $key =>$value){
            $options['id'] = isset($options['id']) ? $options['id'].'_'.$key : $key;
            $optionvalue = self::attributes($options);
            $html .= '<label class="control-label" for="j2store_input-'.$key.'">';
            $html .='<input type="checkbox" '.$optionvalue.'  value="'.$value.'"     />';
            $html .= $value;
            $html .='</label>';
        }
        $html .='</div>';
    return $html;
    } */

    /**
     * Creates a single checkbox element
     * @param stringe $name
     * @param unknown_type $value
     * @param array $options
     * @result html
     */
    public static function checkbox($name, $value = null, $options = array())
    {
        return self::input('checkbox', $name, $value, $options);
    }

    /**
     * Create a textarea  field.
     * @param string $name
     * @param string $value
     * @param array $options
     * @return string
     */
    public static function textarea($name, $value, $options = array())
    {
        return self::input('textarea', $name, $value, $options);
    }

    /**
     * Create a File Field
     * @param string $name
     * @param string $value
     * @param arrat() $options
     */
    public static function file($name, $value, $options = array())
    {
        return self::input('file', $name, $value, $options);
    }

    /**
     * Creates a email field
     * @param string $name
     * @param unknown_type $value
     * @param array $options
     * @result options
     */
    public static function email($name, $value, $options = array())
    {
        return self::input('email', $name, $value, $options);
    }

    /**
     * Create a select box field.
     *
     * @param string $type The type of the select field
     * @param string $name
     * @param array $list
     * @param string $selected
     * @param array $options
     * @return string
     */
    /* public static function select($type, $name , $value, $id='', $options=array(), $relations=array(), $placeholder=array()){
        return J2Select::select($type, $name, $value, $id='', $options, $relations, $placeholder);
    } */


    public static function select()
    {
        return new J2Select();
    }


    /**
     * Creates a radio field
     * @param string $name
     * @param string $value
     * @param array $options
     * @result html
     */
    public static function radio($name, $value, $options = array())
    {
        return self::input('radio', $name, $value, $options);
    }


    /**
     * Creates a radio boolean  field
     * @param string $name
     * @param string $value
     * @param array $options
     * @result html
     */
    public static function radioBooleanList($name, $value = '', $options = array())
    {

        $html = '';
        $id = isset($options['id']) && !empty($options['id']) ? $options['id'] : $name;
        if (!isset($options['hide_label']) && empty($options['hide_label'])) {

            $html .= '<div class="control-group">';
            $label_text = isset($options['label_text']) ? $options['label_text'] : "test";
            $html .= self::label($label_text, $options = array());
        }
        $html .= JHtmlSelect::booleanlist($name, $attribs = array(), $value, $yes = 'JYES', $no = 'JNO', $id);
        if (!isset($options['hide_label']) && empty($options['hide_label'])) {
            $html .= '</div>';
        }

        return $html;

    }

    /**
     * Create a hidden field
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public static function hidden($name, $value, $options = array())
    {
        return self::input('hidden', $name, $value, $options);
    }

    /**
     * Create a button field
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public static function button($name, $value, $options = array())
    {
        return self::input('button', $name, $value, $options);
    }


    /**
     * Creates Media field
     * TODO need to update
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public static function media($name, $value = '', $options = array())
    {
        $platform = J2Store::platform();
        $config = JFactory::getConfig();
        $asset_id = $config->get('asset_id');
        //to overcome Permission access Issues to media
        //@front end
        if (J2Store::platform()->isClient('site')) {
            $asset_id = JFactory::getConfig('com_content')->get('asset_id');
        }

        $id = isset($options['id']) ? $options['id'] : $name;
        $hide_class = isset($options['no_hide']) ? $options['no_hide'] : 'hide';
        $image_id = isset($options['image_id']) ? $options['image_id'] : 'img' . $id;
        $class = isset($options['class']) ? $options['class'] : '';
        $empty_image = JUri::root() . 'media/j2store/images/common/no_image-100x100.jpg';
        $image = JUri::root();
        jimport('joomla.filesystem.file');
        $imgvalue = (isset($value) && !empty($value)) ? $value : 'media/j2store/images/common/no_image-100x100.jpg';

        if ($value && file_exists(JPATH_ROOT . '/' . $value)) {
            $folder = explode('/', $value);
            $folder = array_diff_assoc($folder, explode('/', JComponentHelper::getParams('com_media')->get('image_path', 'images')));
            array_pop($folder);
            $folder = implode('/', $folder);
        } else {
            $folder = '';
        }


        if (JFile::exists(JPATH_SITE . '/' . $imgvalue)) {
            $image .= (isset($value) && !empty($value)) ? $imgvalue : $imgvalue;
        }
        $route = JUri::root();

        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $script = "
	  function removeImage(element){
	  		var ParentDiv = jQuery(element).closest('.input-group');
	  		var InputBox = ParentDiv.find(':input') ;
			var InputImage =ParentDiv.find('img');

			var no_preview ='JUri::root().media/j2store/images/common/no_image-100x100.jpg';

			jQuery(InputBox).attr('value','');
	  		jQuery(InputImage).attr('src','$empty_image') ;
			jQuery('html, body').animate({
				scrollTop: jQuery(ParentDiv).offset().top
		     });
		}
	function previewImage(element,id){
		var value='$route'+jQuery('#'+element.id).attr('value');
		var ParentDiv = jQuery(element).closest('.input-group');
		var inputBox = ParentDiv.find(':input') ;
  		jQuery(inputBox).attr('');
		var InputImage =ParentDiv.find('img') ;
		jQuery(InputImage).attr('src',value);
	}

	function jInsertFieldValue(value, id) {
	    var old_id = document.id(id).value;
		if (old_id != id) {
			var elem = document.id(id)
			elem.value = value;
			elem.fireEvent('change');
			previewImage(elem,id);
		}
	}
	";
            $script_popup = "
	window.addEvent('domready', function() {
		SqueezeBox.initialize({});
		SqueezeBox.assign($('a.modal-button'), {
			parse: 'rel'
		});
	});";
            $style = "
		.j2store-media-slider-image-preview{
			width:50px;

		}";
            $platform->addInlineStyle($style);
            $platform->addInlineScript($script);
            //JFactory::getDocument()->addStyleDeclaration($style);
            //JFactory::getDocument()->addScriptDeclaration($script);
        }


        $version = substr(JVERSION, 0, 5);
        if (version_compare(JVERSION, '3.9.9', 'ge')) {
            $media = JComponentHelper::getParams('com_media');
            $imagesExt  = $media->get('image_extensions') ;
            $audiosExt  = $media->get('audio_extensions');
            $videosExt  = $media->get('video_extensions');
            $documentsExt = $media->get('doc_extensions');
            $displayData = array(
                'asset' => 'com_j2store',
                'authorId' => '281',
                'folder' => $folder,
                'link' => '',
                'preview' => 'show',
                'previewHeight' => '200',
                'previewWidth' => '200',
                'class' => $class,
                'id' => 'imageModal_jform_image_' . $id,
                'name' => $name,
                'value' => $value,
                'readonly' => false,
                'disabled' => false,
                'dataAttribute' => '',
                'mediaTypes' => 0,
                'imagesExt' => isset($imagesExt) && !empty($imagesExt) ? explode(',',$imagesExt) : array() ,
                'audiosExt' =>  isset($audiosExt) && !empty($audiosExt) ? explode(',',$audiosExt) : array() ,
                'videosExt' =>  isset($videosExt) && !empty($videosExt) ? explode(',',$videosExt) : array() ,
                'documentsExt' =>  isset($documentsExt) && !empty($documentsExt) ? explode(',',$documentsExt) : array() ,
                'imagesAllowedExt' => array(),
                'audiosAllowedExt' => array(),
                'videosAllowedExt' => array(),
                'documentsAllowedExt' => array()
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/media.php';
            $media_render = self::getRenderer('joomla.form.field.media', $path);
            $html = $media_render->render($displayData);
        } elseif (version_compare($version, '3.5.0', 'ge') && version_compare($version, '3.6.3', 'lt')) {
            $html = '';
            $html = '<div class="form-inline">';
            $html .= '<div data-preview-height="200" data-preview-width="200" data-preview-container=".field-media-preview" data-preview="false" data-button-save-selected=".button-save-selected" data-button-clear=".button-clear" data-button-cancel=".button-cancel" data-button-select=".button-select" data-input=".field-media-input" data-modal-height="400px" data-modal-width="100%" data-modal=".modal" data-url="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=' . $asset_id . '&amp;author=' . JFactory::getUser()->id . '&amp;fieldid={field-media-id}&amp;folder=" data-basepath="' . JURI::root() . '" class="field-media-wrapper">';
            $html .= '<div class="modal fade j2store-media-model-popup ' . $hide_class . '" tabindex="-1" id="imageModal_jform_image_' . $id . '" >';
            $html .= '<div class="modal-header">';
            $html .= '	<button data-dismiss="modal" class="close" type="button">×</button>';
            $html .= '	<h3>Change Image</h3>';
            $html .= '</div>';
            $html .= '<div class="modal-body">';
            $html .= '</div>';
            $html .= '<div class="modal-footer">';
            $html .= '<button data-dismiss="modal" class="btn">';
            $html .= JText::_('J2STORE_CANCEL');
            $html .= '</button></div>';
            $html .= '</div>';
            $html .= '<div class="input-group">';
            $html .= '<img class="j2store-media-slider-image-preview"  id="' . $image_id . '"	src="' . $image . '" alt="" />';
            $html .= '<input onchange="previewImage(this,jform_image_' . $id . ')" image_id="' . $image_id . '" id="jform_image_' . $id . '" class="input-small hasTooltip field-media-input ' . $class . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" type="text" readonly="readonly"   name="' . $name . '" /> ';
            $html .= '<span class="input-group-btn">';
            $html .= '<a id="media-browse" style="display:inline;position:relative;" class="btn btn-success button-select" >';
            $html .= JText::_('J2STORE_IMAGE_SELECT');
            $html .= '</a>';
            $html .= '<a id="media-cancel" class="btn hasTooltip btn-inverse" onclick="removeImage(this)"   title=""><i class="icon-remove"></i></a>';
            $html .= '</span>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $platform->addInlineScript($script_popup);
            //JFactory::getDocument()->addScriptDeclaration($script_popup);
            $html = '';
            $html = '<div class="form-inline">';
            $html .= '<div class="input-group">';
            $html .= '<img class="j2store-media-slider-image-preview"  id="' . $image_id . '"	src="' . $image . '" alt="" />';
            $html .= '<input onchange="previewImage(this,jform_image_' . $id . ')" image_id="' . $image_id . '" id="jform_image_' . $id . '" class="input-mini ' . $class . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" type="text" readonly="readonly"   name="' . $name . '" /> ';
            $html .= '<span class="input-group-btn">';
            $html .= '<a id="media-browse" style="display:inline;position:relative;" class="modal btn btn-success" rel="{handler:\'iframe\', size: {x: 800, y: 500}}" href="index.php?option=com_media&view=images&tmpl=component&asset=' . $asset_id . '&author=' . JFactory::getUser()->id . '&fieldid=jform_image_' . $id . '&folder=' . $folder . '" title="' . JText::_('PLG_J2STORE_EXTRAIMAGES_SELECT') . '">';
            $html .= JText::_('J2STORE_IMAGE_SELECT');
            $html .= '</a>';
            $html .= '<a id="media-cancel" class="btn hasTooltip btn-inverse" onclick="removeImage(this)"   title=""><i class="icon-remove"></i></a>';
            $html .= '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }

        J2Store::plugin()->event('MediaField', array(&$html, $name, $value, $options));
        return $html;
    }

    protected static function getRenderer($layoutId, $path)
    {
        if (empty($layoutId)) {
            $layoutId = 'default';
        }
        $renderer = new \Joomla\CMS\Layout\FileLayout($layoutId, $basePath = null, $options = array('component' => 'com_j2store'));
        $renderer->setDebug(false);
        $layoutPaths = $renderer->getDefaultIncludePaths();
        if ($layoutPaths) {
            $renderer->setIncludePaths($layoutPaths);
        }
        return $renderer;
    }

    public static function calendar($name, $value, $options = array())
    {
        $id = isset($options['id']) ? $options['id'] : self::clean($name);
        $format = (isset($options['format']) && !empty($options['format'])) ? $options['format'] : '%d-%m-%Y';
        $nullDate = JFactory::getDbo()->getNullDate();
        if ($value == $nullDate || empty($value)) {
            $value = $nullDate;
        }
        return JHtml::_('calendar', $value, $name, $id, $format, $options);
    }


    /**
     * @param $href
     * @param $text
     * @param array $options
     * @return string
     */
    public static function link($href, $text, $options = array())
    {

        $href = isset($href) && !empty($href) ? $href : 'javascript:void(0)';
        $icon = isset($options['icon']) && !empty($options['icon']) ? '<i class="' . $options['icon'] . '"></i>' : '';
        $class = isset($options['class']) && !empty($options['class']) ? $options['class'] : '';
        $id = isset($options['id']) && !empty($options['id']) ? $options['id'] : '';
        $onclick = isset($options['onclick']) && !empty($options['onclick']) ? $options['onclick'] : '';
        $html = '<a id="' . $id . '"  href="' . $href . '" class="' . $class . '"';
        if (isset($options['onclick']) && !empty($options['onclick'])) {
            $html .= 'onclick="' . $onclick . '"';
        }

        $html .= '>' . $icon . $text . '</a>';
        return $html;
    }

    /**
     * Create a form input field.
     *
     * @param string $type
     * @param string $name
     * @param string $value
     * @param array $options
     * @return string
     */
    public static function input($type, $name, $value = null, $options = array())
    {
        //will implode all the options value and return as element attributes
        //$optionvalue = self::attributes($options);
        $optionvalue = J2Store::platform()->toString($options);

        //assign the html
        $html = '';
        //switch the type of input
        switch ($type) {

            // return text input
            case 'text':
                $html .= '<input type="text" name="' . $name . '" value="' . $value . '"  ' . $optionvalue . '    />';
                break;

            //return email input
            case 'email':
                $html .= '<input type="email" name="' . $name . '"  value="' . $value . '"  ' . $optionvalue . '    />';
                break;

            //return password input
            case 'password':
                $html .= '<input type="password"  name="' . $name . '" ' . $optionvalue . '  value="' . $value . '"     />';
                break;

            //return textarea input element
            case 'textarea':
                $html .= '<textarea ' . $optionvalue . ' name="' . $name . '"  value="' . $value . '"     >' . $value . '</textarea>';
                break;

            //return file input element
            case 'file':
                $html .= '<input type="file" name="' . $name . '" ' . $optionvalue . '  value="' . $value . '"     />';
                break;

            //return radio input element
            case 'radio':
                $id = isset($options['id']) && !empty($options['id']) ? $options['id'] : '';
                $html .= J2Html::booleanlist($name, $options, $value, $yes = 'JYES', $no = 'JNO', $id);
                break;

            //return checkbox element
            case 'checkbox':
                $html .= '<input type="checkbox" ' . $optionvalue . '  value="' . $value . '"     />';
                break;

            case 'editor':
                break;

            case 'button':
                $html .= '<input type="button" name="' . $name . '"  ' . $optionvalue . '    value ="' . $value . '"';
                if (isset($options['onclick']) && !empty($options['onclick'])) {
                    $html .= '   onclick ="' . $options['onclick'] . '"';
                }
                $html .= '  />';
                break;

            case 'submit':
                $html .= '<input type="submit" name="' . $name . '"  ' . $optionvalue . 'value ="' . $value . '" />';
                break;

            case 'hidden':
                $html .= '<input type="hidden" name="' . $name . '" ' . $optionvalue . ' value ="' . $value . '" />';
                break;

            case 'number' :
                $html .= '<input type="number" name="' . $name . '" value="' . $value . '" ' . $optionvalue . ' />';
                break;


        }

        return $html;
    }

    public static function user($name, $value,$options = array())
    {
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $user_field = new \Joomla\CMS\Form\Field\UserField();
        } else {
            $user_field = new JFormFieldUser();
            $element = new SimpleXMLElement('<field name="'.$name.'" value="'.$value.'"/>');
            $user_field->setup($element,$value);
        }
        $user_field->setValue($value);
        $layout = 'joomla.form.field.user';
	$data = array('name' => $name);
	if(isset($options['required']) && !empty($options['required'])) {
	$data['required'] = $options['required'];
	}
        return $user_field->render($layout, $data);
    }

    public static function generic_list($name, $value, $options){

        $platform = J2Store::platform();
        $platform->loadExtra('behavior.multiselect');
        //$platform->loadExtra('formbehavior.chosen','select');

        // echo "<pre>";print_r($options);
        $id = isset($options['id']) && $options['id'] ? $options['id'] : $name;
        $placeholders = array();
        if(isset($options['options']) && !empty($options['options'])){
            $placeholders = $options['options'];
            unset($options['options']);
        }
        $multiple = false;
        if(isset($options['multiple']) && !empty($options['multiple'])){
            $multiple = true;
        }
        $required = false ;
        if(isset($options['required']) && !empty($options['required'])){
            $required = true;
        }

        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            return self::select()->clearState()
                ->idTag($id)
                ->type('genericlist')
                ->name($name)
                ->value($value)
                ->attribs($options)
                ->setPlaceholders($placeholders)
                ->getHtml();
        }else{
            $displayData = array(
                'class' => 'input-small',
                'name' => $name,
                'value' => $value  ,
                'options' =>$placeholders ,
                'autofocus' => '',
                'onchange' => '',
                'dataAttribute' => '',
                'readonly' => '',
                'disabled' => false,
                'hint' => '',
                'required' => $required,
                'id' => '',
                'multiple'=> $multiple
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/list-fancy-select.php';
            $media_render = self::getRenderer('joomla.form.field.list-fancy-select', $path);
            return $media_render->render($displayData);
        }
    }
    public static function country($name, $value, $options)
    {
        $country_id = isset($options['id']) && $options['id'] ? $options['id'] : 'country_id';
        $zone_id = isset($options['zone_id']) && $options['zone_id'] ? $options['zone_id'] : 'zone_id';
        $zone_value = isset($options['zone_value']) && $options['zone_value'] ? $options['zone_value'] : '';
        $attr = array("onchange" => "changeZone('$country_id',this.value,'$zone_id',$zone_value)", 'id' => $country_id);
        return self::select()->clearState()
            ->idTag($country_id)
            ->type('genericlist')
            ->name($name)
            ->value($value)
            ->attribs($attr)
            ->hasOne('Countries')
            ->setRelations(
                array(
                    'fields' => array(
                        'key' => 'j2store_country_id',
                        'name' => 'country_name'
                    )
                )
            )->getHtml();
    }

    public static function zone($name, $value, $options)
    {
        $zone_id = isset($options['id']) && $options['id'] ? $options['id'] : 'zone_id';
        $attr = array('id' => $zone_id);
        return self::select()->clearState()
            ->idTag($zone_id)
            ->type('genericlist')
            ->name($name)
            ->value($value)
            ->attribs($attr)
            ->setPlaceHolders(array('' => JText::_('J2STORE_SELECT_OPTION')))
            ->hasOne('Zones')
            ->setRelations(
                array(
                    'fields' => array(
                        'key' => 'j2store_zone_id',
                        'name' => 'zone_name'
                    )
                )
            )->getHtml();
    }

    public static function queueKey($name, $value,$options)
    {
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
		<input type="hidden" name="'.$name.'" value="'.$queue_key.'"/>
		</div>';
        return  $html;
    }

    public static function cronLastHit($name,$value,$options){
        $cron_hit = J2Store::config ()->get('cron_last_trigger','');
        if(empty( $cron_hit )){
            $note = JText::_('J2STORE_STORE_CRON_LAST_TRIGGER_NOT_FOUND');
        }elseif(J2Store::utilities ()->isJson ( $cron_hit )){
            $cron_hit = json_decode ( $cron_hit );
            $date =  isset( $cron_hit->date ) ? $cron_hit->date: '';
            $url = isset( $cron_hit->url ) ? $cron_hit->url:'';
            $ip = isset( $cron_hit->ip ) ? $cron_hit->ip:'';
            $note = JText::sprintf('J2STORE_STORE_CRON_LAST_TRIGGER_DETAILS',$date,$url,$ip);
        }
        $html = '';
        $html .= '<strong>'.$note.'</strong>';
        return  $html;
    }

    public static function customLink($name,$value,$options){
        $id = isset($options['id']) && $options['id'] ? $options['id'] : $name;
        $text = isset($options['text']) && $options['text'] ? $options['text'] : '';
        return '<a class="btn btn-warning" id="'.$id.'" href="#">'.JText::_($text).'</a>';
    }

    public static function menuItems($name,$value,$options){
        $platform = J2Store::platform();
        $items = $platform->getMenuLinks();

        $groups = array();
        // Build the groups arrays.
        foreach ($items as $menu)
        {
            // Initialize the group.
            $groups[$menu->title] = array();

            // Build the options array.
            foreach ($menu->links as $link)
            {
                $levelPrefix = str_repeat('- ', max(0, $link->level - 1));

                // Displays language code if not set to All
                if ($link->language !== '*')
                {
                    $lang = ' (' . $link->language . ')';
                }
                else
                {
                    $lang = '';
                }
                if(version_compare(JVERSION,'3.99.99','lt')){
                    $groups[$menu->title][] = JHtml::_('select.option',
                        $link->value, $levelPrefix . $link->text . $lang,
                        'value',
                        'text',
                        in_array($link->type, array())
                    );
                }else{
                    $groups[$menu->title][] = \Joomla\CMS\HTML\HTMLHelper::_('select.option',
                        $link->value, $levelPrefix . $link->text . $lang,
                        'value',
                        'text',
                        \in_array($link->type, array())
                    );
                }

            }
        }
        $id = isset($options['id']) && $options['id'] ? $options['id'] : $name;
        if(isset($options['id'])){
            unset($options['id']);
        }
        if(version_compare(JVERSION,'3.99.99','lt')){
            $html = JHtml::_(
                'select.groupedlist', $groups, $name,
                array(
                    'list.attr' => implode(' ',$options), 'id' => $id, 'list.select' => $value, 'group.items' => null, 'option.key.toHtml' => false,
                    'option.text.toHtml' => false,
                )
            );
        }else {
            $html = \Joomla\CMS\HTML\HTMLHelper::_(
                'select.groupedlist', $groups, $name,
                array(
                    'list.attr' => implode(' ',$options), 'id' => $id, 'list.select' => $value, 'group.items' => null, 'option.key.toHtml' => false,
                    'option.text.toHtml' => false,
                )
            );
        }
         return $html;
    }

    public static function inputFieldSql($name,$value,$options){
        $id = isset($options['id']) && $options['id'] ? $options['id'] : $name;
        $unset_values = array(
            'id','key_field','value_field','has_one'
        );
        $has_one = isset($options['has_one']) && $options['has_one'] ? $options['has_one'] : '';
        $key_field = isset($options['key_field']) && $options['key_field'] ? $options['key_field'] : '';
        $value_field = isset($options['value_field']) && $options['value_field'] ? $options['value_field'] : '';
        foreach ($unset_values as $unset_value){
            if(isset($options[$unset_value])){
                unset($options[$unset_value]);
            }
        }
        return self::select()->clearState()
            ->idTag($id)
            ->type('genericlist')
            ->name($name)
            ->value($value)
            ->attribs($options)
            ->hasOne($has_one)
            ->setRelations(
                array(
                    'fields' => array(
                        'key' => $key_field,
                        'name' => $value_field
                    )
                )
            )->getHtml();
    }

    public static function custom($type, $name, $value, $options = array())
    {
        if($type == 'radiolist'){
            $arr = array();
            if(isset($options['options']) && !empty($options['options'])){
                foreach ($options['options'] as $option_key => $option_value){
                    $arr[] = JHtml::_('select.option', $option_key,$option_value);
                }
                unset($options['options']);
            }
            $id = isset($options['id']) && $options['id'] ? $options['id'] : $name;
            $html = J2Html::radiolist($arr, $name, $options, 'value', 'text', $value, $id);

        }elseif ($type == 'list') {
            $html = self::generic_list($name, $value,$options);
        }elseif ($type == 'user') {
            $html = self::user($name, $value,$options);
        }elseif ($type == 'queuekey') {
            $html = self::queueKey($name, $value,$options);
        }elseif ($type == 'cronlasthit') {
            $html = self::cronLastHit($name, $value,$options);
        }elseif ($type == 'customlink') {
            $html = self::customLink($name, $value,$options);
        } elseif ($type == 'country') {
            $html = self::country($name, $value, $options);
        } elseif ($type == 'zone') {
            $html = self::zone($name, $value, $options);
        }elseif ($type == 'fieldsql') {
            $html = self::inputFieldSql($name, $value, $options);
        }elseif ($type == 'menuitem') {
            $html = self::menuItems($name, $value, $options);
        } elseif ($type == 'modal_article') {
            $html = self::article($name, $value, $options);
        } elseif ($type == 'enabled') {
            $id = isset($options['id']) && !empty($options['id']) ? $options['id'] : $name;
            $html = JHtmlSelect::booleanlist($name, $attr = array(), $value, $yes = 'JYES', $no = 'JNO', $id);
        }elseif ($type == 'editor') {
            $id = isset($options['id']) && !empty($options['id']) ? $options['id'] : $name;
            $width = isset($options['width']) && !empty($options['width']) ? $options['width'] : '100%';
            $height = isset($options['height']) && !empty($options['height']) ? $options['height'] : '500';
            $cols = isset($options['cols']) && !empty($options['cols']) ? (int)$options['cols'] : false;
            $rows = isset($options['rows']) && !empty($options['rows']) ? (int)$options['rows'] : false;
            $editor_type = isset($options['editor']) && !empty($options['editor']) ? $options['editor'] : '';
            $editor_content = isset($options['content']) && !empty($options['content']) ? $options['content'] : '';
            if($editor_content == 'from_file' && !empty($value)){
                $content = self::getSource($value);
                $value = $content->source;
            }
            $editor = self::getEditor($editor_type);
            $html = $editor->display( $name,  $value, $width, $height, $cols, $rows,false,$id,null,null,$options) ;
        } elseif ($type == 'filelist'){
            $file_options = array(
                'options' => array(
                    '' => JText::_('J2STORE_CHOOSE')
                )
            );
            $fileFilter = isset($options['filter']) && !empty($options['filter']) ? $options['filter']: '';
            $path = isset($options['directory']) && !empty($options['directory']) ? $options['directory']: '';
            if (!is_dir($path))
            {
                $path = JPATH_ROOT . '/' . $path;
            }

            $path = JPath::clean($path);
            $files = JFolder::files($path, $fileFilter);
            if (is_array($files))
            {
                foreach ($files as $file)
                {
                    $file_options['options'][$file] = $file;
                }
            }

            $html = self::generic_list($name, $value,$file_options);
        } elseif ($type == 'calendar') {
            $html = self::calendar($name, $value, $options);
        } elseif ($type == 'coupondiscounttypes') {
            $html = self::couponDiscountTypes($name, $value, $options);
        }elseif ($type == 'couponproducts') {
            $html = self::couponProduct($name, $value, $options);
        }elseif ($type == 'duallistbox') {
            $html = self::duallistbox($name, $value, $options);
        }elseif ($type == 'usergroup') {
            $html = self::userGroup($name, $value,$options);
        } else {
            $html = self::input('text', $name, $value, $options);
        }
        return $html;
    }
    public static  function userGroup($name, $value,$options){

        $platform = J2Store::platform();
        $platform->loadExtra('behavior.multiselect');
        //$platform->loadExtra('formbehavior.chosen','select');
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id AS value, a.title AS text');
        $query->from('#__usergroups AS a');
        $query->group('a.id, a.title');
        $query->order('a.id ASC');
        $query->order($query->qn('title') . ' ASC');

        // Get the options.
        $db->setQuery($query);
        $user_group = $db->loadObjectList();

        $id = isset($options['id']) && $options['id'] ? $options['id'] : $name;
        $placeholders = array();
        if(isset($user_group) && !empty($user_group)){
            $placeholders = $user_group;
        }

//        $multiple = false;
//        if(isset($options['multiple']) && !empty($options['multiple'])){
//            $multiple = true;
//        }
//        $required = false ;
//        if(isset($options['required']) && !empty($options['required'])){
//            $required = true;
//        }

        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            return self::select()->clearState()
                ->idTag($id)
                ->type('genericlist')
                ->name($name)
                ->value($value)
                ->attribs($options)
                ->setPlaceholders($placeholders)
                ->getHtml();
        }else{
            $displayData = array(
                'class' => 'input-small',
                'name' => $name,
                'value' => $value  ,
                'options' =>$placeholders ,
                'autofocus' => '',
                'onchange' => '',
                'dataAttribute' => '',
                'readonly' => '',
                'disabled' => false,
                'hint' => '',
                'required' => false,
                'id' => '',
                'multiple'=> true
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/list-fancy-select.php';
            $media_render = self::getRenderer('joomla.form.field.list-fancy-select', $path);
            return $media_render->render($displayData);
        }
    }
    protected static function getSource($filename) {
        $app = JFactory::getApplication ();
        $item = new stdClass ();

        if ($filename) {
            $filePath = JPath::clean ( JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tpls/'.$filename);
            if (file_exists ( $filePath )) {
                $item->filename = $filename;
                $item->source = file_get_contents ( $filePath );
            } else {
                $app->enqueueMessage ( JText::_ ( 'J2STORE_EMAILTEMPLATE_ERROR_SOURCE_FILE_NOT_FOUND' ), 'error' );
            }
        }
        return $item;
    }
    public static  function duallistbox($name, $value, $options){

        $platform = J2Store::platform();
        JFormHelper::loadFieldClass('list');
        $json = self::getOptions($name, $value, $options);
        $json = json_encode($json,JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
        JHtml::_('script', 'media/j2store/js/dual-list-box.js', false, false);
        JHtml::_('stylesheet', 'media/j2store/css/dual-list-box.css', false, false);
        $selected = json_encode($value);
        $input_id = !empty($options->id) ? $options->id : 'duallistbox-input';
        $html ='<div id="dual-list-box" class="row-fluid">';
        $html .='<select id='.$input_id.' multiple="multiple" 
					size ="10"  name='.$name.'[]'.'
					>		
                    </select>';
        $html .='<script type="text/javascript"> 
	   var optionList = document.getElementById(\''.$input_id.'\').options;
              var options = '.$json.'
              var selected = '. $selected .'
              options.forEach(option => {
               if (option.title && option.id ) {
                  optionList.add(new Option(option.title, option.id, option.selected));
               }
             });
		(function($){
        var values = '. $selected .';
        if ( values != null ){
        $.each(values.split(","), function(i,e){
        $("#' . $input_id . ' option[value=\'" + e + "\']").attr("selected", true);
        });
        }
		var dualListObj =  $(\'#'.$input_id.'\').bootstrapDualListbox(); 
		})(j2store.jQuery)
		</script></div>';
        return $html;
    }
    public static function getOptions($name, $value, $options)
    {
        $source_file      = empty($options['source_file']) ? '' : (string) $options['source_file'];
        $source_class     = empty($options['source_class']) ? '' : (string) $options['source_class'];
        $source_method    = empty($options['source_method']) ? '' : (string) $options['source_method'];
        $source_key       = empty($options['source_key']) ? '*' : (string) $options['source_key'];
        $source_value     = empty($options['source_value']) ? '*' : (string) $options['source_value'];
        $source_translate = empty($options['source_translate']) ? 'true' : (string) $options['source_translate'];
        $source_translate = in_array(strtolower($source_translate), array('true','yes','1','on')) ? true : false;
        $source_format	  = empty($options['source_format']) ? '' : (string) $options['source_format'];

        //echo $source_method;
        $option = array();
        {
            // Maybe we have to load a file?
            if (!empty($source_file))
            {
                $source_file = F0FTemplateUtils::parsePath($source_file, true);

                if (F0FPlatform::getInstance()->getIntegrationObject('filesystem')->fileExists($source_file))
                {
                    include_once $source_file;
                }
            }
            // Make sure the class exists
            if (class_exists($source_class, true))
            {				// ...and so does the option
                if (in_array($source_method, get_class_methods($source_class)))
                {
                    // Get the data from the class
                    if ($source_format == 'optionsobject')
                    {
                        $option = array_merge($option, $source_class::$source_method());
                    }
                    else
                    {
                        // Get the data from the class
                        $source_data = $source_class::$source_method();

                    }
                }
            }
        }
        //$group_options[] = array();
        //to avoid jquery error
        foreach($source_data as $cat){
            //$group_options[$cat->title] =($cat->title);
            $source_data[] =JText ::_ ( strtoupper( $cat->title));
        }


        return $source_data ;
      //  return $source_data;
    }

    public static function couponProduct($name, $value, $options){
        $html ='';
        $fieldId = isset($options['id']) ? $options['id'] : 'jform_product_list';
        $html =J2StorePopup::popup("index.php?option=com_j2store&view=coupons&task=setProducts&layout=products&tmpl=component&function=jSelectProduct&field=".$fieldId, JText::_( "J2STORE_SET_PRODUCTS" ), array('width'=>800 ,'height'=>400 ,'class'=>'btn btn-success'));
        return $html ;
    }

    public  static function couponDiscountTypes($name, $value, $options) {
        $model = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' );
        $list = $model->getCouponDiscountTypes ();
        $attr = array ();
        // Get the field options.
        // Initialize some field attributes.
        $attr ['class'] = ! empty ( $options->class ) ? $options->class : '';
        // Initialize JavaScript field attributes.
        $attr ['onchange'] = isset ( $options->onchange ) ? $options->onchange : '';
        $attr ['id'] = isset ( $options->id ) ? $options->id : '';

        // generate country filter list
        return J2Html::select ()->clearState ()->type ( 'genericlist' )->name ( $name )->attribs ( $attr )->value ( $value )->setPlaceHolders ( $list )->getHtml ();
    }
    public static function getEditor($editor=''){
        if(empty($editor)){
            if(version_compare(JVERSION,'3.99.99','lt')){
                $config = JFactory::getConfig();
                $editor = $config->get('editor',null);
            }elseif(version_compare(JVERSION,'3.99.99','ge')){
                $editor = JFactory::getApplication()->get('editor');
            }
            if(empty($editor)) $editor = null;
        }
        $my_editor = JEditor::getInstance($editor);
        //$my_editor = JFactory::getEditor($editor);
        $my_editor->initialise();
        return $my_editor;
    }

    public static function article($name, $value, $options){
        $platform = J2Store::platform();
        //
        $allowClear     = true;
        $allowSelect    = true;
        $languages = JLanguageHelper::getContentLanguages(array(0, 1), false);
        $app = $platform->application();
        // Load language
        JFactory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);

        // The active article id field.
        $value = (int) $value ?: '';
        $id = isset($options['id']) && !empty($options['id']) ? $options['id']: $name;
        $required = (int)isset($options['required']) && !empty($options['required']) ? $options['required']: false;
        $modalId = 'Article_' . $id;
        $document = JFactory::getDocument();
        if(version_compare(JVERSION,'3.99.99','lt')){
            $platform->loadExtra('jquery.framework');
            $platform->loadExtra('behavior.modal','a.modal');
            JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));
        }else{
            $wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
            // Add the modal field script to the document head.
            $wa->useScript('field.modal-fields');
        }

        // Script to proxy the select modal function to the modal-fields.js file.
        if ($allowSelect)
        {
            static $scriptSelect = null;

            if (is_null($scriptSelect))
            {
                $scriptSelect = array();
            }

            if (!isset($scriptSelect[$id]))
            {
                if(version_compare(JVERSION,'3.99.99','lt')){
                    $document->addScriptDeclaration("window.jSelectJ2Article_" . $id . " = function (id, title, catid, object, url, language) {
                   
                    document.getElementById(\"" . $id . "_id\").value = id;
					document.getElementById(\"" . $id . "_name\").value = title;
					jQuery(\"#".$id."_clear\").removeClass(\"hidden\");
				SqueezeBox.close();
					jQuery('body').removeClass('modal-open');
jQuery('.modal-backdrop').remove();
				}");
                }else{
                    $wa->addInlineScript("
				window.jSelectJ2Article_" . $id . " = function (id, title, catid, object, url, language) {
					window.processModalSelect('Article', '" . $id . "', id, title, catid, object, url, language);
					jQuery('body').removeClass('modal-open');
                    jQuery('.modal-backdrop').remove();
				}",
                        [],
                        ['type' => 'module']
                    );
                }


                JText::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');

                $scriptSelect[$id] = true;
            }
        }
        if ($allowClear && version_compare(JVERSION,'3.99.99','lt'))
        {
            $scriptClear = true;
            $script = array();
            $script[] = '	function jClearArticle(id) {';
            $script[] = '		document.getElementById(id + "_id").value = "";';
            $script[] = '		document.getElementById(id + "_name").value = "' . htmlspecialchars(JText::_('COM_CONTENT_SELECT_AN_ARTICLE', true), ENT_COMPAT, 'UTF-8') . '";';
            $script[] = '		jQuery("#"+id + "_clear").addClass("hidden");';
            $script[] = '		if (document.getElementById(id + "_edit")) {';
            $script[] = '			jQuery("#"+id + "_edit").addClass("hidden");';
            $script[] = '		}';
            $script[] = '		return false;';
            $script[] = '	}';
            $platform->addInlineScript(implode("\n", $script));
            //$document->addScriptDeclaration(implode("\n", $script));
        }
        // Setup variables for display.
        $linkArticles = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
        $urlSelect = $linkArticles . '&amp;function=jSelectJ2Article_' . $id;
        if ($value)
        {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' = :value')
                ->bind(':value', $value);
            $db->setQuery($query);

            try
            {
                $title = $db->loadResult();
            }
            catch (\RuntimeException $e)
            {
                JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        $title = empty($title) ? JText::_('COM_CONTENT_SELECT_AN_ARTICLE') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        $html = '<span class="input-group">';
        $html .= '<input class="form-control" id="' . $id . '_name" type="text" value="' . $title . '" readonly size="35">';
// Select article button
        if ($allowSelect)
        {
            if(version_compare(JVERSION,'3.99.99','lt')){
                $html .= '<a class="modal btn hasTooltip" title="' . JHtml::tooltipText('COM_CONTENT_CHANGE_ARTICLE') . '"  href="' . $urlSelect . '" rel="{handler: \'iframe\', size: {x: 800, y: 450}}"><i class="icon-file"></i> ' . JText::_('JSELECT') . '</a>';
            }else{
                $html .= '<button'
                    . ' class="btn btn-primary' . ($value ? ' hidden' : '') . '"'
                    . ' id="' . $id . '_select"'
                    . ' data-bs-toggle="modal"'
                    . ' type="button"'
                    . ' data-bs-target="#ModalSelect' . $modalId . '">'
                    . '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('JSELECT')
                    . '</button>';
            }
        }
// Clear article button
        if ($allowClear)
        {
            if(version_compare(JVERSION,'3.99.99','lt')){
                $html .= '<button id="' . $id . '_clear" class="btn' . ($value ? '' : ' hidden') . '" onclick="return jClearArticle(\'' . $id . '\')"><span class="icon-remove"></span> ' . JText::_('JCLEAR') . '</button>';
            }else{
                $html .= '<button'
                    . ' class="btn btn-secondary' . ($value ? '' : ' hidden') . '"'
                    . ' id="' . $id . '_clear"'
                    . ' type="button"'
                    . ' onclick="window.processModalParent(\'' . $id . '\'); return false;">'
                    . '<span class="icon-times" aria-hidden="true"></span> ' . JText::_('JCLEAR')
                    . '</button>';
            }


        }

        $html .= '</span>';
        $modalTitle    = JText::_('COM_CONTENT_SELECT_AN_ARTICLE');
        // Select article modal
        if ($allowSelect && version_compare(JVERSION,'3.99.99','gt'))
        {
            $html .= \Joomla\CMS\HTML\HTMLHelper::_(
                'bootstrap.renderModal',
                'ModalSelect' . $modalId,
                array(
                    'title'       => $modalTitle,
                    'url'         => $urlSelect,
                    'height'      => '400px',
                    'width'       => '800px',
                    'bodyHeight'  => 70,
                    'modalWidth'  => 80,
                    'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
                        . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
                )
            );
        }


        $class = $required ? ' class="required modal-value"' : '';
        if(version_compare(JVERSION,'3.99.99','lt')){
            $html .= '<input type="hidden" id="' . $id . '_id"' . $class . ' name="' . $name . '" value="' . $value . '" />';
        }else{
            $html .= '<input type="hidden" id="' . $id . '_id" ' . $class . ' data-required="' . (int) $required . '" name="' . $name
                . '" data-text="' . htmlspecialchars(JText::_('COM_CONTENT_SELECT_AN_ARTICLE'), ENT_COMPAT, 'UTF-8') . '" value="' . $value . '">';
        }

        return $html;

    }

    public static function getOrderStatusHtml($id)
    {
        $html = '';
        $item = F0FModel::getTmpInstance('OrderStatuses', 'J2StoreModel')->getItem($id);
        if ($id) {
            $html .= '<label class="label ' . $item->orderstatus_cssclass . '">' . JText::_($item->orderstatus_name) . '</label>';
        }
        return $html;
    }

    public static function getUserNameById($id)
    {
        $html = '';
        $user = JFactory::getUser($id);
        return $user->name;
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param array $attributes
     * @return string
     */
    public static function attributes($attributes)
    {
        $html = array();

        // For numeric keys we will assume that the key and the value are the same
        // as this will convert HTML attributes such as "required" to a correct
        // form like required="required" instead of using incorrect numerics.
        foreach ((array)$attributes as $key => $value) {

            $element = self::attributeElement($key, $value);

            if (!is_null($element)) $html[] = $element;
        }
        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    protected static function attributeElement($key, $value)
    {
        if (is_numeric($key)) $key = $value;

        if (!is_null($value))

            return $key . '="' . ($value) . '"';
    }


    public static function booleanlist($name, $attribs = array(), $selected = null, $yes = 'JYES', $no = 'JNO', $id = false)
    {
        $arr = array(JHtml::_('select.option', '0', JText::_($no)), JHtml::_('select.option', '1', JText::_($yes)));

        return J2Html::radiolist($arr, $name, $attribs, 'value', 'text', (int)$selected, $id);
    }


    public static function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }


    /**
     * Generates an HTML radio list.
     *
     * @param array $data An array of objects
     * @param string $name The value of the HTML name attribute
     * @param string $attribs Additional HTML attributes for the <select> tag
     * @param mixed $optKey The key that is selected
     * @param string $optText The name of the object variable for the option value
     * @param string $selected The name of the object variable for the option text
     * @param boolean $idtag Value of the field id or null by default
     * @param boolean $translate True if options will be translated
     *
     * @return  string  HTML for the select list
     *
     * @since   1.5
     */
    public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false,
                                     $translate = false)
    {
        reset($data);

        $id = isset($attribs['id']) && !empty($attribs['id']) ? $attribs['id'] : '';
        $class = isset($attribs['class']) && !empty($attribs['class']) ? $attribs['class'] : '';


        if (is_array($attribs)) {
            $attribs = J2Store::platform()->toString($attribs);
        }

        $id_text = $idtag ? $idtag : self::clean($name);

            $html = '<div class="'.$class.' radio">';
            foreach ($data as $obj) {
                $k = $obj->$optKey;
                $t = $translate ? JText::_($obj->$optText) : $obj->$optText;
                $id = (isset($obj->id) ? $obj->id : null);

                $extra = '';
                $id .= $id ? $obj->id : $id_text . $k;

                if (is_array($selected)) {
                    foreach ($selected as $val) {
                        $k2 = is_object($val) ? $val->$optKey : $val;

                        if ($k == $k2) {
                            $extra .= ' selected="selected" ';
                            break;
                        }
                    }
                } else {
                    $extra .= ((string)$k == (string)$selected ? ' checked="checked" ' : '');

                }
                $input_class = ($class == 'btn-group' ? ' class="btn-check" ' : '');
                $label_class = ($class == 'btn-group' ? 'btn btn-outline-success' : 'radio');

                $html .= "\n\t\n\t" . '<input type="radio"  '.$input_class.'  name="' . $name . '" id="' . $id . '"  value="' . $k . '" ' . $extra
                    . $attribs . ' />' ;
                $html .= "\n\t" . '<label class="'. $label_class.'"  for="' . $id . '" id="' . $id . '-lbl" >'. $t;
                $html .= "\n\t" . '</label>';
            }

            $html .= "\n";
            $html .= '</div>';
            $html .= "\n";

        return $html;
    }


    public static function checkboxlist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false,
                                        $translate = false)
    {
        reset($data);

        if (is_array($attribs)) {
            $attribs = J2Store::platform()->toString($attribs);
        }

        $id_text = $idtag ? $idtag : $name;

        $html = '<div class="checkbox">';

        foreach ($data as $obj) {
            $k = $obj->$optKey;
            $t = $translate ? JText::_($obj->$optText) : $obj->$optText;
            $id = (isset($obj->id) ? $obj->id : null);

            $extra = '';
            $id = $id ? $obj->id : $id_text . $k;

            if (is_array($selected)) {
                foreach ($selected as $val) {
                    $k2 = is_object($val) ? $val->$optKey : $val;

                    if ($k == $k2) {
                        $extra .= ' selected="selected" ';
                        break;
                    }
                }
            } else {
                $extra .= ((string)$k == (string)$selected ? ' checked="checked" ' : '');
            }

            $html .= "\n\t" . '<label for="' . $id . '" id="' . $id . '-lbl" class="checkbox">';
            $html .= "\n\t\n\t" . '<input type="checkbox" name="' . $name . '" id="' . $id . '" value="' . $k . '" ' . $extra
                . $attribs . ' >' . $t;
            $html .= "\n\t" . '</label>';
        }

        $html .= "\n";
        $html .= '</div>';
        $html .= "\n";

        return $html;
    }

    /**
     * Method to return PRO feature notice
     *
     * @return string
     */

    public static function pro()
    {
        if (!class_exists('J2Store')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/j2store.php';
        }
        $view = J2Store::view();
        $view->setDefaultViewPath(JPATH_ADMINISTRATOR . '/components/com_j2store/views/eupdates/tmpl');
        $html = $view->getOutput('profeature');
        return $html;
    }

    public static function list_custom($type, $name, $field, $item)
    {
        $html = '';
        if ($type == 'couponexpiretext') {
            $html = self::couponExpireText($item);
        } elseif ($type == 'fieldsql') {
            $html = self::fieldSQL($name, $field, $item);
        } elseif ($type == 'corefieldtypes') {
            $html = self::fieldCore($name, $field, $item);
        }elseif ($type == 'receivertypes') {
            $html = self::receiverTypes($item);
        } elseif ($type == 'orderstatuslist'){
            $html = self::orderStatusList($item);
        } elseif ($type == 'shipping_link'){
            $html = self::shippingLink($item,$field);
        }
        return $html;
    }

    public static function couponExpireText($item)
    {
        if (version_compare(JVERSION, '3.99.99', 'ge')) {
            $info_class = 'badge bg-info';
            $warning_class = 'badge bg-warning';
            $success_class = 'badge bg-success';
        }else if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $info_class = 'label label-info';
            $warning_class = 'label label-warning';
            $success_class = 'label label-success';
           }
        if (!isset($item->valid_from) || !isset($item->valid_to)) {
            return '';
        }
        $diff = self::getExpiryDate($item->valid_from, $item->valid_to);
        $style = 'style="padding:5px"';
        if ($diff->format("%R%a") == 0) {
            $text = JText::sprintf('COM_J2STORE_COUPON_WILL_EXPIRE_TODAY', $diff->format("%a") . ' day (s) ');
            $html = '<label class="'.$info_class .'" ' . $style . '>' . $text . '</label>';
        } elseif ($diff->format("%R%a") <= 0) {
            $text = JText::sprintf('COM_J2STORE_COUPON_EXPIRED_BEFORE_DAYS', $diff->format("%a") . ' day (s) ');
            $html = '<label class="'.$warning_class .'" ' . $style . '>' . $text . '</label>';
        } else {
            $text = JText::sprintf('COM_J2STORE_COUPON_WILL_EXPIRE_WITH_DAYS', $diff->format("%a") . ' day (s) ');
            $html = '<label class="'.$success_class.'" ' . $style . '>' . $text . '</label>';
        }
        return $html;
    }

    protected static function getExpiryDate($valid_from, $valid_to)
    {
        $start = date("Y-m-d");
        $today = date_create($start);
        //assign the coupon offer start date
        // Assign the coupon valid date
        $date2 = date_create($valid_to);
        return date_diff($today, $date2);
    }

    public static function fieldSQL($name, $field, $item)
    {
        $html = '';
        $query = isset($field['query']) && !empty($field['query']) ? $field['query'] : '';
        if (!empty($field['key_field']) && !empty($query) && !empty( $item->$name) ) {
            $query .= ' WHERE ' . $field['key_field'] . ' = ' . $item->$name ;
        }
        if (!empty($query)) {
            $field_data = JFactory::getDbo()->setQuery($query)->loadObject();
            $value_field = $field['value_field'] ?? '';
            $html = $field_data->$value_field ?? '';
        }
        return $html;
    }
    public static function receiverTypes($item)
    {
        $html ='';

        $list = array(
            '*' => JText::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_BOTH' ),
            'admin'=> JText::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_ADMIN' ),
            'customer'=>JText::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_CUSTOMER')
        );

        if(empty($item->receiver_type)) $item->receiver_type = '*';
        $html .= $list[$item->receiver_type];
        return $html;
    }
    public static function orderStatusList($item)
    {

            $success_class = 'badge bg-success';
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $success_class = 'label label-success';
        }
        $html ='';
        if($item->orderstatus_id != '*'){
            if (version_compare(JVERSION, '3.99.99', 'lt')) {
                $orderstatus = F0FTable::getAnInstance('Orderstatus', 'J2StoreTable');
                $orderstatus->load($item->orderstatus_id);
                $html = '<label class="label">' . JText::_($orderstatus->orderstatus_name);
                if (isset($orderstatus->orderstatus_cssclass) && $orderstatus->orderstatus_cssclass) {
                    $html = '<label class="label ' . $orderstatus->orderstatus_cssclass . '">' . JText::_($orderstatus->orderstatus_name);
                }
            }else if (version_compare(JVERSION, '3.99.99', 'ge')) {
                $orderstatus = F0FTable::getAnInstance('Orderstatus', 'J2StoreTable');
                $orderstatus->load($item->orderstatus_id);
                $html = '<label class="label">' . JText::_($orderstatus->orderstatus_name);
                if (isset($orderstatus->orderstatus_cssclass) && $orderstatus->orderstatus_cssclass) {
                    if($orderstatus->orderstatus_cssclass == 'label-success'){
                        $label_class = 'badge bg-success';
                    }else if($orderstatus->orderstatus_cssclass == 'label-warning'){
                        $label_class = 'badge bg-warning';
                    }else if($orderstatus->orderstatus_cssclass == 'label-important'){
                        $label_class = 'badge bg-important';
                    }else if($orderstatus->orderstatus_cssclass == 'label-info'){
                        $label_class = 'badge bg-info';
                    }
                    $html = '<label class="' .$label_class. '">' . JText::_($orderstatus->orderstatus_name);
                }
            }

        }else{
            $html ='<label class="'.$success_class.'">'.JText::_('J2STORE_ALL');
        }
        $html .='</label>';
        return $html;
    }
    public static function shippingLink($item,$field){
        $url = '';
        if(empty($item) || !isset($field['label'])){
            return $url;
        }
        $custom_element = array('shipping_standard');
        J2Store::plugin()->event('IsJ2StoreCustomShippingPlugin',array(&$custom_element));
        if(isset($item->element) && isset($item->link_edit) && in_array($item->element,$custom_element)){
            $url = $item->link_edit;
        }elseif (isset($item->element) && isset($item->plugin_link_edit)){
            $url = $item->plugin_link_edit;
        }
        $text = isset($field['translate']) && $field['translate'] ? JText::_($field['label']):$field['label'];
        return '<a href="'.$url.'">'.$text.'</a>';
    }

    public static function fieldCore($name, $field, $item){

        if(version_compare(JVERSION,'3.99.99','lt')){
            $html ='<label class="label label-warning">'.JText::_('J2STORE_CUSTOM_FIELDS_NOT_CORE').'</label>';
            if(isset($item->$name) && $item->$name){
                $html = '<label class="label label-success">'.JText::_('J2STORE_CUSTOM_FIELDS_CORE').'</label>';
            }
        }elseif(version_compare(JVERSION,'3.99.99','ge')){
            $html ='<label class="badge bg-warning">'.JText::_('J2STORE_CUSTOM_FIELDS_NOT_CORE').'</label>';
            if(isset($item->$name) && $item->$name){
                $html = '<label class="badge bg-success">'.JText::_('J2STORE_CUSTOM_FIELDS_CORE').'</label>';
            }
        }


        return $html;
    }
}

class J2Select extends JObject
{

    protected $state;

    protected $options;

    public function __construct($properties = null)
    {

        if (!is_object($this->state)) {
            $this->state = new JObject();
        }
        $this->options = array();
        parent::__construct($properties);

    }

    /**
     * Magic getter; allows to use the name of model state keys as properties
     *
     * @param string $name The name of the variable to get
     *
     * @return  mixed  The value of the variable
     */
    public function __get($name)
    {
        return $this->getState($name);
    }

    /**
     * Magic setter; allows to use the name of model state keys as properties
     *
     * @param string $name The name of the variable
     * @param mixed $value The value to set the variable to
     *
     * @return  void
     */
    public function __set($name, $value)
    {
        return $this->setState($name, $value);
    }

    /*
    * Magic caller; allows to use the name of model state keys as methods to
    * set their values.
    *
    * @param   string  $name       The name of the state variable to set
    * @param   mixed   $arguments  The value to set the state variable to
    *
    * @return  J2Select  Reference to self
    */
    public function __call($name, $arguments)
    {
        $arg1 = array_shift($arguments);
        $this->setState($name, $arg1);

        return $this;
    }


    /**
     * Method to set model state variables
     *
     * @param string $property The name of the property.
     * @param mixed $value The value of the property to set or null.
     *
     * @return  mixed  The previous value of the property or null if not set.
     */
    public function setState($property, $value = null)
    {
        return $this->state->set($property, $value);
    }

    /**
     * Method to set model state variables
     *
     * @param string $property The name of the property.
     * @param mixed $value The value of the property to set or null.
     *
     * @return  mixed  The previous value of the property or null if not set.
     */
    public function getState($property = null, $default = null)
    {
        return $property === null ? $this->state : $this->state->get($property, $default);
    }

    public function clearState()
    {
        $this->state = new JObject();
        return $this;
    }

    /*
    * Method to return a select list. Allows mapping table relations
    * Example for relations
    * array (
            'hasone' => array (
                    'Vendors' => array (
                            'fields' => array (
                                    'key'=>'j2store_vendor_id',
                                    'name'=>array('company')
                            )
                    )
            )
    );
    *
    */

    public function getHtml()
    {

        $html = '';

        $state = $this->getState();

        $value = isset($state->value) ? $state->value : '';
        $attribs = isset($state->attribs) ? $state->attribs : array();

        $placeholder = isset($state->placeholder) ? $state->placeholder : array();

        if (isset($state->hasOne)) {
            $modelName = $state->hasOne;
            $model = F0FModel::getTmpInstance($modelName, 'J2StoreModel');

            //check relations
            if (isset($state->primaryKey) && isset($state->displayName)) {
                $primary_key = $state->primaryKey;
                $displayName = $state->displayName;

            } else {
                $primary_key = $model->getTable()->getKeyName();
                $knownFields = $model->getTable()->getKnownFields();
                $displayName = $knownFields[1];
            }

            if (isset($state->ordering) && !empty($state->ordering)) {
                $model->setState('filter_order', $state->ordering);
            }

            $items = $model->enabled(1)->getList();

            if (count($items)) {
                foreach ($items as $item) {
                    if (is_array($displayName)) {
                        $text = '';
                        foreach ($displayName as $n) {
                            if (isset($item->$n)) $text .= JText::_($item->$n) . ' ';
                        }
                    } else {
                        $text = JText::_($item->$displayName);
                    }
                    $this->options[] = JHtml::_('select.option', $item->$primary_key, $text);
                }
            }

        }


        $idTag = isset($state->idTag) ? $state->idTag : 'j2store_' . F0FInflector::underscore($state->name);

        return JHtml::_('select.' . $state->type, $this->options, $state->name, $attribs, 'value', 'text', $value, $idTag);
    }


    public function setRelations($relations = array())
    {

        $state = $this->getState();

        if (is_array($relations) && isset($relations['fields']) && count($relations['fields'])) {
            $primary_key = $relations['fields']['key'];
            $displayName = $relations['fields']['name'];
        }
        $this->setState('primaryKey', $primary_key);
        $this->setState('displayName', $displayName);
        return $this;
    }

    public function setPlaceholders($placeholders = array())
    {

        //placeholder
        if (is_array($placeholders) && count($placeholders)) {
            foreach ($placeholders as $k => $v) {
                $this->options[] = JHtml::_('select.option', $k, $v);
            }
        } else {
            $this->options[] = JHtml::_('select.option', '', JText::_('J2STORE_SELECT_OPTION'));
        }

        return $this;
    }

}
