<?php
defined('_JEXEC') or die;
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<?php if(isset($vars->header) && !empty($vars->header)):?>
    <div class="<?php echo $row_class?>">
        <div class="<?php echo $col_class;?>12">
            <?php
            $sortable_field = array();
            //Search field
            foreach ($vars->header as $name => $field): ?>
                <?php if(isset($field['sortable']) && $field['sortable'] === 'true'){
                    $sortable_field[$name] = JText::_($field['label']);
                }; ?>
                <?php if(isset($field['type']) && $field['type'] === 'fieldsearchable'):?>
                <div class="pull-left searchable_field">
                    <input id="search_<?php echo $name;?>" type="text" name="<?php echo $name;?>" value="<?php echo $vars->state->get($name,'');?>" placeholder="<?php echo JText::_($field['label'])?>">
                    <a class="btn" onclick="document.adminForm.submit()"><i class="icon-search"></i></a>
                    <a class="btn" onclick="document.adminForm.<?php echo $name;?>.value='';document.adminForm.submit()"><i class="icon-remove"></i></a>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="pull-right">
                <?php echo $vars->pagination->getLimitBox();?>
            </div>
            <?php if(!empty($sortable_field)):?>
                <div class="pull-right sort_field">
                    <select id="directionTable" class="input-medium" name="sortTable" onchange="jQuery('#filter_order_Dir').val(this.value);this.form.submit();">
                        <option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
                        <option value="asc" <?php echo $vars->state->filter_order_Dir == 'asc' ? 'selected="selected"': '';?>><?php echo JText::_('J2STORE_ASCENDING_ORDER');?></option>
                        <option value="desc" <?php echo $vars->state->filter_order_Dir == 'desc' ? 'selected="selected"': '';?>><?php echo JText::_('J2STORE_DESCENDING_ORDER');?></option>
                    </select>
                </div>
                <div class="pull-right sort_field">
                    <select id="sortTable" class="input-medium" name="sortTable" onchange="jQuery('#filter_order').val(this.value);this.form.submit();">
                        <option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
                        <?php foreach ($sortable_field as $filter_name => $filter_value): ?>
                            <?php if($vars->state->filter_order == $filter_name):?>
                                <option value="<?php echo $filter_name;?>" selected="selected"><?php echo $filter_value;?></option>
                            <?php else: ?>
                                <option value="<?php echo $filter_name;?>"><?php echo $filter_value;?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
