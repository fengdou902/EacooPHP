function loadRegion(sel,type_id,selName,url){
		jQuery("#"+selName+" option").each(function(){
			jQuery(this).remove();
		});
		jQuery("<option value=0>无</option>").appendTo(jQuery("#"+selName));
		if(jQuery("#"+sel).val()==0){
			return;
		}
		var getur=url+"/pid/"+jQuery("#"+sel).val();
		$.get(getur,function(data){				
				if(data){
					jQuery("#"+selName).show();
					$.each(data.info, function(i, item){						
						jQuery("<option value="+item.id+">"+item.name+"</option>").appendTo(jQuery("#"+selName));
					});
				}else{
					jQuery("#"+selName).hide();
					jQuery("<option value='0'>无</option>").appendTo(jQuery("#"+selName));
				}
			}
		);
		}