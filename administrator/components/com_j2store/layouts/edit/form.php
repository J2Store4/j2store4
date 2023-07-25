<?php
defined('_JEXEC') or die;
$platform = J2Store::platform();
//$platform->loadExtra('behavior.core');
$platform->loadExtra('behavior.formvalidator');
$row_class = 'row';
$col_class = 'col-md-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.JText::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $alert_html = '<div class="alert alert-error alert-danger">'.JText::_('J2STORE_INVALID_INPUT_FIELD').'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
}
?>
    <script  type="text/javascript">
        Joomla.submitbutton = function(pressbutton) {
            var form = document.adminForm;
            if (pressbutton == 'cancel') {
                document.adminForm.task.value = pressbutton;
                form.submit();
            }else{
                if (document.formvalidator.isValid(form)) {
                    document.adminForm.task.value = pressbutton;
                    form.submit();
                }
                else {
                    let msg = [];
                    msg.push('<?php echo $alert_html; ?>');
                    document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
                }
            }
        }
    </script>
    <div class="<?php echo $row_class; ?>">
        <div class="<?php echo $col_class; ?>12">
            <div class="j2store_<?php echo $vars->view; ?>_edit">
                <!--onsubmit="return doValidate( this );"-->
                <form id="adminForm" class="form-horizontal form-validate"   action="<?php echo $vars->action_url; ?>"
                      method="post" name="adminForm">
                    <?php echo J2Html::hidden('option', 'com_j2store'); ?>
                    <?php echo J2Html::hidden('view', $vars->view); ?>
                    <?php if (isset($vars->primary_key) && !empty($vars->primary_key)): ?>
                        <?php echo J2Html::hidden($vars->primary_key, $vars->id); ?>
                    <?php endif; ?>
                    <?php echo J2Html::hidden('task', '', array('id' => 'task')); ?>
                    <?php echo JHTML::_( 'form.token' ); ?>
                    <div class="<?php echo $row_class; ?>">
                        <?php if (isset($vars->field_sets) && !empty($vars->field_sets)): ?>
                            <?php foreach ($vars->field_sets as $field_set): ?>
                                <?php if (isset($field_set['fields']) && !empty($field_set['fields'])): ?>
                                    <div <?php echo isset($field_set['id']) && $field_set['id'] ? 'id="' . $field_set['id'] . '"' : ''; ?>
                                        <?php echo isset($field_set['class']) && is_array($field_set['class']) ? 'class="' . implode(' ', $field_set['class']) . '"' : ''; ?>
                                    >
                                        <?php if (isset($field_set['label']) && !empty($field_set['label'])): ?>
                                            <h3><?php echo JText::_($field_set['label']); ?></h3>
                                        <?php endif; ?>
                                        <?php foreach ($field_set['fields'] as $field_name => $field): ?>
                                            <?php $is_required = isset($field['options']['required']) && !empty($field['options']['required']) ? true : false; ?>
                                            <div class="control-group">
                                                <label class="control-label"><?php echo JText::_($field['label']); ?><?php echo $is_required ? "<span>*</span>" : ''; ?></label>
                                                <div class="controls">
                                                    <?php if (isset($field['type']) && in_array($field['type'], array('number', 'text', 'email', 'password', 'textarea', 'file', 'radio', 'checkbox', 'button', 'submit'))): ?>
                                                        <?php echo J2Html::input($field['type'], $field['name'], $field['value'], $field['options']); ?>
                                                    <?php else: ?>
                                                        <?php echo J2Html::custom($field['type'], $field['name'], $field['value'], $field['options']); ?>
                                                    <?php endif; ?>
                                                    <?php if (isset($field['desc']) && !empty($field['desc'])): ?>
                                                        <small><?php echo JText::_($field['desc']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php //if (version_compare(JVERSION, '3.99.99', 'lt')): ?>
    <!--<script type="text/javascript">-->
    <!--    function myValidate(f) {-->
    <!--        if(f.task.value == 'cancel'){-->
    <!--            document.adminForm.submit();-->
    <!--            return true;-->
    <!--        }else {-->
    <!--            if (document.formvalidator.isValid(f)) {-->
    <!--                document.adminForm.submit();-->
    <!--                return true;-->
    <!--            } else {-->
    <!--                var msg = new Array();-->
    <!--                msg.push('--><?php //echo JText::_('J2STORE_INVALID_INPUT_FIELD')?>
<!--    //                document.getElementById('system-message-container').innerHTML = '-->
<!--    <div class="alert alert-error alert-danger">' + msg.join('\n') + '-->
<!--        <button type="button" class="close" data-dismiss="alert">×</button>-->
<!--    </div>';-->
<!--    //                return false;-->
<!--    //            }-->
<!--    //        }-->
<!--    //    }-->
<!--    //</script>-->
<?php //endif; ?>