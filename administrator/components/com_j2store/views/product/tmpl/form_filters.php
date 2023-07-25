<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="j2store-product-filters" id="j2store-product-filter-blog">
    <?php  echo $this->loadTemplate('ajax_avfilter');?>
</div>

<script type="text/javascript">
    var total_variants =<?php echo $this->item->productfilter_pagination->total;?>;
    var limit  = <?php echo $this->filter_limit;?>;
    var product_id = <?php echo $this->item->j2store_product_id;?>;
    (function($) {
        /**  on load will create footer list **/
        $(document).ready(function(){
            $('#j2store-product-filter-blog').after('<div id="nav" class="pagination pagination-toolbar"><ul class="pagination-list"></ul></div>');
            var numPages = total_variants / limit;
            // now convert the numPages to int
            numPages = Math.ceil(numPages);
            if(numPages > 1 ){
                createFilterFooterList(numPages);
                $('#nav .pagination-list a').bind('click', function(){
                    $('#nav .pagination-list li').removeClass('active');
                    $(this).parent('li').addClass('active');
                });
            }
        });

    })(j2store.jQuery);

    /***
     *  This method will append pagination li to parent Ul
     */
    function createFilterFooterList(numPages){
        (function($) {
            var limitstart = 0;
            for(i = 0;i < numPages;i++) {
                var pageNum = i + 1;
                limitstart = i * limit;
                $('#nav .pagination-list').append('<li><a data-get_limitstart="'+limitstart +'" data-get_page="'+i+'"  onclick="getProductFilterList(this);" href="javascript:void(0);" rel="'+i+'">'+pageNum+'</a></li> ');
            }
            $('#nav .pagination-list li:first').addClass('active');
        })(j2store.jQuery);
    }
    
    function getProductFilterList(element) {
        (function($) {
            var limitstart = $(element).data('get_limitstart');
            var ajOptions = {
                type : 'post',
                url :  'index.php',
                cache: false,
                dataType : 'json',
                data:{
                    'option':'com_j2store',
                    'view' :'products',
                    'task' :'getProductFilterListAjax',
                    'limitstart':limitstart,
                    'product_id' : product_id,
                    'limit' : limit,
                    'form_prefix' : '<?php echo $this->form_prefix;?>'
                }
            }
            $.ajax(ajOptions)
                .done( function(json) {
                    if(json['html']){
                        $('#j2store-product-filter-blog').html(json['html']);
                    }
                })
        })(j2store.jQuery);
    }
						function removeFilter(filter_id, product_id) {
							var rem_filter = {
								option: 'com_j2store',
								view: 'products',
								task: 'deleteproductfilter',
								filter_id: filter_id,
								product_id: product_id
							};
							(function($) {
								$('.j2notify').remove();
								$.ajax({
									type : 'post',
									url  : '<?php echo JRoute::_('index.php');?>',
									data : rem_filter,
									dataType : 'json',
									success : function(data) {
										if(data.success) {
											$('#product_filter_current_option_'+filter_id).remove();
										}
										$('#product_filters_table').before('<div class="j2notify alert alert-block">'+data.msg+'</div>');
									}
								});
							})(j2store.jQuery);

						}


(function($) {
		$(document).ready(function() {
			$('#J2StoreproductFilter').autocomplete({
				source : function(request, response) {
					var search_filter = {
						option: 'com_j2store',
						view: 'products',
						task: 'searchproductfilters',
						q: request.term
					};
					$.ajax({
						type : 'post',
						url  : '<?php echo JRoute::_('index.php');?>',
						data : search_filter,
						dataType : 'json',
						success : function(data) {
							$('#J2StoreproductFilter').removeClass('optionsLoading');
							response($.map(data, function(item) {
								return {
									label:item.group_name +' > '+item.filter_name,
									value: item.j2store_filter_id
								}
							}));
						}
					});
				},
				minLength : 2,
				select : function(event, ui) {
					$('<tr><td class=\"addedFilter\">' + ui.item.label+ '</td><td><span class=\"filterRemove\" onclick=\"j2store.jQuery(this).parent().parent().remove();\">x</span><input type=\"hidden\"  value=\"' + ui.item.value+ '\" name=\"<?php echo $this->form_prefix.'[productfilter_ids]' ;?>[]\" /> </td></tr>').insertBefore('.j2store_a_filter');
					this.value = '';
					return false;
				},
				search : function(event, ui) {
					$('#J2StoreproductFilter').addClass('optionsLoading');
				}
			});

		});
		})(j2store.jQuery);
</script>
