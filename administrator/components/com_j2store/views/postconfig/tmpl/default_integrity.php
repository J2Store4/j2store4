<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
			<div class="control-group">
					<label>
					<?php if($this->systemPlugin): ?>
						<p class="text-success">	<?php echo JText::_('J2STORE_INTEGRITY_SYSTEM_PLUGIN_ENABLED'); ?></p>
					<?php endif; ?>
					</label>
					
					<label>
					<?php if($this->cachePlugin): ?>
						<p class="text-error">	<?php echo JText::_('J2STORE_INTEGRITY_CACHE_PLUGIN_ENABLED'); ?></p>
					<?php endif; ?>
					</label>
					
					<div class="controls">
					
					</div>
				</div>