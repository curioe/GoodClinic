/*jQuery("#activate_redirect").click(function() {
		if(jQuery('#activate_redirect').val()=='N')
		{
			jQuery('.hide_setting').css('display','none');
		}
		else
		{
			jQuery('.hide_setting').css('display','');
		}
	}
)*/

function displaynone()
{	
	
	if(jQuery('#activate_redirect:checked').val()=='Y')
	{
		jQuery('.hide_setting').css('display','');
	}
	else
	{
		jQuery('.hide_setting').css('display','none');
	}
}
