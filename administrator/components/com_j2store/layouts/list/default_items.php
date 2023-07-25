<?php
defined('_JEXEC') or die;
$cols_count = 0;
?>
<table class="table table-bordered table-striped">
    <?php if (isset($vars->header) && !empty($vars->header)): ?>
        <thead>
        <tr>
            <?php foreach ($vars->header as $name => $field): ?>
                <?php if (isset($field['type']) && $field['type'] == 'rowselect'): ?>
                    <th <?php echo isset($field['tdwidth']) && !empty($field['tdwidth']) ? 'style="width:' . $field['tdwidth'] . '"' : ''; ?>>
                        <input type="checkbox" name="checkall-toggle" value=""
                               title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
                               onclick="Joomla.checkAll(this)"/>
                    </th>
                    <?php $cols_count += 1; ?>
                <?php elseif (isset($field['sortable']) && $field['sortable'] == 'true'): ?>
                    <th>
                        <?php echo JHtml::_('grid.sort', $field['label'], $name, $vars->state->filter_order_Dir, $vars->state->filter_order); ?>
                    </th>
                    <?php $cols_count += 1; ?>
                <?php elseif (isset($field['label']) && $field['label']): ?>
                    <th><?php echo JText::_($field['label']); ?></th>
                    <?php $cols_count += 1; ?>
                <?php else: ?>
                    <th><?php echo JText::_($name); ?></th>
                    <?php $cols_count += 1; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tfoot>
        <td colspan="<?php echo $cols_count; ?>">
            <?php echo $vars->pagination->getListFooter(); ?>
        </td>
        </tfoot>
        <tbody>
        <?php if (isset($vars->items) && !empty($vars->items)): ?>
            <?php foreach ($vars->items as $i => $item): ?>
                <tr>
                <?php foreach ($vars->header as $name => $field): ?>
                    <?php if (isset($field['type']) && $field['type'] == 'rowselect'): ?>
                        <td>
                            <?php if((isset($item->orderstatus_core) && $item->orderstatus_core == 1) || (isset($item->field_core) && $item->field_core == 1)):?>
                            <?php else:?>
                                <?php echo JHtml::_('grid.id', $i, $item->$name) ?>
                            <?php endif; ?>
                        </td>
                    <?php elseif (isset($field['type']) && in_array($field['type'], array('couponexpiretext', 'fieldsql','corefieldtypes','receivertypes','orderstatuslist','shipping_link'))): ?>
                        <td><?php echo J2Html::list_custom($field['type'], $name, $field, $item); ?></td>
                    <?php elseif (isset($field['show_link']) && $field['show_link'] == 'true' && isset($field['url_id']) && isset($field['url'])): ?>
                        <?php $url_id = $field['url_id']; ?>
                        <td>
                            <a href="<?php echo str_replace('[ITEM:ID]', $item->$url_id, $field['url']); ?>">
                                <?php echo isset($field['translate']) && $field['translate'] ? JText::_($item->$name):$item->$name; ?>
                            </a>
                        </td>
                    <?php elseif (isset($field['type']) && $field['type'] == 'published'): ?>
                        <td>
                            <?php if (version_compare(JVERSION, '3.99.99', 'ge')) : ?>
                                <?php
                                $options = [
                                    'id' => 'publish-' . $i
                                ];
                                echo (new \Joomla\CMS\Button\PublishedButton)->render((int)$item->$name, $i, $options);
                                ?>
                            <?php else: ?>
                                <?php echo JHtml::_('grid.published', $item->$name, $i); ?>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td><?php echo $item->$name; ?></td>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo $cols_count; ?>"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    <?php endif; ?>
</table>
<?php echo $vars->extra_content ?? '';?>
