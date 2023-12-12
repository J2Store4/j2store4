<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');

class JFormFieldJ2Store extends JFormField
{
    /**
     * The field type.
     *
     * @var		string
     */
    protected $type = 'J2Store';

    protected function getInput()
    {
        try{
            $platform = J2Store::platform();
            $fof_helper = J2Store::fof();
            $app = $platform->application();
            $id = $app->input->getInt('id');

            if($platform->isClient('site')){
                $id = $app->input->getInt('a_id');
            }
            $productTable = $fof_helper->loadTable('Product' ,'J2StoreTable',array('product_source'=>'com_content', 'product_source_id' =>$id));
            $product_id = (isset($productTable->j2store_product_id)) ? $productTable->j2store_product_id : '';

            $inputvars = array(
                'task' =>'edit',
                'render_toolbar'        => '0',
                'product_source_id'=>$id,
                'id' =>$product_id,
                'product_source'=>'com_content',
                'product_source_view'=>'article',
                'form_prefix'=>'jform[attribs][j2store]'
            );
            $input = new F0FInput($inputvars);

            @ob_start();
            F0FDispatcher::getTmpInstance('com_j2store', 'product', array('layout'=>'form', 'tmpl'=>'component', 'input' => $input))->dispatch();
            $html = ob_get_contents();
            ob_end_clean();
            if(version_compare(JVERSION,'3.99.99','lt')){
                $html .= "<script>
		(function($){
			$(document).ready(function(){
				function saveActiveTab(href) {
					// Remove the old entry if exists, key is always dependant on the url
					// This should be removed in the future
					if (sessionStorage.getItem('active-tab')) {
						sessionStorage.removeItem('active-tab');
					}
		
					// Reset the array
					activeTabsHrefs = [];
		
					// Save clicked tab href to the array
					activeTabsHrefs.push(href);
		
					// Store the selected tabs hrefs in sessionStorage
					sessionStorage.setItem(window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').replace(/&[a-zA-Z-_]+=[0-9]+/, ''), JSON.stringify(activeTabsHrefs));
        		}
				$('#item-form #myTabTabs a[data-toggle=\"tab\"]').on('show.bs.tab', function(e) {
					console.log('Next active'+$(e.target).attr('href'));
					localStorage.setItem('activeTab', $(e.target).attr('href'));
				});
    			var activeTab = localStorage.getItem('activeTab');
    			if(activeTab){
    				saveActiveTab(activeTab);
    			}
			});
		})(j2store.jQuery);
		
		</script>
		";
            }else{
                $html .= "<script>
                (function($){
                    $(document).ready(function(){
                        let tabContainer = document.getElementById('myTab');
                        if(tabContainer){
                            let tabsUlElement = tabContainer.firstElementChild;
                            links = [].slice.call(tabsUlElement.querySelectorAll('button[aria-controls]'));
                            links.forEach(function (link, index) {
                                link.addEventListener('joomla.tab.shown', function (e) {
                                    localStorage.setItem('activeTab', $(e.target).attr('aria-controls'));
                                });
                            });
                            var activeTab = localStorage.getItem('activeTab');
                            if(activeTab){
                               $('#myTab button[aria-controls]').attr('aria-expanded','false');
                               $('#myTab joomla-tab-element[role=\"tabpanel\"]').removeAttrs('active');
                               $('#myTab #'+activeTab).attr('active','');
                               $('#myTab button[aria-controls=\"'+activeTab+'\"]').attr('aria-expanded','true');
                            }
                            if(activeTab === 'attrib-j2store'){
                                let j2tabContainer = document.getElementById('j2storetab');
                                if(j2tabContainer){
                                     let j2tabsUlElement = j2tabContainer.firstElementChild;
                                     j2links = [].slice.call(j2tabsUlElement.querySelectorAll('button[aria-controls]')); // Couple the tabs with the plugin objects
                                     j2links.forEach(function (j2link, index) {
                                         j2link.addEventListener('joomla.tab.shown', function (e) {
                                                localStorage.setItem('j2storetab', $(e.target).attr('aria-controls'));
                                            });
                                        });
                                }
                                var j2store_active_tab = localStorage.getItem('j2storetab');
                                if(j2store_active_tab === null){
                                    j2store_active_tab = 'generalTab';
                                }
                                if(j2store_active_tab){
                                    $('#j2storetab button[aria-controls]').attr('aria-expanded','false');
                                    $('#j2storetab joomla-tab-element[role=\"tab\"]').removeAttrs('active');
                                    $('#j2storetab #'+j2store_active_tab).attr('active','');
                                    $('#j2storetab button[aria-controls=\"'+j2store_active_tab+'\"]').attr('aria-expanded','true');
                                }
                            }
                        }
                    });
                })(j2store.jQuery);
                </script>";
            }
        }catch (\Exception $e){
            $html = '';
        }
        return $html;
    }

    protected function getLabel()
    {
        return '';
    }
    public function getControlGroup()
    {
        return '<div class="j2store_catalog_article">'.$this->getInput().'</div>';
    }
}